<?php

namespace App\Main\Import;

use App\Models\Device;
use App\Models\MigrateTree;
use App\Models\Modem;
use App\Models\Tree;
use App\Models\TreeData;
use Illuminate\Support\Facades\DB;

class ImportRepository implements ImportInterface
{
    private TableColumns $tables;

    public function __construct()
    {
        $this->tables = new TableColumns();
        DB::setDefaultConnection('mysql_lk');
    }

    public function prepare(string $table, array $values): array
    {
        $columns = $this->tables->getColumns($table);

        foreach ($values as $key => $value) {
            if (!$columns->has($key)) {
                unset($values[$key]);
            }
        }

        return $values;
    }

    public function tree($tree, $tree_data = [], $path = '.')
    {
        $tree = $this->prepare('tree', $tree);
        $tree_data = $this->prepare('tree_data', $tree_data ?? []);

        $old_id = $tree['id'];
        $tree_id = null;

        if ($migrateTree = MigrateTree::findNewId($old_id)->first()) {
            $tree_id = $migrateTree->new_id;
        }

        $tree['id'] = $tree_id;
        $tree['path'] = $path;

        $modelTree = Tree::updateOrCreate([
            'id' => $tree['id']
        ], $tree);


        $path = str_replace('..', '.', sprintf('%s.%s', $path, $modelTree->id));

        if ($tree_data) {
            $tree_data['element_id'] = $modelTree->id;

            TreeData::updateOrCreate([
                'element_id' => $tree_data['element_id']
            ], $tree_data);
        }

        if (is_null($tree_id)) {
            MigrateTree::updateOrCreate([
                'old_id' => $old_id,
                'new_id' => $modelTree->id
            ]);
        }

        return $path;
    }

    public function data_to_tree(array $rows)
    {
        foreach ($rows as $row) {
            $migrateTree = MigrateTree::findNewId($row['tree_id'])->first();

            if (empty($migrateTree)) {
                continue;
            }

            foreach ($row['modems'] as $modem) {
                if ($data = $this->prepare('modems', $modem)) {
                    Modem::updateOrCreate([
                        'id' => $modem['id']
                    ], $data);
                }
            }

            foreach ($row['devices'] as $device) {

                $device['parent'] = $migrateTree->new_id;

                if ($data = $this->prepare('devices', $device)) {
                    $modem_id = $device['modem_id'];

                    $devicePrimary = Device::updateOrCreate([
                        'parent' => $device['parent'],
                        'modem_id' => $device['modem_id'],
                        'relation' => 'primary'
                    ], $data);

                    DB::table('modems_devices_rel')
                        ->insertOrIgnore([
                            'device_id' => $devicePrimary->id,
                            'modem_id' => $modem_id,
                        ]);

                    $device_secondary = DB::table('devices AS d')
                        ->join('modems_devices_rel AS mdr', 'd.id', '=', 'mdr.device_id')
                        ->where('d.parent', '=', $device['parent'])
                        ->where('d.relation', 'secondary')
                        ->where('mdr.modem_id', '=', $device['modem_id'])
                        ->first(['d.id']);

                    $data['config_time'] = 0;
                    $data['status_messages'] = '';
                    $data['modem_id'] = null;
                    $data['relation'] = 'secondary';

                    $deviceSecondary = Device::updateOrCreate([
                        'id' => $device_secondary?->id
                    ], $data);

                    DB::table('modems_devices_rel')
                        ->insertOrIgnore([
                            'device_id' => $deviceSecondary->id,
                            'modem_id' => $modem_id,
                        ]);

                    $devicesRegistrators = DB::table('devices_registrators_rel')
                        ->where('device_id', $devicePrimary->id)
                        ->get();

                    foreach ($devicesRegistrators as $devicesRegistrator) {
                        DB::table('devices_registrators_rel')
                            ->insertOrIgnore([
                                'registrator_id' => $devicesRegistrator->registrator_id,
                                'device_id' => $deviceSecondary->id,
                            ]);
                    }
                }
            }
        }
    }

    public function connect_by_primary_devices(array $devices)
    {
        foreach ($devices as $device) {
            $tree = MigrateTree::findTreeOld($device['tree_id'])->first();

            if (empty($tree)) {
                continue;
            }

            $queryResults = DB::table('devices')
                ->where('modem_id', '=', $device['modem_id'])
                ->where('relation', 'primary')
                ->get();

            if (!$queryResults->count()) {
                $this->modems_not_found[(string)$device['modem_id']] = 0;
                continue;
            }

            foreach ($queryResults as $result) {
                DB::table('devices')
                    ->where('id', '=', $result->id)
                    ->update([
                        'parent' => $tree->importable_id
                    ]);

                if (DB::table('devices AS d')
                    ->join('modems_devices_rel AS mdr', 'd.id', '=', 'mdr.device_id')
                    ->where('d.parent', '=', $tree->importable_id)
                    ->where('d.relation', 'secondary')
                    ->where('mdr.modem_id', '=', $device['modem_id'])
                    ->count(['d.id'])) {
                    continue;
                }

                $device_primary_id = $result->id;
                $modem_id = $result->modem_id;

                unset($result->id);

                $result->parent = $tree->importable_id;
                $result->config_time = 0;
                $result->status_messages = '';
                $result->modem_id = null;
                $result->relation = 'secondary';

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
