<?php

namespace App\Main\Import;

use App\Models\Device;
use App\Models\LkMigration;
use App\Models\Modem;
use App\Models\Registrator;
use App\Models\Tree;
use App\Models\TreeData;
use Illuminate\Support\Facades\DB;

class ImportRepository implements ImportInterface
{
    private TableColumns $tables;
    private array $modems_not_found = [];

    public function __construct()
    {
        $this->tables = new TableColumns();
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

    public function tree($data, $path = '.')
    {
        $old_id = $data['id'];
        $tree_id = null;

        if ($migrate = LkMigration::findTreeOld($data['id'])->first()) {
            $tree_id = $migrate->importable_id;
        } else {
            unset($data['id']);
        }

        $data['path'] = $path;

        $tree = Tree::updateOrCreate([
            'id' => $tree_id
        ], $data);

        $path = str_replace('..', '.', sprintf('%s.%s', $path, $tree->id));

        $this->migration([
            'importable_type' => 'tree',
            'importable_id' => $tree->id,
            'old_id' => $old_id,
        ]);

        return $path;
    }

    public function tree_data($data)
    {
        $old_id = $data['element_id'];
        $tree_id = null;

        if ($migrate = LkMigration::findTreeDataOld($data['element_id'])->first()) {
            $tree_id = $migrate->importable_id;
        } else {
            unset($data['element_id']);
        }

        $tree_data = TreeData::updateOrCreate([
            'element_id' => $tree_id
        ], $data);

        $this->migration([
            'importable_type' => 'tree_data',
            'importable_id' => $tree_data->element_id,
            'old_id' => $old_id,
        ]);
    }

    public function modems($data)
    {
        Modem::updateOrCreate([
            'id' => $data['id']
        ], $data);
    }

    public function devices($data)
    {
        $old_id = $data['id'];
        $device_id = null;

        if ($migrate = LkMigration::findDeviceOld($data['id'])->first()) {
            $device_id = $migrate->importable_id;
        } else {
            unset($data['id']);
        }

        $device = Device::updateOrCreate([
            'id' => $device_id
        ], $data);

        $this->migration([
            'importable_type' => 'devices',
            'importable_id' => $device->id,
            'old_id' => $old_id,
        ]);
    }

    public function registrators($data)
    {
        $old_id = $data['id'];
        $registrator_id = null;

        if ($migrate = LkMigration::findRegistratorOld($data['id'])->first()) {
            $registrator_id = $migrate->importable_id;
        } else {
            unset($data['id']);
        }

        $registrators = Registrator::updateOrCreate([
            'id' => $registrator_id
        ], $data);

        $this->migration([
            'importable_type' => 'devices',
            'importable_id' => $registrators->id,
            'old_id' => $old_id,
        ]);
    }

    public function modems_devices_rel($data)
    {
        if ($find = LkMigration::findDeviceOld($data['device_id'])->first()) {
            DB::connection('mysql_lk')
                ->table('modems_devices_rel')
                ->insertOrIgnore([
                    'modem_id' => $data['modem_id'],
                    'device_id' => $find->importable_id,
                ]);
        }
    }

    public function devices_registrators_rel($data)
    {
        if (
            ($registrator = LkMigration::findRegistratorOld($data['registrator_id'])->first()) &&
            ($device = LkMigration::findDeviceOld($data['device_id'])->first())
        ) {
            DB::connection('mysql_lk')
                ->table('devices_registrators_rel')
                ->insertOrIgnore([
                    'registrator_id' => $registrator->importable_id,
                    'device_id' => $device->importable_id
                ]);
        }
    }

    public function connect_by_primary_devices(array $devices)
    {
        foreach ($devices as $device) {
            if (!($tree = LkMigration::findTreeOld($device['tree_id'])->first())) {
                continue;
            }

            $queryResults = DB::connection('mysql_lk')
                ->table('devices')
                ->where('parent', '<>', $tree->new_id)
                ->where('modem_id', '=', $device['modem_id'])
                ->where('relation', 'primary')
                ->get();

            foreach ($queryResults as $result) {
                if (empty($result->modem_id)) {
                    continue;
                }

                DB::connection('mysql_lk')
                    ->table('devices')
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
                $result->relation = 'secondary';

                $last_id = DB::table('devices')
                    ->insertGetId((array)$result);

                DB::connection('mysql_lk')
                    ->table('modems_devices_rel')
                    ->insertOrIgnore([
                        'device_id' => $last_id,
                        'modem_id' => $modem_id,
                    ]);

                $devicesRegistrators = DB::connection('mysql_lk')
                    ->table('devices_registrators_rel')
                    ->where('device_id', $device_primary_id)->get();

                foreach ($devicesRegistrators as $devicesRegistrator) {
                    DB::connection('mysql_lk')
                        ->table('devices_registrators_rel')
                        ->insertOrIgnore([
                            'registrator_id' => $devicesRegistrator->registrator_id,
                            'device_id' => $last_id,
                        ]);
                }
            }
        }
    }

    public function migration($data)
    {
        LkMigration::updateOrCreate($data, $data);
    }

    public function getModemsNotFound(): array
    {
        return $this->modems_not_found;
    }
}
