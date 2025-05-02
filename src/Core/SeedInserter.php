<?php

namespace SeedBuilder\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Faker\Factory;
use SeedBuilder\Helpers\ValueResolver;
use RuntimeException;

class SeedInserter
{
    public function insert(string $table, array $rows, array $defaults = [], string $mode = 'insert', array $uniqueKeys = [], array $schema = []): void
    {
        $faker = Factory::create();
        $finalRows = [];

        foreach ($rows as $index => $row) {
            $combined = [];

            foreach ($defaults as $key => $value) {
                $combined[$key] = ValueResolver::resolve($value, $faker);
            }

            foreach ($row as $key => $value) {
                if (is_array($value) && isset($value['table'], $value['row'], $value['foreign_key'])) {
                    $nestedId = $this->insertAndReturnId(
                        $value['table'],
                        [$value['row']],
                        [],
                        'insert',
                        []
                    );
                    $combined[$value['foreign_key']] = $nestedId;
                } else {
                    $combined[$key] = ValueResolver::resolve($value, $faker);
                }
            }

            if (!empty($schema)) {
                $this->validateAgainstSchema($combined, $schema, $index);
            }

            $finalRows[] = $combined;
        }

        $count = count($finalRows);

        if ($mode === 'upsert' && !empty($uniqueKeys)) {
            DB::table($table)->upsert($finalRows, $uniqueKeys);
            if (config('seedbuilder.log')) {
                Log::info("[SeedBuilder] Upserted {$count} rows into '{$table}' using unique keys: " . implode(', ', $uniqueKeys));
            }
        } else {
            DB::table($table)->insert($finalRows);
            if (config('seedbuilder.log')) {
                Log::info("[SeedBuilder] Inserted {$count} rows into '{$table}'");
            }
        }
    }

    protected function insertAndReturnId(string $table, array $rows, array $defaults = [], string $mode = 'insert', array $uniqueKeys = []): int
    {
        $faker = Factory::create();

        $resolved = [];
        foreach ($defaults as $key => $value) {
            $resolved[$key] = ValueResolver::resolve($value, $faker);
        }
        foreach ($rows[0] as $key => $value) {
            $resolved[$key] = ValueResolver::resolve($value, $faker);
        }

        return DB::table($table)->insertGetId($resolved);
    }

    protected function validateAgainstSchema(array $row, array $schema, int $index): void
    {
        foreach ($row as $key => $value) {
            if (!array_key_exists($key, $schema)) {
                throw new RuntimeException("[SeedBuilder] Row #$index: field '{$key}' not defined in schema");
            }

            if (isset($schema[$key]['values']) && is_array($schema[$key]['values'])) {
                if (!in_array($value, $schema[$key]['values'], true)) {
                    throw new RuntimeException("[SeedBuilder] Row #$index: field '{$key}' has invalid value '{$value}' for enum: " . implode(', ', $schema[$key]['values']));
                }
            }
        }
    }
}
