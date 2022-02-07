<?php

namespace App\Main\Import;

interface ImportInterface
{
    public function getModemsNotFound(): array;

    public function prepare(string $table, array $values);

    public function tree(array $data, string $path = '.');

    public function tree_data(array $data);

    public function modems(array $data);

    public function devices(array $data);

    public function registrators(array $data);

    public function modems_devices_rel(array $data);

    public function devices_registrators_rel(array $data);
}
