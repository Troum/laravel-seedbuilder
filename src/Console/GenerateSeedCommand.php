<?php

namespace SeedBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class GenerateSeedCommand extends Command
{
    protected $signature = 'seedbuilder:generate {table} {--path=seeds}';
    protected $description = 'Generate a JSON seed stub for the given table';

    public function handle(): int
    {
        $table = $this->argument('table');
        $path = $this->option('path');

        if (!Schema::hasTable($table)) {
            $this->error("Table '$table' does not exist.");
            return 1;
        }

        $columns = Schema::getColumnListing($table);
        $firstRow = (array) DB::table($table)->first();
        $schema = [];

        foreach ($columns as $col) {
            $value = $firstRow[$col] ?? null;

            $schema[$col] = [
                'type' => match (true) {
                    is_int($value) => 'bigInteger',
                    is_float($value) => 'decimal',
                    is_bool($value) => 'boolean',
                    is_string($value) && strtotime($value) !== false => 'timestamp',
                    is_string($value) && strlen($value) <= 255 => 'string',
                    default => 'text',
                }
            ];
        }

        $output = [
            'table' => $table,
            'schema' => $schema,
            'rows' => []
        ];

        $json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $filename = "$path/$table.stub.json";

        Storage::disk('local')->put($filename, $json);

        $this->info("Generated stub for table '$table' in storage/app/$filename");

        return 0;
    }
}
