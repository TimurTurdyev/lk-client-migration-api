<?php

namespace App\Main\Export;

use Illuminate\Support\Facades\DB;

class DeviceToTreeRelationRepository
{
    public function __construct()
    {
        DB::setDefaultConnection('mysql_lk');
    }

    public function devices($tree_id)
    {
        return DB::select("SELECT t1.id AS tree_id, mdr.modem_id
                            FROM tree t1
                                     INNER JOIN tree t2 ON t1.id = t2.id OR t1.path like CONCAT(t2.path, '.', t2.id, '%')
                                     INNER JOIN devices d ON t1.id = d.parent
                                     INNER JOIN modems_devices_rel mdr ON d.id = mdr.device_id
                            WHERE t2.id = ?
                            GROUP BY t1.id, mdr.modem_id", [(int)$tree_id]);
    }
}
