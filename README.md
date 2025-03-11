# laravel-spellnumber

## usage

```php
<?php

use Cchhay\LaravelSpellnumber\Facades\Spellnumber;
use Illuminate\Support\Facades\Route;


Route::get('test-spellnumber', function () {
    return [
        '3' => Spellnumber::spell(3),
        '13' => Spellnumber::spell(13),
        '20' => Spellnumber::spell(20),
        '39.045' => Spellnumber::spell(39.045),
        '100' => Spellnumber::spell(100),
        '837' => Spellnumber::spell(837),
        '8387' => Spellnumber::spell(8387),
        '123456' => Spellnumber::spell(123456),
        '1234567' => Spellnumber::spell(1234567),
        '123456789' => Spellnumber::spell(123456789),
        '1234567890' => Spellnumber::spell(1234567890),
        '123456789012' => Spellnumber::spell(1234567890),
        '-1234567890' => Spellnumber::spell(-1234567890.01),
    ];
});