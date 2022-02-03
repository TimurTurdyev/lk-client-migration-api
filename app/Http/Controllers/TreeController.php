<?php

namespace App\Http\Controllers;

use App\Main\Export\BaseGenerateSql;
use App\Main\Export\TreeRepository;
use Illuminate\Support\Facades\Cache;

ini_set('memory_limit', '1024M');

class TreeController extends Controller
{
    private int $timer = 60 * 5;

    public function show($tree_id, TreeRepository $treeRepository): \Illuminate\Http\JsonResponse
    {
        $tree = $treeRepository->find($tree_id);

        abort_if(is_null($tree), 404, 'Not found tree: ' . $tree_id . '!');

        $timer = $this->timer;

        list($tree, $timer) = Cache::remember('json' . $tree_id, $timer, function () use ($treeRepository, $tree, $timer) {
            return [
                $treeRepository->searchPathToDepth([$tree]),
                now()->addSeconds($timer)
            ];
        });

        return response()->json([
            'query' => $treeRepository->getSqlCount(),
            'cache' => $timer,
            'data' => $tree,
        ]);
    }

    public function export($tree_id, $new_server_tree_id, TreeRepository $treeRepository)
    {
        $tree = $treeRepository->find($tree_id);

        abort_if(is_null($tree), 404, 'Not found tree: ' . $tree_id . '!');

        $timer = $this->timer;

        $procedure_text = Cache::remember('str' . $tree_id, $timer, function () use ($treeRepository, $tree, $new_server_tree_id) {
            $sql = '';

            $treeRepository->searchPathCallback([$tree], function ($list, $parent) use (&$sql) {
                $sql .= (new BaseGenerateSql($list, $parent))->apply();
            });

            $path = app_path('Main/Resources/TreeMigrate.sql');

            $procedure_text = file_get_contents($path);
            $procedure_text = str_replace('NULL;##$new_server_tree_id', $new_server_tree_id . ';', $procedure_text);
            return str_replace('##SQL##', $sql, $procedure_text);
        });

        if (request('dump')) {
            echo '<body style="padding: .5rem;margin: 0;background: black; color: #718096;"><pre>';
            print_r($procedure_text);
            echo '</pre></body>';
            die();
        }

        return response()->streamDownload(function () use ($procedure_text) {
            echo $procedure_text;
        }, 'migrate-' . $tree_id . '.sql');
    }
}
