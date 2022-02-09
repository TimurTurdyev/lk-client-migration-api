<?php

namespace App\Main\Export;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TreeRepository
{
    private string $action = 'full_data';
    private int $sql_count = 0;

    public function __construct()
    {
        DB::setDefaultConnection('mysql_lk');
    }

    public function searchPathToDepth($tree)
    {
        $list_search = [];

        foreach ($tree as $list) {
            $find_tree = $this->findToPath(str_replace('..', '.', sprintf('%s.%s', $list->path, $list->id)));

            $item = [
                'tree' => $list,
                'tree_data' => $this->treeData($list->id),
            ];

            if ($this->getAction() === 'full_data') {
                $devices = $this->devices($list->id);

                $device_idx = [];

                foreach ($devices as $index => $device) {
                    if ($device->id && !array_key_exists($device->id, $device_idx)) {
                        $device_idx[$device->id] = '';
                    }
                }

                $device_idx = array_keys($device_idx);

                $item['modems'] = $this->modems($device_idx);
                $item['devices'] = $devices;
                $item['registrators'] = $this->registrators($device_idx);
                $item['modems_devices_rel'] = $this->modems_devices_rel($device_idx);
                $item['devices_registrators_rel'] = $this->devices_registrators_rel($device_idx);
            }

            $item['data'] = $this->searchPathToDepth($find_tree);

            $list_search[] = $item;
        }

        return $list_search;
    }

    public function searchPathCallback($tree, $callback, $parent = 0)
    {
        foreach ($tree as $list) {
            $id = $list->id;

            $find_tree = $this->findToPath(str_replace('..', '.', sprintf('%s.%s', $list->path, $id)));

            $callback([
                'tree' => $list,
                'tree_data' => $this->treeData($id),
            ], $parent);

            $this->searchPathCallback($find_tree, $callback, $id);
        }
    }

    public function find(int $tree_id)
    {
        $this->sql_count += 1;
        return DB::selectOne("SELECT * FROM tree WHERE id = ?", [$tree_id]);
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

    public function devices(string $tree_id): Collection
    {
        $this->sql_count += 1;

        return DB::table('devices AS d')
            ->join('modems_devices_rel AS mdr', 'd.id', '=', 'mdr.device_id')
            ->where('d.parent', 'IS NOT NULL')
            ->where('d.parent', '=', $tree_id)
            ->where('d.modem_id', 'IS NOT NULL')
            ->where('d.relation', '=', 'primary')->get(['d.*']);
    }

    public function registrators(array $device_id): array
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

    public function getSqlCount(): int
    {
        return $this->sql_count;
    }

    private function modems(array $device_idx): array
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

    private function modems_devices_rel(array $device_idx): array
    {
        if (!$device_idx) return [];

        $device_idx = join(',', $device_idx);

        $this->sql_count += 1;
        return DB::select("SELECT *
                                FROM modems_devices_rel
                                WHERE device_id IN (?)", [$device_idx]);
    }

    private function devices_registrators_rel(array $device_idx): array
    {
        if (!$device_idx) return [];

        $device_idx = join(',', $device_idx);

        $this->sql_count += 1;
        return DB::select("SELECT *
                                FROM devices_registrators_rel
                                WHERE device_id IN (?)", [$device_idx]);
    }

    public function setAction(string $action)
    {
        $this->action = $action;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
