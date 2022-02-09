<?php

namespace App\Orchid\Layouts\Import;

use App\Models\LkImportFile;
use Orchid\Platform\Models\User;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Persona;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ImportFileListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'lk_import_files';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
            TD::make('id', __('Id'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(function (LkImportFile $lkImportFile) {
                    return $lkImportFile->id;
                }),

            TD::make('description', __('Description'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(function (LkImportFile $lkImportFile) {
                    return $lkImportFile->description;
                }),

            TD::make('app_url', __('App url'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(function (LkImportFile $lkImportFile) {
                    return $lkImportFile->app_url;
                }),

            TD::make('file_name', __('File name'))
                ->sort()
                ->cantHide()
                ->filter(Input::make())
                ->render(function (LkImportFile $lkImportFile) {
                    return $lkImportFile->file_name;
                }),

            TD::make('created_at', __('Created'))
                ->sort()
                ->render(function (LkImportFile $lkImportFile) {
                    return $lkImportFile->created_at->toDateTimeString();
                }),
        ];
    }
}
