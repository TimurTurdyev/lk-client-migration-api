<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $connection = 'mysql_lk';

    protected $table = 'devices';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $primaryKey = 'id';

    public function import(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(MigrateTree::class, 'importable');
    }
}
