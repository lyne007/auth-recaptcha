<?php

namespace Lyne007\AuthRecaptcha;

use Encore\Admin\Admin;
use Illuminate\Support\ServiceProvider;

class AuthRecaptchaServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(AuthRecaptcha $extension)
    {
        if (! AuthRecaptcha::boot()) {
            return ;
        }

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'auth-recaptcha');
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/laravel-admin-ext/auth-recaptcha')],
                'auth-recaptcha'
            );
        }

        Admin::booting(function () {
            Admin::js('vendor/laravel-admin-ext/auth-recaptcha/reCAPTCHA/recaptcha-api.min.js');
        });

        $this->app->booted(function () {


            AuthRecaptcha::routes(__DIR__.'/../routes/web.php');
        });


    }
}
