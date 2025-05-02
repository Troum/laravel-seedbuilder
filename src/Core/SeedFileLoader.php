<?php

namespace SeedBuilder\Core;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class SeedFileLoader
{
    /**
     * @param string $path
     * @return array
     */
    public function load(string $path): array
    {
        $json = Storage::disk('local')->get($path);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new InvalidArgumentException("Invalid JSON structure in file: $path");
        }

        return $data;
    }
}
