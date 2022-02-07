<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkMigration extends Model
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
     * @return LkMigration|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindTreeOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', 'tree')
            ->where('old_id', $old_id);
    }

    /**
     * @param $old_id
     * @return LkMigration|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindTreeDataOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', 'tree_data')
            ->where('old_id', $old_id);
    }

    /**
     * @param $old_id
     * @return LkMigration|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindModem($builder, $modem_id)
    {
        return $builder
            ->where('importable_type', 'modems')
            ->where('old_id', $modem_id);
    }

    /**
     * @param $old_id
     * @return LkMigration|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindDeviceOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', 'devices')
            ->where('old_id', $old_id);
    }

    /**
     * @param $old_id
     * @return LkMigration|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeFindRegistratorOld($builder, $old_id)
    {
        return $builder
            ->where('importable_type', 'registrators')
            ->where('old_id', $old_id);
    }
}
