<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RdKafka\Conf;
use RdKafka\Consumer as KafkaConsumer;
use RdKafka\Producer as KafkaProducer;
use App\Application\Services\RecommendationService;
use App\Application\Services\WorldServiceClient;
use Illuminate\Support\Facades\Log;

class KafkaConsumeCommand extends Command
{
    protected $signature = 'kafka:consume';
    protected $description = 'Consume messages from Kafka for Actions service';

    private RecommendationService $recommendationService;
    private WorldServiceClient $worldServiceClient;
    private bool $running = true;
    private $producer;

    public function __construct(RecommendationService $recommendationService, WorldServiceClient $worldServiceClient)
    {
        parent::__construct();
        $this->recommendationService = $recommendationService;
        $this->worldServiceClient = $worldServiceClient;
    }

    public function handle(): void
    {
        $this->info('Starting Actions Kafka consumer...');

        $this->initProducer();

        // Настройка Consumer
        $conf = new Conf();
        $conf->set('metadata.broker.list', env('KAFKA_BROKERS', 'kafka:9092'));
        $conf->set('group.id', env('KAFKA_GROUP_ID', 'actions_service_group'));
        $conf->set('enable.auto.commit', 'false');
        $conf->set('auto.offset.reset', 'earliest');

        // Обработка сигналов для graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'shutdown']);
            pcntl_signal(SIGINT, [$this, 'shutdown']);
        }

        $consumer = new KafkaConsumer($conf);
        $topicConf = new \RdKafka\TopicConf();
        $topic = $consumer->newTopic(env('KAFKA_TOPIC_REQUEST', 'client_to_actions'), $topicConf);

        // Используем RD_KAFKA_OFFSET_STORED (0) для начала чтения с сохранённого смещения
        $topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);

        $this->info('Waiting for messages on topic: ' . env('KAFKA_TOPIC_REQUEST', 'client_to_actions'));

        while ($this->running) {
            try {
                $message = $topic->consume(0, 1000);

                if ($message === null) {
                    continue;
                }

                if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                    $this->handleMessage($message);
                    $topic->offsetStore($message->partition, $message->offset);
                } elseif ($message->err === RD_KAFKA_RESP_ERR__PARTITION_EOF) {
                    // Нет новых сообщений — нормально
                } elseif ($message->err === RD_KAFKA_RESP_ERR__TIMED_OUT) {
                    // Таймаут — нормально
                } else {
                    $this->error('Error: ' . $message->errstr());
                    Log::error('Kafka consumer error: ' . $message->errstr());
                }

                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
            } catch (\Exception $e) {
                Log::error('Consumer error: ' . $e->getMessage());
                $this->error('Error: ' . $e->getMessage());
                sleep(1);
            }
        }

        $topic->consumeStop(0);
        $consumer->close();
        $this->info('Consumer stopped.');
    }

    private function initProducer(): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', env('KAFKA_BROKERS', 'kafka:9092'));
        $conf->set('acks', 'all');
        $this->producer = new KafkaProducer($conf);
    }

    private function handleMessage(\RdKafka\Message $message): void
    {
        $this->info('Received message: ' . $message->payload);

        try {
            $data = json_decode($message->payload, true, flags: JSON_THROW_ON_ERROR);

            if (!isset($data['correlation_id'], $data['action'], $data['payload'])) {
                Log::warning('Invalid message format', ['message' => $data]);
                return;
            }

            if ($data['action'] !== 'get_recommendation') {
                Log::info('Unknown action: ' . $data['action']);
                return;
            }

            $responseData = $this->processRequest($data['payload']);
            $this->sendResponse($data['correlation_id'], $responseData);
            $this->info('Recommendation processed. Correlation ID: ' . $data['correlation_id']);

        } catch (\JsonException $e) {
            Log::error('JSON decode error: ' . $e->getMessage());
            $this->sendErrorResponse($data['correlation_id'] ?? 'unknown', 'Invalid JSON format');
        } catch (\Exception $e) {
            Log::error('Message handling error: ' . $e->getMessage());
            $this->sendErrorResponse($data['correlation_id'] ?? 'unknown', $e->getMessage());
        }
    }

    private function processRequest(array $payload): array
    {
        $userId = $payload['user_id'] ?? null;
        $latitude = $payload['latitude'] ?? 0;
        $longitude = $payload['longitude'] ?? 0;

        if (!$userId) {
            throw new \InvalidArgumentException('Missing required field: user_id');
        }

        // Получаем контекст из World
        $worldContext = $this->worldServiceClient->getWorldContext($userId, $latitude, $longitude);

        // Получаем рекомендацию
        $recommendation = $this->recommendationService->recommend($worldContext);

        return $recommendation->toArray();
    }

    private function sendResponse(string $correlationId, array $data): void
    {
        $topicName = env('KAFKA_TOPIC_RESPONSE', 'actions_to_client');
        $topic = $this->producer->newTopic($topicName);

        $response = [
            'correlation_id' => $correlationId,
            'status' => 'success',
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ];

        $message = json_encode($response, JSON_UNESCAPED_UNICODE);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        $this->producer->flush(3000);

        Log::info('Response sent', ['correlation_id' => $correlationId]);
    }

    private function sendErrorResponse(string $correlationId, string $error): void
    {
        $topicName = env('KAFKA_TOPIC_RESPONSE', 'actions_to_client');
        $topic = $this->producer->newTopic($topicName);

        $response = [
            'correlation_id' => $correlationId,
            'status' => 'error',
            'error' => $error,
            'timestamp' => now()->toIso8601String()
        ];

        $message = json_encode($response, JSON_UNESCAPED_UNICODE);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
        $this->producer->flush(3000);

        Log::error('Error response sent', ['correlation_id' => $correlationId, 'error' => $error]);
    }

    public function shutdown(int $signal): void
    {
        $this->info('Shutting down...');
        $this->running = false;
    }
}
