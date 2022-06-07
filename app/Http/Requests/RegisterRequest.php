<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegisterRequest
{
    public function __construct($data)
    {
        if (empty($data)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Request was empty');
        }

        $validator = Validator::make($data, [
            "email" => "required|email|unique:users",
            "password" => "required|string",
        ]);

        if ($validator->fails())
            throw new ValidationException($validator, $validator->errors(), 'fail');

        $this->map((object)$data);
    }

    private function map($object)
    {
        $this->email = property_exists($object, 'email') ? $object->email : null;
        $this->password = property_exists($object, 'password') ? $object->password : null;
    }

    public function parse()
    {
        $result = array(
            'email' => $this->email,
            'password' => $this->password,
        );

        return array_filter($result, function ($value) {
            return !empty($value) || $value === 0;
        });
    }
}