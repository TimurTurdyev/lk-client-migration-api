<?php

namespace App\Orchid\Screens\Import;

use App\Main\Api\ApiClient;
use App\Models\MigrateFile;
use App\Models\Modem;
use App\Models\Tree;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ModemsFromImportListScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Список модемов из импорта %s';

    protected Collection $modems_exist;
    protected Collection $tree_lk;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(MigrateFile $lkImportFile): array
    {
        $this->name = sprintf($this->name, $lkImportFile->id);

        try {
            $file = $lkImportFile->attachment->first();

            $content = json_decode(Storage::disk('public')->get($file->physicalPath()), true);
            $paginator = $this->paginate(collect($content['data']['connect_by_primary_devices']), 50, null, [
                'path' => request()->url()
            ]);

            $this->modems_exist = Modem::whereIn('id', $paginator->map(fn($item) => $item['modem_id'])->toArray())
                ->get('id')
                ->pluck('id', 'id');

            $this->tree_lk = Tree::whereIn('id', $paginator->map(fn($item) => $item['tree_id'])->toArray())
                ->get('id')
                ->pluck('id', 'id');

        } catch (\Exception $exception) {
            Toast::error($exception->getMessage());
        }

        return [
            'table' => $paginator
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::table('table', [
                TD::make('exist', __('Exist modem'))
                    ->cantHide()
                    ->width(115)
                    ->render(function ($item) {
                        $exist = $this->modems_exist->has($item['modem_id']);
                        $style = 'color: ' . ($exist ? 'green;' : 'red;');
                        return ModalToggle::make()
                            ->modal('oneAsyncApiModal')
                            ->modalTitle(__('Api setting modem: ') . $item['modem_id'])
                            ->style($style)
                            ->icon($exist ? 'check' : 'close')
                            ->asyncParameters([
                                'api_modem' => $item['modem_id'],
                            ]);
                    }),

                TD::make('modem_id', __('Hex Id'))
                    ->cantHide()
                    ->width(100)
                    ->render(function ($item) {
                        return $item['modem_id'];
                    }),

                TD::make('modem_id', __('Dec Id'))
                    ->cantHide()
                    ->width(100)
                    ->render(function ($item) {
                        return hexdec($item['modem_id']);
                    }),

                TD::make('tree_id', __('Old tree'))
                    ->cantHide()
                    ->render(function ($item) {
                        return $item['tree_id'];
                    }),
            ]),
            Layout::modal('oneAsyncApiModal', Layout::rows([
                Code::make('modem')->language(Code::JS)
            ]))
                ->size(Modal::SIZE_LG)
                ->withoutApplyButton()
                ->async('asyncGetApiModem'),
        ];
    }

    public function asyncGetApiModem($modem_id)
    {
        $client = new ApiClient();
        $response = $client->modemSetting([
            'modem_id' => hexdec($modem_id)
        ]);

        return [
            'modem' => json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ];
    }

    public function paginate($items, $perPage = 50, $page = null, $options = []): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
