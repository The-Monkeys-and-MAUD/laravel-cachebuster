<?php

namespace Themonkeys\Cachebuster;


use Illuminate\Support\Facades\Facade;

class Cachebuster extends Facade {
    protected static function getFacadeAccessor() { return 'cachebuster.url'; }
}