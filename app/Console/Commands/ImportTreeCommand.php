<?php

namespace App\Console\Commands;

use App\Main\Import\ImportRepository;
use App\Main\Import\RecursiveIterationData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

ini_set('memory_limit', -1);

class ImportTreeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tree:import {--file=} {--path=.} {--full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт json файла веток и девайсов';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = $this->option('file');
        $path = $this->option('path');
        $full = $this->option('full');

        if (
            !$file
            || !Storage::disk('public')
                       ->exists('import/' . $file)
        ) {
            $this->error('Not found file');

            return Command::FAILURE;
        }

        $recursiveIteration = new RecursiveIterationData(
            new ImportRepository()
        );

        $content = json_decode(
            Storage::disk('public')
                   ->get('import/' . $file),
            true
        );

        if ($full) {
            $recursiveIteration->applyTree($content['data'], $path);
        }

        $recursiveIteration->applyDataToTree($content['data_to_tree']);

        return Command::SUCCESS;
    }
}
