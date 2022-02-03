<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreeData extends Model
{
    use HasFactory;

    protected $connection = 'mysql_lk';

    protected $table = 'tree_data';
    public $timestamps = false;
    protected $guarded = [];
    protected $primaryKey = 'element_id';
}
