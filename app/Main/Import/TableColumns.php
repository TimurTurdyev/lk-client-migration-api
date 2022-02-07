<?php

namespace App\Main\Import;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TableColumns
{
    private Collection $schema;

    public function __construct()
    {
        $this->schema = collect();
    }

    public function getColumns($table): Collection
    {
        if (!$this->schema->has($table)) {
            $this->schema->put($table, collect(array_flip(DB::connection('mysql_lk')->getSchemaBuilder()->getColumnListing($table))));
        }

        if ($this->schema->get($table) === null) {
            dd(['getColumns' => $table, 'columns' => $this->schema->get($table)]);
        }

        return $this->schema->get($table);
    }
}
