<?php

namespace App\Http\Controllers\Base;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API для рекомендаций действий на основе информации об окружающем мире пользователя",
    title: "Actions API",
    contact: new OA\Contact(
        email: "https://github.com/luniah"
    )
)]
#[OA\Server(
    url: "http://127.0.0.1:8000"
)]
abstract class Controller
{
    //
}
