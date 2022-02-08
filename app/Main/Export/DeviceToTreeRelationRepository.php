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
        return DB::select("SELECT CONCAT(99, t1.id) AS tree_id, mdr.modem_id
                            FROM tree t1
                                     INNER JOIN tree t2 ON t1.id = t2.id OR t1.path like CONCAT(t2.path, '.', t2.id, '%')
                                     INNER JOIN devices d ON t1.id = d.parent
                                     INNER JOIN modems_devices_rel mdr ON d.id = mdr.device_id
                            WHERE t2.id = ?
                            GROUP BY t1.id, mdr.modem_id", [(int)$tree_id]);
    }

    public function insertToData(array $devices)
    {
        foreach ($devices as $device) {
            $tree = DB::table('migrate_data')
                ->where('entity', '=', 'tree')
                ->where('old_id', '=', $device['tree_id'])
                ->first();

            if ($tree) {
                $queryResults = DB::table('devices')
                    ->where('modem_id', '=', $device['modem_id'])
                    ->where('relation', 'primary')
                    ->get();

                foreach ($queryResults as $result) {
                    if ($result->modem_id) {
                        DB::table('devices')
                            ->where('id', '=', $result->id)
                            ->update([
                                'parent' => $tree->new_id
                            ]);

                        $device_primary_id = $result->id;
                        $modem_id = $result->modem_id;

                        unset($result->id);

                        $result->parent = $tree->new_id;
                        $result->config_time = 0;
                        $result->status_messages = '';
                        $result->modem_id = null;

                        $last_id = DB::table('devices')
                            ->insertGetId((array)$result);

                        DB::table('modems_devices_rel')
                            ->insertOrIgnore([
                                'device_id' => $last_id,
                                'modem_id' => $modem_id,
                            ]);

                        $devicesRegistrators = DB::table('devices_registrators_rel')
                            ->where('device_id', $device_primary_id)->get();

                        foreach ($devicesRegistrators as $devicesRegistrator) {
                            DB::table('devices_registrators_rel')
                                ->insertOrIgnore([
                                    'registrator_id' => $devicesRegistrator->registrator_id,
                                    'device_id' => $last_id,
                                ]);
                        }
                    }
                }
            }
        }
    }
}
