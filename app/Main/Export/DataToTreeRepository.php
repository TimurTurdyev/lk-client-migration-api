<?php

namespace App\Main\Export;

use Illuminate\Support\Facades\DB;
use SplFixedArray;

class DataToTreeRepository
{
    private int $sql_count = 0;

    public function __construct()
    {
        DB::setDefaultConnection('mysql_lk');
    }

    public function getData($tree_id): SplFixedArray
    {
        $this->sql_count += 1;
        $rows = DB::select("SELECT t1.id AS tree_id, mdr.modem_id
                            FROM tree t1
                                     INNER JOIN tree t2 ON t1.id = t2.id OR t1.path like CONCAT(t2.path, '.', t2.id, '%')
                                     INNER JOIN devices d ON t1.id = d.parent
                                     INNER JOIN modems_devices_rel mdr ON d.id = mdr.device_id
                            WHERE t2.id = ?
                            GROUP BY t1.id, mdr.modem_id", [(int)$tree_id]);

        $values = new SplFixedArray(count($rows));

        foreach ($rows as $index => $row) {
            $this->sql_count += 2;
            $values[$index] = [
                'tree_id' => $row->tree_id,
                'modems' => DB::table('modems')
                    ->where('id', $row->modem_id)
                    ->get(),
                'devices' => DB::table('devices')
                    ->where('parent', $row->tree_id)
                    ->where('relation', 'primary')
                    ->get(),
            ];
        }

        return $values;
    }

    public function getSqlCount(): int
    {
        return $this->sql_count;
    }
}
