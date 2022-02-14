<?php

namespace Lyne007\AuthRecaptcha;

use Encore\Admin\Extension;

class AuthRecaptcha extends Extension
{
    public $name = 'auth-recaptcha';

    public $views = __DIR__.'/../resources/views';

    public $assets = __DIR__.'/../resources/assets';

    public $menu = [
        'title' => 'Authrecaptcha',
        'path'  => 'auth-recaptcha',
        'icon'  => 'fa-gears',
    ];
}