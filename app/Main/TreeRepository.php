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

    public function findToPath($path): array
    {
        return DB::select("SELECT tree.* FROM tree WHERE path = ?", [(string)$path]);
    }

    public function searchPathToDepth($tree): array
    {
        $list_search = [];

        foreach ($tree as $list) {
            $devices = $this->devices($list->id);
            $modems = [];

            foreach ($devices as $device) {
                if ($device->modem_id) {
                    $modems[$device->modem_id] = '';
                }
            }

            $find_tree = $this->findToPath(str_replace('..', '.', sprintf('%s.%s', $list->path, $list->id)));

            $list_search[$list->id] = [
                'tree' => $list,
                'tree_data' => $this->treeData($list->id),
                'devices' => $devices,
                'registrators' => $this->registrators(array_keys($modems)),
                'data' => $this->searchPathToDepth($find_tree)
            ];
        }

        return $list_search;
    }

    public function searchPathCallback($tree, $callback, $parent = 0): void
    {
        foreach ($tree as $list) {
            $devices = $this->devices($list->id);
            $modems = [];

            foreach ($devices as $device) {
                if ($device->modem_id) {
                    $modems[$device->modem_id] = '';
                }
            }
            $id = $list->id;
            $path = str_replace('..', '.', sprintf('%s.%s', $list->path, $id));

            $find_tree = $this->findToPath($path);

            $callback([
                'tree' => $list,
                'tree_data' => $this->treeData($id),
                'devices' => $devices,
                'registrators' => $this->registrators(array_keys($modems)),
            ], $parent);

            $this->searchPathCallback($find_tree, $callback, $id);
        }
    }

    public function treeData(int $tree_id)
    {
        $this->sql_count += 1;
        return DB::selectOne("SELECT * FROM tree_data WHERE element_id = ?", [$tree_id]);
    }

    public function devices(string $tree_id): array
    {
        $this->sql_count += 1;
        return DB::select("SELECT `id`, `class`, `parent`, `name`, `config_id`, `device_sn`, `modem_id`,
                                    `specific_loss_factor`, `outgoing_line_length`, `verification_date`,
                                    `billing_date`, `network_id`, `device_time`, `config_time`, `timezone`, `data_source`,
                                    `config_source`, `reg_way`, `status_flags`, `status_messages`, `relation`, `display`, `rates`, `demo_parent`
                                FROM devices
                                WHERE parent IS NOT NULL AND parent = ?", [$tree_id]);
    }

    public function registrators(array $modems): array
    {
        if (!$modems) return [];

        $modems = join(',', $modems);

        $this->sql_count += 1;
        return DB::select("SELECT `id`, `name`, `modem_id`, `network_id`, `device_id`, `channel_id`, `serial`, `unit_id`, `offset`,
                                    `multiplier`, `scaler`, `transform`, `full_counter`, `modem_value`, `last_value`, `last_value_timestamp`,
                                    `isactive`, `reg_way`, `extended`, `deleted`, `moderated`, `billing_init_value`, `billing_init_timestamp`,
                                    inReckon, `data_source`, `verification_report`, `profile`
                                FROM registrators
                                WHERE modem_id IN (?)", [$modems]);
    }

    public function getSqlCount(): int
    {
        return $this->sql_count;
    }
}
