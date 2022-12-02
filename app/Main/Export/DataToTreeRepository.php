<?php

namespace App\Main\Export;

use App\Models\Tree;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;

class DataToTreeRepository
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
                 ->select('t.id', 'mdr.modem_id')
                 ->from('modems')
                 ->join('modems_devices_rel AS mdr', 'modems.id', '=', 'mdr.modem_id')
                 ->join('devices AS d', function (JoinClause $query) {
                     $query->whereRaw("mdr.device_id = d.id and d.parent IS NOT NULL and d.relation = 'primary'");
                 })
                 ->join('tree AS t', function (JoinClause $query) {
                     $query->whereRaw("d.parent = CONCAT('', t.id)");
                 })
                 ->where('t.id', $tree->id)
                 ->orWhere('t.path', 'like', str_replace('..', '.', sprintf('%s.%d%%', $tree->path, $tree->id)));
    }

    public function getData(Tree $tree, JsonCollectionStreamWriter $writer, ProgressBar $bar = null): void
    {
        $rows = $this->query($tree)
                     ->cursor();

        $writer->push('"data_to_tree": [');
        $writer->resetKey();

        foreach ($rows as $index => $row) {
            $this->sql_count += 2;
            $bar->advance();
            $writer->push(
                json_encode([
                    'tree_id' => $row->id,
                    'modems' => DB::table('modems')
                                  ->where('id', $row->modem_id)
                                  ->get(),
                    'devices' => DB::table('devices')
                                   ->where('parent', (string)$row->id)
                                   ->where('relation', 'primary')
                                   ->get(),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }

        $writer->push(']', '');
    }

    public function getSqlCount(): int
    {
        return $this->sql_count;
    }
}
