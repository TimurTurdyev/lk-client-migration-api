<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modem extends Model
{
    protected $connection = 'mysql_lk';

    protected $table = 'modems';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $primaryKey = 'id';

    public function import(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(LkMigrations::class, 'importable');
    }
}