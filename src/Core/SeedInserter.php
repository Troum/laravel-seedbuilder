<?php

namespace SeedBuilder\Core;

use Faker\Factory;
use Illuminate\Support\Facades\DB;

class SeedInserter
{
    /**
     * @param string $table
     * @param array $rows
     * @param array $defaults
     * @return void
     */
    public function insert(string $table, array $rows, array $defaults = []): void
    {
        $faker = Factory::create();

        $resolved = array_map(function ($row) use ($defaults, $faker) {

            $combined = array_map(function ($value) use ($faker) {
                return is_callable($value) ? $value($faker) : $value;
            }, $defaults);

            foreach ($row as $key => $value) {
                $combined[$key] = is_callable($value) ? $value($faker) : $value;
            }

            return $combined;
        }, $rows);

        DB::table($table)->insert($resolved);
    }
}
