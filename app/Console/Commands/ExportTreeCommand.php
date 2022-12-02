<?php

namespace App\Console\Commands;

use App\Main\Export\DataToTreeRepository;
use App\Main\Export\JsonCollectionStreamWriter;
use App\Main\Export\TreeRepository;
use App\Models\Tree;
use Illuminate\Console\Command;

class ExportTreeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tree:export {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tree = Tree::query()
                    ->findOrFail($this->argument('id'));

        $file_path = sprintf(
            'app/public/export/%s_export_to_%s.json',
            date('Y_m_d'),
            $tree->id,
        );

        $timer = microtime(true);

        $path = storage_path($file_path);
        $writer = new JsonCollectionStreamWriter($path);

        $treeRepository = new TreeRepository();
        $dataToTree = new DataToTreeRepository();

        $writer->push(
            sprintf(
                '"app_url": "%s", "export_tree_id": %d, "data": [',
                config('app.url'),
                $tree->id,
            )
        );

        $treeCount = $treeRepository->query($tree)
                                    ->count();
        $dataToTreeCount = $dataToTree->query($tree)
                                      ->count();

        $bar = $this->output->createProgressBar($treeCount + $dataToTreeCount);
        $this->newLine(1);
        $this->line('Progress:');
        $bar->start();

        $treeRepository->searchPathToDepth([$tree->toArray()], $writer, $bar);

        $writer->push(']', '');
        $dataToTree->getData($tree, $writer, $bar);

        $bar->finish();

        $this->newLine(2);
        $this->line('Bench:');
        $this->table(
            ['Timer', 'Memory MB', 'Tree sql count', 'Data ro tree sql count'],
            [[microtime(true) - $timer, memory_get_peak_usage(true) / 1024 / 1024, $treeRepository->getSqlCount(), $dataToTree->getSqlCount()]]
        );
        $this->newLine(2);

        $this->line('File:');
        $this->line($path);

        return Command::SUCCESS;
    }
}
