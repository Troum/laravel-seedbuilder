<?php

namespace App\SeedBuilder;

use Illuminate\Support\Str;

class SchemaInferrer
{
    /**
     * @param array $sample
     * @return array
     */
    public function infer(array $sample): array
    {
        $columns = [];

        foreach ($sample as $key => $value) {
            if ($key === 'id') continue;

            $columns[$key] = $this->resolveType($value);
        }

        return $columns;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function resolveType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'bigInteger',
            is_float($value) => 'decimal',
            is_bool($value) => 'boolean',
            is_string($value) && strtotime($value) !== false => 'timestamp',
            is_string($value) && Str::length($value) <= 255 => 'string',
            default => 'text',
        };
    }
}
