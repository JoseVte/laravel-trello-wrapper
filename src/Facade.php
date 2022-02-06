<?php

namespace LaravelTrello;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'trello';
    }
}
