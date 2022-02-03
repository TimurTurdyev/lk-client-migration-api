<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registrator extends Model
{
    use HasFactory;

    protected $connection = 'mysql_lk';

    protected $table = 'devices';
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = 'id';
}
