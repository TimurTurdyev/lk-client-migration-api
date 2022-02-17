<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MigrateTree extends Model
{
    public $connection = 'sqlite';

    protected $table = 'migrate_trees';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $fillable = ['old_id', 'new_id', 'created_at'];
    public $timestamps = false;

    protected $casts = ['created_at' => 'datetime'];

    /**
     * @param $old_id
     * @return MigrateTree|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindNewId($builder, $old_id)
    {
        return $builder
            ->where('old_id', $old_id);
    }
}
