<?php

namespace App\Repositories\Contracts;

interface IGenericRepository
{
    public function get($page, $limit, $price, $order, $sort, $filter, $field, $market, $join, $category, $general, $active);
    public function getAll($price, $order, $sort, $filter, $field, $market, $join, $category, $general, $active);
    public function find($id, $field, $join);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);

}