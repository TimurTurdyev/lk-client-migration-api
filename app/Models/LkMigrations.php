<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkMigrations extends Model
{
    use HasFactory;

    protected $table = 'lk_migrations';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['table', 'old_id', 'new_id', 'created_at'];
    protected $primaryKey = 'id';

    protected $casts = ['created_at' => 'datetime'];
}
