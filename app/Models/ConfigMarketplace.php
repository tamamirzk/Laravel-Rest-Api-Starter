<?php   
namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class ConfigMarketplace extends Model
{

    protected $table = "config_marketplace";

    protected $fillable = [
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
    
    protected $defaultSort = 'asc';

    public function getSortDirection()
    {
        return $this->defaultSort;
    }
}