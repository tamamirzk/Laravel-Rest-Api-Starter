<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, Filterable;

    protected $table = "user";
    protected $guarded = array('id');
    protected $primaryKey = "id";


    protected $fillable = [
        //  'first_name',
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

    public function tokenRevoke(){
        $user_id = auth()->user()->id;
        if($user_id){
            $query = $this->findOrFail($user_id);
            $data = $query->toArray();
    
            if($data){
                $data['api_token'] = null;
                $query->update($data);
                return true;
            }else{ return false; }

        }else{ return false; };
    }
}
