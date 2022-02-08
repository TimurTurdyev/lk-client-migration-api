<?php

namespace App\Main\Export;

use Illuminate\Support\Facades\DB;

class TreeRepository
{
    public function __construct()
    {
        DB::setDefaultConnection('mysql_lk');
    }

    private int $sql_count = 0;

    public function find($tree_id)
    {
        return DB::selectOne("SELECT * FROM tree WHERE id = ?", [(int)$tree_id]);
    }

    public function findToPath($path)
    {
        return DB::select("SELECT tree.* FROM tree WHERE path = ?", [(string)$path]);
    }

    public function searchPathToDepth($tree)
    {
        $list_search = [];

        foreach ($tree as $list) {
            $devices = $this->devices($list->id);

            $device_idx = [];

            foreach ($devices as $device) {
                if ($device->id) {
                    $device_idx[$device->id] = '';
                }
            }

            $device_idx = array_keys($device_idx);

            $find_tree = $this->findToPath(str_replace('..', '.', sprintf('%s.%s', $list->path, $list->id)));

            $list_search[] = [
                'tree' => $list,
                'tree_data' => $this->treeData($list->id),
//                'modems' => $this->modems($device_idx),
//                'devices' => $devices,
//                'registrators' => $this->registrators($device_idx),
//                'modems_devices_rel' => $this->modems_devices_rel($device_idx),
//                'devices_registrators_rel' => $this->devices_registrators_rel($device_idx),
//                'data' => $this->searchPathToDepth($find_tree)
            ];
        }

        return $list_search;
    }

    public function searchPathCallback($tree, $callback, $parent = 0)
    {
        foreach ($tree as $list) {
            $devices = $this->devices($list->id);
            $device_id = [];

            foreach ($devices as $device) {
                if ($device->id) {
                    $device_id[$device->id] = '';
                }
            }

            $id = $list->id;
            $path = str_replace('..', '.', sprintf('%s.%s', $list->path, $id));

            $find_tree = $this->findToPath($path);

            $callback([
                'tree' => $list,
                'tree_data' => $this->treeData($id),
//                'devices' => $devices,
//                'registrators' => $this->registrators(array_keys($device_id)),
            ], $parent);

            $this->searchPathCallback($find_tree, $callback, $id);
        }
    }

    public function treeData(int $tree_id)
    {
        $this->sql_count += 1;
        return DB::selectOne("SELECT * FROM tree_data WHERE element_id = ?", [$tree_id]);
    }

    public function devices(string $tree_id)
    {
        $this->sql_count += 1;
        return DB::select("SELECT *
                                FROM devices
                                WHERE parent IS NOT NULL AND parent = ?", [$tree_id]);
    }

    public function registrators(array $device_id)
    {
        if (!$device_id) return [];

        $device_id = join(',', $device_id);

        $this->sql_count += 1;
        return DB::select("SELECT r.*
                                FROM registrators r
                                JOIN devices_registrators_rel drr ON r.id = drr.registrator_id
                                WHERE drr.device_id IN (?)
                                GROUP BY r.id", [$device_id]);
    }

    public function getSqlCount()
    {
        return $this->sql_count;
    }

    private function modems(array $device_idx)
    {
        if (!$device_idx) return [];

        $device_idx = join(',', $device_idx);

        $this->sql_count += 1;
        return DB::select("SELECT m.*
                                FROM modems m
                                JOIN modems_devices_rel dmr ON m.id = dmr.modem_id
                                WHERE dmr.device_id IN (?)
                                GROUP BY m.id", [$device_idx]);
    }

    private function modems_devices_rel(array $device_idx)
    {
        if (!$device_idx) return [];

        $device_idx = join(',', $device_idx);

        $this->sql_count += 1;
        return DB::select("SELECT *
                                FROM modems_devices_rel
                                WHERE device_id IN (?)", [$device_idx]);
    }

    private function devices_registrators_rel(array $device_idx)
    {
        if (!$device_idx) return [];

        $device_idx = join(',', $device_idx);

        $this->sql_count += 1;
        return DB::select("SELECT *
                                FROM devices_registrators_rel
                                WHERE device_id IN (?)", [$device_idx]);
    }
}
