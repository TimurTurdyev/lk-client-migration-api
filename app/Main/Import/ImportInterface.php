<?php

namespace App\Main\Import;

interface ImportInterface
{
    public function prepare(string $table, array $values);

    public function tree(array $tree, array $tree_data, string $path = '.');

    public function data_to_tree(array $data);
}
