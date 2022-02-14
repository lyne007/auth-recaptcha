<?php

use Lyne007\AuthRecaptcha\Http\Controllers\AuthRecaptchaController;

Route::get('auth/login', AuthRecaptchaController::class.'@getLogin');
Route::post('auth/login', AuthRecaptchaController::class.'@postLogin');
Route::any('auth/api/recaptcha', AuthRecaptchaController::class.'@googleApi');
