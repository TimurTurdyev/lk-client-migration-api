<?php

namespace App\Main\Import;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TableColumns
{
    private Collection $schema;

    private array $tables = [
        'tree', 'tree_data', 'devices', 'registrators'
    ];

    public function __construct()
    {
        $this->schema = collect();

        foreach ($this->tables as $table) {
            $this->schema->put($table, DB::connection()->getSchemaBuilder()->getColumnListing($table));
        }
    }

    public function getColumns($table): Collection
    {
        return $this->schema->get($table);
    }
}
