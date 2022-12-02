<?php

namespace App\Orchid\Screens\Export;

use App\Main\Export\DeviceToTreeRelationRepository;
use App\Models\Tree;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
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
                           ->withCount([
                               'treeChild' => function ($q) {
                                   $q->remember(60 * 5);
                               }
                           ])
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

                TD::make('file', 'File')
                  ->width('100')
                  ->render(function (Tree $tree) {
                      $file_path = sprintf(
                          'public/export/%s_export_to_%s.json',
                          date('Y_m_d'),
                          $tree->id,
                      );

                      if (Storage::exists($file_path)) {
                          return Link::make()->icon('orc.cloud-download')->href(asset(Storage::url($file_path)));
                      }
                      return '-';
                  }),

                TD::make('action', 'Action')
                  ->render(function (Tree $tree) {
                      return Button::make('Export file')
                                   ->method('export', ['tree_id' => $tree->id, 'action' => 'with_app_sandbox'])
                                   ->icon('orc.cloud-download')
                                   ->rawClick()
                                   ->novalidate();
                  })
            ]),
        ];
    }

    /**
     * @param Tree $tree
     *
     * @return void
     */
    public function export(Tree $tree)
    {
        Artisan::call('tree:export', ['id' => $tree->id]);
    }
}
