<?php

namespace App\Orchid\Screens\Export;

use App\Main\Export\TreeRepository;
use App\Models\Tree;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExportFormScreen extends Screen
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
                ->withCount('treeChild')
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
                            ->method('export', ['tree_id' => $tree->id])
                            ->icon('cloud-download')
                            ->rawClick()
                            ->novalidate();
                    })
            ]),
        ];
    }

    /**
     * @param $tree_id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export($tree_id, TreeRepository $treeRepository): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $file_name = sprintf('export_%s', $tree_id);

        $tree = $treeRepository->find($tree_id);

        abort_if(is_null($tree), 404, 'Not found tree: ' . $tree_id . '!');

        $timer = 1;

        $result = Cache::remember($file_name, $timer, function () use ($treeRepository, $tree, $timer) {
            return json_encode([
                'query' => $treeRepository->getSqlCount(),
                'cache' => now()->addSeconds($timer),
                'data' => array_values($treeRepository->searchPathToDepth([$tree])),
            ], JSON_PRETTY_PRINT);
        });

        return response()->streamDownload(function () use ($result) {
            echo $result;
        }, $file_name . '.json');
    }
}
