<?php

namespace App\SeedBuilder;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableCreator
{
    /**
     * @param string $table
     * @param array $columns
     * @return void
     */
    public function create(string $table, array $columns): void
    {
        Schema::create($table, function (Blueprint $blueprint) use ($columns) {
            $blueprint->bigIncrements('id');

            foreach ($columns as $name => $type) {
                $column = $blueprint->$type($name);
                $column->nullable();
            }
        });
    }

    /**
     * @param string $table
     * @param array $definition
     * @return void
     */
    public function createFromDefinition(string $table, array $definition): void
    {
        Schema::create($table, function (Blueprint $blueprint) use ($definition) {
            foreach ($definition as $column => $options) {
                $type = $options['type'];

                // ENUMS
                if ($type === 'enum') {
                    $columnBuilder = $blueprint->enum($column, $options['values'] ?? []);
                } else {
                    $columnBuilder = $blueprint->$type($column);
                }

                // Nullable
                if (($options['nullable'] ?? false) === true) {
                    $columnBuilder->nullable();
                }

                // Default value
                if (array_key_exists('default', $options)) {
                    $columnBuilder->default($options['default']);
                }

                // Unique
                if (!empty($options['unique'])) {
                    $blueprint->unique($column);
                }

                // Primary
                if (!empty($options['primary'])) {
                    $blueprint->primary($column);
                }

                // Foreign key
                if (!empty($options['foreign'])) {
                    $ref = $options['foreign'];
                    $blueprint->foreign($column)
                        ->references($ref['column'] ?? 'id')
                        ->on($ref['table']);
                }
            }
        });
    }

}
