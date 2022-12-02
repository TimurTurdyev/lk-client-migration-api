<?php

namespace App\Main\Export;

use App\Models\Tree;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class TreeRepository
{
    private int $sql_count = 0;

    public function __construct()
    {
        DB::setDefaultConnection('mysql_lk');
    }

    public function query(Tree $tree)
    {
        $this->sql_count += 1;
        return DB::query()
                 ->distinct()
                 ->from('tree')
                 ->where('id', $tree->id)
                 ->orWhere('path', 'like', str_replace('..', '.', sprintf('%s.%d%%', $tree->path, $tree->id)));
    }

    public function searchPathToDepth($tree, JsonCollectionStreamWriter $writer, ProgressBar $bar = null)
    {
        $writer->resetKey();
        foreach ($tree as $list) {
            $bar->advance();

            $list = (array)$list;
            $data_str = sprintf(
                '{ "tree": %s, "tree_data": %s, "data": [',
                json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                json_encode($this->treeData($list['id']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            );

            $writer->push($data_str);

            $this->searchPathToDepth(
                $this->findToPath(
                    str_replace('..', '.', sprintf('%s.%s', $list['path'], $list['id']))
                ),
                $writer,
                $bar
            );
            $writer->push(']}', '');
        }
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
