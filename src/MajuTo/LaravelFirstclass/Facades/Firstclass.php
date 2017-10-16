<?php
namespace MajuTo\LaravelFirstclass\Facades;

use Illuminate\Support\Facades\Facade;

class Firstclass extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'firstclass';
    }

}