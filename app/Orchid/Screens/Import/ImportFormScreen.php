<?php

namespace App\Orchid\Screens\Import;

use App\Models\LkImportFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ImportFormScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Форма импорта';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make(__('Save'))
                ->icon('check')
                ->method('save'),
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
            Layout::rows([
                TextArea::make('description')
                    ->title(__('Description (optional)')),

                Upload::make('upload_json')
                    ->title(__('Upload file to json'))
                    ->maxFiles(1)
                    ->acceptedFiles('application/JSON,.json'),
            ])
        ];
    }

    /**
     * @param LkImportFile $lkImportFile
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(LkImportFile $lkImportFile, Request $request)
    {
        $request_validated = $request->validate([
            'upload_json' => [
                'required',
            ],
            'description' => [
                'required',
                'string',
            ]
        ]);

        $lkImportFile->save();

        $lkImportFile->attachment()->syncWithoutDetaching(
            $request_validated['upload_json']
        );

        try {
            $file = $lkImportFile->attachment()->first();
            $content = json_decode(Storage::disk('public')->get($file->physicalPath()), true);

            if (empty($content['app_url']) || empty($content['data'])) {
                throw new \Exception('Ошибка в содержимом файла!');
            }

            $lkImportFile->fill([
                'app_url' => $content['app_url'],
                'file_name' => $file->original_name,
                'description' => $request_validated['description'],
            ])->save();
        } catch (\Exception $exception) {
            $lkImportFile->delete();
            Toast::error($exception->getMessage());
            return redirect()->route('platform.import.create');
        }

        Toast::info(__('File was created.'));

        return redirect()->route('platform.import');
    }
}
