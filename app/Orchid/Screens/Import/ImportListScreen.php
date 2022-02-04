<?php

namespace App\Orchid\Screens\Import;

use App\Models\LkImportFile;
use App\Orchid\Layouts\Import\ImportFileListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

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
}
