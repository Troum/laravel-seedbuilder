<?php

namespace SeedBuilder\Core;

use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * @class SeedBuilder
 * @package App\Integrator
 */
class SeedBuilder
{
    /**
     * @param string|array $input
     * @return void
     */
    public static function insert(string|array $input): void
    {
        $config = is_string($input)
            ? (new SeedFileLoader())->load($input)
            : $input;

        $table = $config['table'];
        $rows = $config['rows'];
        $defaults = $config['defaults'] ?? [];

        if (!Schema::hasTable($table)) {
            if (!empty($config['schema'])) {
                (new TableCreator())->createFromDefinition($table, $config['schema']);
            } else {
                $sample = array_merge($defaults, $rows[0] ?? []);
                if (empty($sample)) {
                    throw new RuntimeException("Cannot infer schema: table '$table' does not exist and no schema or data provided.");
                }
                $schema = (new SchemaInferrer())->infer($sample);
                (new TableCreator())->create($table, $schema);
            }
        }

        (new SeedInserter())->insert($table, $rows, $defaults);
    }
}
