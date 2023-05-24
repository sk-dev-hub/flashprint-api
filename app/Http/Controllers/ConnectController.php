<?php

namespace App\Http\Controllers;

use App\Services\Vsemayki\RestConnectorNew;
use Illuminate\Http\Request;

class ConnectController extends Controller
{
    public function updateToken() {

    $connector = new RestConnectorNew($_ENV['CLIENT_ID'], $_ENV['CLIENT_SECRET']);

    $connector->updateToken();

    return 'токен обновлен';

    }
}
