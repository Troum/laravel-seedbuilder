<?php

namespace SeedBuilder\Core;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SeedBuilder
{
    public static function insert(string|array $input): void
    {
        $config = is_string($input)
            ? (new SeedFileLoader())->load($input)
            : $input;

        $table = $config['table'];
        $rows = $config['rows'];
        $defaults = $config['defaults'] ?? [];
        $truncate = $config['truncate'] ?? false;
        $deleteWhere = $config['delete_where'] ?? null;
        $mode = $config['mode'] ?? 'insert';
        $uniqueKeys = $config['unique'] ?? [];
        $schema = $config['schema'] ?? [];

        if (!Schema::hasTable($table)) {
            if (!empty($schema)) {
                (new TableCreator())->createFromDefinition($table, $schema);
            } else {
                $sample = array_merge($defaults, $rows[0] ?? []);
                if (empty($sample)) {
                    throw new RuntimeException("Cannot infer schema: table '$table' does not exist and no schema or data provided.");
                }
                $schema = (new SchemaInferrer())->infer($sample);
                (new TableCreator())->create($table, $schema);
            }
        }

        if ($truncate) {
            DB::table($table)->truncate();
        } elseif (!empty($deleteWhere) && is_array($deleteWhere)) {
            DB::table($table)->where($deleteWhere)->delete();
        }

        (new SeedInserter())->insert($table, $rows, $defaults, $mode, $uniqueKeys, $schema);
    }
}
