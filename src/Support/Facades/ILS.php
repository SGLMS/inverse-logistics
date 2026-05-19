<?php

namespace SGLMS\InverseLogistics\Support\Facades;

use Illuminate\Support\Facades\Facade;

class ILS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'inverse-logistics';
    }
}
