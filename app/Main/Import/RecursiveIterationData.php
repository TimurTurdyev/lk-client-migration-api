<?php

namespace App\Main\Import;

class RecursiveIterationData
{
    private ImportInterface $repository;

    public function __construct(ImportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function apply(array $data, $path = '.'): void
    {
        if (!isset($data['tree'])) {
            foreach ($data as $values) {
                $this->apply($values, $path);
            }
            return;
        }

        $path = $this->repository->tree($data['tree'], $data['tree_data'], $path) ?: $path;

        if (!empty($data['data'])) {
            $this->apply($data['data'], $path);
        };

        if (isset($data['data_to_tree'])) {
            $this->repository->data_to_tree($data['data_to_tree']);
        }
    }
}
