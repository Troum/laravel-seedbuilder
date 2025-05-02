<?php

namespace SeedBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ExportSeedCommand extends Command
{
    protected $signature = 'seedbuilder:export {table} {--path=seeds}';
    protected $description = 'Export table schema and data to a JSON file';

    public function handle(): int
    {
        $table = $this->argument('table');
        $path = $this->option('path');

        if (!Schema::hasTable($table)) {
            $this->error("Table '$table' does not exist.");
            return 1;
        }

        $columns = Schema::getColumnListing($table);
        $rows = DB::table($table)->get()->map(function ($row) {
            return (array) $row;
        })->toArray();

        $sample = $rows[0] ?? [];
        $schema = [];

        foreach ($sample as $col => $val) {
            $type = match (true) {
                is_null($val) => 'string',
                is_int($val) => 'bigInteger',
                is_float($val) => 'decimal',
                is_bool($val) => 'boolean',
                is_string($val) && strtotime($val) !== false => 'timestamp',
                is_string($val) && strlen($val) <= 255 => 'string',
                default => 'text',
            };

            $schema[$col] = ['type' => $type];

            if ($val === null) {
                $schema[$col]['nullable'] = true;
            }
        }

        $output = [
            'table' => $table,
            'schema' => $schema,
            'rows' => $rows,
        ];

        $json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $filename = "$path/$table.json";

        Storage::disk('local')->put($filename, $json);

        $this->info("Exported table '$table' to storage/app/$filename");

        return 0;
    }
}
