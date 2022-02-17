<?php

namespace App\Main\Export;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TreeRepository
{
    private int $sql_count = 0;

    public function __construct()
    {
        DB::setDefaultConnection('mysql_lk');
    }

    public function searchPathToDepth($tree): array
    {
        $list_search = [];

        foreach ($tree as $list) {
            $list_search[] = [
                'tree' => $list,
                'tree_data' => $this->treeData($list->id),
                'data' => $this->searchPathToDepth(
                    $this->findToPath(
                        str_replace('..', '.', sprintf('%s.%s', $list->path, $list->id))
                    )
                )
            ];
        }

        return $list_search;
    }

    public function findToPath(string $path): array
    {
        $this->sql_count += 1;
        return DB::select("SELECT tree.* FROM tree WHERE path = ?", [$path]);
    }

    public function treeData(int $tree_id)
    {
        $this->sql_count += 1;
        return DB::selectOne("SELECT * FROM tree_data WHERE element_id = ?", [$tree_id]);
    }

    public function getSqlCount(): int
    {
        return $this->sql_count;
    }
}
