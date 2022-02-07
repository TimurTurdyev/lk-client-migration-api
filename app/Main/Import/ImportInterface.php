<?php

namespace App\Main\Import;

interface ImportInterface
{
    public function prepare(string $table, array $values);

    public function tree(array $data, string $path = '.');

    public function tree_data(array $data);

    public function devices(array $data);

    public function registrators(array $data);
}
