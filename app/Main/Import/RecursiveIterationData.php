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
        foreach ($data as $key => $values) {
            if (empty($values)) {
                continue;
            }

            if (method_exists($this->repository, $key)) {
                if (in_array($key, ['modems', 'devices', 'registrators', 'modems_devices_rel', 'devices_registrators_rel'])) {
                    foreach ($values as $value) {
                        if ($data = $this->repository->prepare($key, $value)) {
                            $this->repository->{$key}($data);
                        }
                    }
                    continue;
                }

                if ($data = $this->repository->prepare($key, $values)) {
                    if ($p = $this->repository->{$key}($data, $path)) {
                        $path = $p;
                    }
                }
            }

            if (!is_string($key) || $key === 'data') {
                $this->apply($values, $path);
            }
        }
    }
}
