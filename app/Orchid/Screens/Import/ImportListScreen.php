<?php

namespace App\Orchid\Screens\Import;

use App\Main\Import\ImportRepository;
use App\Main\Import\RecursiveIterationData;
use App\Models\LkImportFile;
use App\Orchid\Layouts\Import\ImportFileListLayout;
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
            $importRepository = new ImportRepository();
            $recursiveIteration = new RecursiveIterationData($importRepository);

            $recursiveIteration->apply($content);
        } catch (\Exception $exception) {
            Toast::error($exception->getMessage());
            return redirect()->route('platform.import');
        }


        Toast::info(__('File was imported.'));

        return redirect()->route('platform.import');
    }
}
