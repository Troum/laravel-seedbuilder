<?php

namespace SeedBuilder\Facades;

use Illuminate\Support\Facades\Facade;

class SeedBuilder extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SeedBuilder\Core\SeedBuilder::class;
    }
}
