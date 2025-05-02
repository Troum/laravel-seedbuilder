<?php

namespace SeedBuilder\Helpers;

use Faker\Generator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ValueResolver
{
    public static function resolve(mixed $value, Generator $faker): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (trim($value) === 'now()') {
            return Carbon::now()->toDateTimeString();
        }

        if (Str::startsWith($value, 'faker:')) {
            return self::resolveFakerValue($value, $faker);
        }

        return $value;
    }

    protected static function resolveFakerValue(string $expression, Generator $faker): mixed
    {
        $parts = explode(':', $expression);
        array_shift($parts); // удалить 'faker'

        $method = array_shift($parts);

        if (!method_exists($faker, $method)) {
            throw new \InvalidArgumentException("Faker method '$method' does not exist.");
        }

        return call_user_func_array([$faker, $method], $parts);
    }
}
