<?php

namespace App\Repositories;
 
use App\Repositories\Contracts\IGenericRepository;

abstract class GenericRepository implements IGenericRepository
{
    protected $model;

    protected $callback = true;

    public function __construct($model, $callback = true)
    {
        $this->model = $model;
        $this->callback = $callback;
    }

    public function get($page, $limit, $price = null, $order = null, $sort = null, $filter = null, $field = [], $market = null, $join = [], $category = null, $general = null, $active = null)
    {        
        $orderBy = $order ? $order : $this->model->getKeyName();
        $sortBy = $sort ? $sort : $this->model->getSortDirection();

        $data = [
            'name' =>  $filter,
            'field' =>  $field,
            'price'  =>  $price,
            'market'  =>  $market,
            'category'  =>  $category,
            'general'  =>  $general,
            'active'  =>  $active
        ];

        if ($join) { 
            $query = $this->model
                ->join($join[0], $join[1], '=', $join[2]);

        } else{ $query = $this->model; }

        return $query->filter($data)
            ->orderBy($orderBy, $sortBy)
            ->offset(($page - 1) * $page)
            ->limit($limit)
            ->paginate($limit);
    }
    
    public function getAll($price = null ,$order = null, $sort = null, $filter = null, $field = [], $market = null, $join = [], $category = null, $general = null, $active = null)
    {
        $orderBy = $order ? $order : $this->model->getKeyName();
        $sortBy = $sort ? $sort : $this->model->getSortDirection();

        $data = [
            'name' =>  $filter,
            'field' =>  $field,
            'price'  =>  $price,
            'market'  =>  $market,
            'category'  =>  $category,
            'general'  =>  $general,
            'active'  =>  $active
        ];

        if ($join) { 
            $query = $this->model->join($join[0], $join[1], '=', $join[2]);

        } else{ $query = $this->model; }
            
        return $query->filter($data)->orderBy($orderBy, $sortBy)->get();
    }
    
    public function find($id, $field = null, $join = [])
    {
        $data = [
            'id' =>  $id,
            'field' =>  $field,
        ];

        if ($join) { 
            $query = $this->model
                ->join($join[0], $join[1], '=', $join[2])
                ->where($join[1] , '=' , $id);

        } else{ $query = $this->model; }

        return $query->filter($data)->get();
    }

    
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $query = $this->model->findOrFail($id);

        $query->update($data);
        return $query;
    }

    public function delete($id){
        $query = $this->model->findOrFail($id);
        return $query->delete();
    }

}