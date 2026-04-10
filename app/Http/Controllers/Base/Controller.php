<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\OA;

#[OA\Info(
    version: "1.0.0",
    title: "Actions API",
    description: "API для рекомендаций действий на основе информации об окружающем мире пользователя",
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
