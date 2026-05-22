<?php

namespace App\Console\Commands;

use App\Services\HoerenImportService;
use Illuminate\Console\Command;

class HoerenImport extends Command
{
    protected $signature   = 'hoeren:import {file : Path to the JSON file (absolute or relative to project root)}';
    protected $description = 'Import Hören modules / codes / exams / statements from a source JSON file. Idempotent.';

    public function handle(HoerenImportService $importer): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            // Fallback: try resolving relative to base_path()
            $alt = base_path($path);
            if (is_file($alt)) $path = $alt;
            else {
                $this->error("File not found: {$path}");
                return self::FAILURE;
            }
        }

        $this->info("Reading {$path} ...");
        $json = file_get_contents($path);
        if ($json === false || $json === '') {
            $this->error('Could not read file (empty or unreadable).');
            return self::FAILURE;
        }

        $this->info('Importing... (re-run is safe; existing audio uploads are preserved by slug)');
        $result = $importer->import($json);

        $this->newLine();
        $this->table(
            ['modules', 'codes', 'exams', 'statements', 'skipped'],
            [[
                $result['modules'],
                $result['codes'],
                $result['exams'],
                $result['statements'],
                $result['skipped'],
            ]],
        );

        if (! empty($result['errors'])) {
            $this->warn('Errors encountered:');
            foreach (array_slice($result['errors'], 0, 10) as $err) {
                $this->line('  - ' . $err);
            }
            if (count($result['errors']) > 10) {
                $this->line('  ... and ' . (count($result['errors']) - 10) . ' more');
            }
            return self::FAILURE;
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
