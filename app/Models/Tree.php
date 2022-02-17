<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Watson\Rememberable\Rememberable;

class Tree extends Model
{
    use Filterable, AsSource, Rememberable;

    protected $connection = 'mysql_lk';

    public $timestamps = false;
    protected $table = 'tree';
    protected $guarded = ['id'];
    protected $primaryKey = 'id';

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id',
        'name',
        'time_zone',
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'time_zone',
    ];

    public function treeChild()
    {
        return $this->hasOne($this, 'id')->orWhere('path', 'like', DB::raw("REPLACE(CONCAT(tree.path, '.', tree.id, '%'), '..', '.')"));
    }

    public function import(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(MigrateTree::class, 'importable');
    }

    public static function count()
    {
        return Cache::remember('tree_count', 60 * 5, function () {
            return static::query()->count();
        });
    }
}
