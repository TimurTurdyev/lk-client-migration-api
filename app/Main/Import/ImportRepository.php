<?php

namespace App\Main\Import;

use App\Models\Device;
use App\Models\LkMigrations;
use App\Models\Modem;
use App\Models\Tree;
use App\Models\TreeData;

class ImportRepository implements ImportInterface
{
    private TableColumns $tables;
    private array $modem_not_found = [];

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
        if ($migrate = LkMigrations::findTreeOld($data['id'])->first()) {
            $tree = $migrate->import ?? new Tree();
        } else {
            $tree = new Tree();
        }

        $data['path'] = $path;
        $tree->fill($data)->save();
        $path = str_replace('..', '.', sprintf('%s.%s', $path, $tree->id));
        $this->migration($tree, $data['id']);

        return $path;
    }

    public function tree_data($data)
    {
        if ($migrate = LkMigrations::findTreeDataOld($data['element_id'])->first()) {
            $treeData = $migrate->import ?? new TreeData();
        } else {
            $treeData = new TreeData();
        }

        $treeData->fill($data)->save();
        $this->migration($treeData, $data['element_id']);
    }

    public function modems($data)
    {
        if (Modem::find($data['id'])) {
            return;
        }

        if ($migrate = LkMigrations::findDeviceOld($data['id'])->first()) {
            $modem = $migrate->import ?? new Modem();
        } else {
            $modem = new Modem();
        }

        $modem->fill($data)->save();
        $this->migration($modem, $data['id']);
    }

    public function devices($data)
    {
        if (isset($this->modem_not_found[$data['modem_id']]) || !Modem::find($data['modem_id'])) {
            $this->modem_not_found[$data['modem_id']] = '';
            return;
        }

        if ($migrate = LkMigrations::findDeviceOld($data['id'])->first()) {
            $device = $migrate->import ?? new TreeData();
        } else {
            $device = new Device();
        }

        $device->fill($data)->save();
        $this->migration($device, $data['id']);
    }

    public function registrators($data)
    {
        if (isset($this->modem_not_found[$data['modem_id']]) || !Modem::find($data['modem_id'])) {
            $this->modem_not_found[$data['modem_id']] = '';
            return;
        }

        if ($migrate = LkMigrations::findRegistratorOld($data['id'])->first()) {
            $registrators = $migrate->import ?? new TreeData();
        } else {
            $registrators = new Registrators();
        }

        $registrators->fill($data)->save();
        $this->migration($registrators, $data['id']);
    }

    public function migration($model, $old_id)
    {
        $data = ['old_id' => $old_id];

        if (!$model->import) {
            $model->import()->create($data);
        } else {
            $model->import->fill($data)->save();
        }
    }
}
