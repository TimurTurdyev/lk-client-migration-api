<?php

namespace App\Orchid\Screens\Import;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use App\Main\Import\ImportRepository;
use App\Main\Import\RecursiveIterationData;
use App\Models\LkImportFile;
use App\Orchid\Layouts\Import\ImportFileListLayout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;

class ImportListScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Список файлов импорта';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'lk_import_files' => LkImportFile::filters()->defaultSort('id', 'desc')->paginate()
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('Add'))
                ->icon('plus')
                ->route('platform.import.create'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            ImportFileListLayout::class
        ];
    }

    public function runMigrate(LkImportFile $lkImportFile)
    {
        try {
            $file = $lkImportFile->attachment->first();

            $content = json_decode(Storage::disk('public')->get($file->physicalPath()), true);
            $importRepository = new ImportRepository($file->id);
            $recursiveIteration = new RecursiveIterationData($importRepository);
            $recursiveIteration->apply($content);
        } catch (\Exception $exception) {
            Toast::error($exception->getMessage());
            return redirect()->route('platform.import');
        }

        Cache::flush();

        $message = __('File was imported.');

        if ($modems_count_not_found = $recursiveIteration->modemsCountNotFound()) {
            $message = sprintf(__('File was imported. But %s modems not found'), $modems_count_not_found) ;
        }

        Toast::info($message);

        return redirect()->route('platform.import');
    }
}
