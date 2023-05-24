<?php

use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\TestServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers\API'], function () {

    Route::get('/catalog', [HomeController::class, 'index'])->name('api.home');

    Route::get('/test', [TestServerController::class, 'index']);

});
