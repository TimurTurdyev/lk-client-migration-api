<?php

namespace App\Main;

use Illuminate\Support\Facades\DB;

class TreeRepository
{
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
            $device_id = [];

            foreach ($devices as $device) {
                if ($device->id) {
                    $device_id[$device->id] = '';
                }
            }

            $find_tree = $this->findToPath(str_replace('..', '.', sprintf('%s.%s', $list->path, $list->id)));

            $list_search[$list->id] = [
                'tree' => $list,
                'tree_data' => $this->treeData($list->id),
                'devices' => $devices,
                'registrators' => $this->registrators(array_keys($device_id)),
                'data' => $this->searchPathToDepth($find_tree)
            ];
        }

        return $list_search;
    }

    public function searchPathCallback($tree, $callback, $depth = 0)
    {
        foreach ($tree as $list) {
            $devices = $this->devices($list->id);
            $device_id = [];

            foreach ($devices as $device) {
                if ($device->id) {
                    $device_id[$device->id] = '';
                }
            }

            $find_tree = $this->findToPath(str_replace('..', '.', sprintf('%s.%s', $list->path, $list->id)));

            $callback([
                'tree' => $list,
                'tree_data' => $this->treeData($list->id),
                'devices' => $devices,
                'registrators' => $this->registrators(array_keys($device_id)),
            ], $depth);

            $this->searchPathCallback($find_tree, $callback, $depth + 1);
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
        return DB::select("SELECT `id`, `class`, `parent`, `name`, `config_id`, `device_sn`, `modem_id`,
                                    `specific_loss_factor`, `outgoing_line_length`, `verification_date`,
                                    `billing_date`, `network_id`, `device_time`, `config_time`, `timezone`, `data_source`,
                                    `config_source`, `reg_way`, `status_flags`, `status_messages`, `relation`, `display`, `rates`, `demo_parent`
                                FROM devices
                                WHERE parent IS NOT NULL AND parent = ?", [$tree_id]);
    }

    public function registrators(array $device_id)
    {
        if (!$device_id) return [];

        $device_id = join(',', $device_id);

        $this->sql_count += 1;
        return DB::select("SELECT r.`id`, r.`name`, r.`modem_id`, r.`network_id`, r.`device_id`, r.`channel_id`, r.`serial`, r.`unit_id`, r.`offset`,
                                    `multiplier`, r.`scaler`, r.`transform`, r.`full_counter`, r.`modem_value`, r.`last_value`, r.`last_value_timestamp`,
                                    `isactive`, r.`reg_way`, r.`extended`, r.`deleted`, r.`moderated`, r.`billing_init_value`, r.`billing_init_timestamp`,
                                    inReckon, `data_source`, r.`verification_report`, r.`profile`
                                FROM registrators r
                                JOIN devices_registrators_rel drr ON r.id = drr.registrator_id
                                WHERE drr.device_id IN (?)
                                GROUP BY r.id", [$device_id]);
    }

    public function getSqlCount()
    {
        return $this->sql_count;
    }
}
