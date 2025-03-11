<?php

namespace Cchhay\LaravelSpellnumber\Facades;

use Illuminate\Support\Facades\Facade;

class Spellnumber extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Spellnumber';
    }
}
