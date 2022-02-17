<?php

namespace App\Orchid\Screens\Export;

use App\Main\Export\DataToTreeRepository;
use App\Main\Export\DeviceToTreeRelationRepository;
use App\Main\Export\TreeRepository;
use App\Models\Tree;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExportListScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Экспорт данных по дереву';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'table' => Tree::filters()
                ->withCount(['treeChild' => function($q) {
                    $q->remember(60 * 5);
                }])
                ->remember(60 * 5)
                ->prefix('tree')
                ->defaultSort('path', 'asc')
                ->paginate(),
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
            Layout::table('table', [
                TD::make('id', 'ID')
                    ->width('100')
                    ->filter(Input::make())
                    ->sort()
                    ->cantHide()
                    ->render(function (Tree $tree) {
                        return $tree->id;
                    }),

                TD::make('name', 'Name')
                    ->filter(Input::make())
                    ->width('300')
                    ->sort()
                    ->render(function (Tree $tree) {
                        return $tree->name;
                    }),

                TD::make('type', 'Type')
                    ->sort()
                    ->render(function (Tree $tree) {
                        return $tree->type;
                    }),

                TD::make('path', 'Path')
                    ->filter(Input::make())
                    ->width('300')
                    ->sort()
                    ->render(function (Tree $tree) {
                        return $tree->path;
                    }),

                TD::make('child_count', 'Child count')
                    ->width('100')
                    ->render(function (Tree $tree) {
                        return $tree->tree_child_count - 1;
                    }),

                TD::make('action', 'Action')
                    ->render(function (Tree $tree) {
                        return Button::make('Export file')
                            ->method('export', ['tree_id' => $tree->id, 'action' => 'with_app_sandbox'])
                            ->icon('cloud-download')
                            ->rawClick()
                            ->novalidate();
                    })
            ]),
        ];
    }

    /**
     * @param Tree $tree
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Tree $tree): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $file_name = sprintf('%s_export_to_%s',
            date('Y_m_d_H_i_s'),
            $tree->id,
        );

        $timer = 1;

        $result = Cache::remember($file_name, $timer, function () use ($tree, $timer) {

            $treeRepository = new TreeRepository();
            $dataToTree = new DataToTreeRepository();

            $data = $treeRepository->searchPathToDepth([$tree])[0];
            $data['data_to_tree'] = $dataToTree->getData($tree->id);

            return json_encode([
                'app_url' => config('app.url'),
                'sql_count' => $treeRepository->getSqlCount() + $dataToTree->getSqlCount(),
                'caching_time_up_to' => now()->addSeconds($timer),
                'export_tree_id' => $tree->id,
                'data' => $data,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        });

        return response()->streamDownload(function () use ($result) {
            echo $result;
        }, $file_name . '.json');
    }
}
