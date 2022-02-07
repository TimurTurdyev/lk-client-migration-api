<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Tree extends Model
{
    use Filterable, AsSource;

    protected $connection = 'mysql_lk';

    public $timestamps = false;
    protected $table = 'tree';
    protected $guarded = ['id'];
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];

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
        return $this->morphOne(LkMigrations::class, 'importable');
    }
}
