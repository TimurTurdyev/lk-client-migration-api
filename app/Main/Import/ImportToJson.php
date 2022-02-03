<?php

namespace App\Main\Import;

use App\Models\Device;
use App\Models\LkMigrations;
use App\Models\Tree;
use App\Models\TreeData;

class ImportToJson
{
    private TableColumns $tables;

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

    public function tree($data)
    {
        if ($migrate = LkMigrations::where('table', 'tree')->where('old_id', $data['id'])->first()) {
            $tree = Tree::find($migrate->new_id) ?? new Tree();
        } else {
            $tree = new Tree();
        }

        $tree->fill($data)->save();
        $this->migrateData('tree', $data['id'], $tree->id);

        return $this;
    }

    public function tree_data($data)
    {
        if ($migrate = LkMigrations::where('table', 'tree_data')->where('old_id', $data['element_id'])->first()) {
            $treeData = TreeData::find($migrate->new_id) ?? new TreeData();
        } else {
            $treeData = new TreeData();
        }

        $treeData->fill($data)->save();
        $this->migrateData('tree_data', $data['element_id'], $treeData->element_id);

        return $this;
    }

    public function devices($data)
    {
        if ($migrate = LkMigrations::where('table', 'devices')->where('old_id', $data['id'])->first()) {
            $device = Device::find($migrate->new_id) ?? new Device();
        } else {
            $device = new Device();
        }

        $device->fill($data)->save();
        $this->migrateData('devices', $data['id'], $device->id);

        return $this;
    }

    public function registrators($data)
    {
        if ($migrate = LkMigrations::where('table', 'registrators')->where('old_id', $data['id'])->first()) {
            $registrators = Registrators::find($migrate->new_id) ?? new Registrators();
        } else {
            $registrators = new Registrators();
        }

        $registrators->fill($data)->save();
        $this->migrateData('registrators', $data['id'], $registrators->id);

        return $this;
    }

    public function migrateData($table, $old_id, $new_id)
    {
        $migrateData = new LkMigrations(['table' => $table, 'old_id' => $old_id, 'new_id' => $new_id]);
        $migrateData->save();

        return $this;
    }
}
