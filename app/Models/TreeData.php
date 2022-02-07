<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreeData extends Model
{
    protected $connection = 'mysql_lk';

    protected $table = 'tree_data';
    public $timestamps = false;
    protected $guarded = ['element_id'];
    protected $primaryKey = 'element_id';

    public function import(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(LkMigration::class, 'importable');
    }
}
