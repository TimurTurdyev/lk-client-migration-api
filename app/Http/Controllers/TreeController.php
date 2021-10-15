<?php

namespace App\Http\Controllers;

use App\Main\BaseGenerateSql;
use App\Main\TreeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

ini_set('memory_limit', '256M');

class TreeController extends Controller
{
    public function show($tree_id, TreeRepository $treeRepository): \Illuminate\Http\JsonResponse
    {
        $tree = $treeRepository->find($tree_id);

        abort_if(is_null($tree), 404, 'Not found tree: ' . $tree_id . '!');

        $timer = 10;

        list($tree, $timer) = Cache::remember($tree_id, $timer, function () use ($treeRepository, $tree, $timer) {
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
        $timer = 10;

        $procedure_text = Cache::remember($tree_id, $timer, function () use ($treeRepository, $tree, $new_server_tree_id) {
            $sql = '';

            $treeRepository->searchPathCallback([$tree], function ($list, $depth) use (&$sql) {
                $sql .= (new BaseGenerateSql($list, $depth))->apply();
            }, 0);

            $path = app_path('Main/Resources/TreeMigrate.sql');
            $procedure_text = file_get_contents($path);
            $procedure_text = str_replace('NULL;##$new_server_tree_id', $new_server_tree_id . ';', $procedure_text);
            return str_replace('##SQL##', $sql, $procedure_text);
        });


        echo '<body style="padding: .5rem;margin: 0;background: black; color: #718096;"><pre>';
        print_r($procedure_text);
        echo '</pre></body>';
        die();
    }
}
