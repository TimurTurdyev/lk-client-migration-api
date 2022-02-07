<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkMigrations extends Model
{
    public $connection = 'mysql';

    protected $table = 'lk_migrations';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['importable_type', 'importable_id', 'old_id', 'created_at'];
    protected $primaryKey = 'id';

    protected $casts = ['created_at' => 'datetime'];

    public function importable()
    {
        return $this->morphTo();
    }

    /**
     * @param $old_id
     * @return LkMigrations|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindTreeOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', Tree::class)
            ->where('old_id', $old_id);
    }

    /**
     * @param $old_id
     * @return LkMigrations|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindTreeDataOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', TreeData::class)
            ->where('old_id', $old_id);
    }

    /**
     * @param $old_id
     * @return LkMigrations|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindDeviceOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', Device::class)
            ->where('old_id', $old_id);
    }

    /**
     * @param $old_id
     * @return LkMigrations|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindRegistratorOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', Registrator::class)
            ->where('old_id', $old_id);
    }
}
