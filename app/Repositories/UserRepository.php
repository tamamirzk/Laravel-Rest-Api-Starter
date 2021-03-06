<?php

namespace App\Repositories;

use Exception;
use App\Models\User;
use App\Mail\Welcome;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ChangeEmail;
use App\Mail\ForgotPassword;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use MikeMcLin\WpPassword\Facades\WpPassword;
use App\Repositories\Contracts\IUserRepository;

class UserRepository extends GenericRepository implements IUserRepository{
    
    public function __construct()
    {
        parent::__construct(app(User::class));
    }

    public function login(array $data){
        $check_user = $this->model->where('username', $data['username'])->first();
        $password = $data['password'];
        
        if($check_user){
            if ( password_verify($password, $check_user['password']) == true ) {
                $user_id =  $check_user->user_id;

            } else {
                 $user_id =  null; 
            }
            return $user_id;
            
        }else { return null;}
        
    }

    public function register(array $data){
        $email = $data['email'];
        $data['status'] = 0;
        $data['role_id'] = 3;
        $data['password'] = Hash::make($data['password']);
        
        $user = $this->model->create($data);
        Mail::to($user)->send(new Welcome($user));

        if($user->id){
            $user_id =  $user->id;
        } else {
            $user_id =  null; 
        }

        return $this->query($user_id);

    }
    
    public function loginGoogle($email){
        $check_user = $this->model->where('email', '=', $email)->where('status', 1)->first();
        if($check_user){
            $user_id =  $check_user->id;
            return $this->query($user_id);
        }else { return null;}

    }

    public function registerGoogle($email){
        $data = [ 'email' => $email, 'role_id' => 3, 'status' => 0 ];
        $user = $this->model->create($data);
        Mail::to($email)->send(new Welcome($user));
        if($user){
            $user_id =  $user->id;
            return $this->query($user_id);
        }else { return null;}

    }

    public function emailRecovery(array $data){
        $check_user = $this->model->where('email', $data['email'])->first();
        $user = $check_user->toArray();
        $user['password'] = $data['old_password'];

        return $this->model->findOrFail($data['user_id'])->update($user);

    }

    public function verify($id){
        $data = $this->model->where('id', $id)->first();
        
        if($data){
            $user = $data->toArray();
            $user['status'] = 1; 

            $query = $this->model->findOrFail($id)->update($user);
            return $this->query($id);
        }else { return null; }

    }

    
    public function reqChangeEmail($email){
        $auth_email = auth()->user()->email;
        $user = $this->model->where('email', $email)->first();

        if($user){
            if($email == $auth_email){
                $response = Mail::to($email)->send(new changeEmail($user));
                return true;
    
            }else { return null; }

        }else { return null; }

    }

    public function changeEmail(array $data){
        $data['status'] = 0;
        $email = $data['email'];
        $user_id = $data['user_id'];

        $user = $this->model->findOrFail($user_id);
        $user->update($data);

        if($user){
            $response = Mail::to($email)->send(new Welcome($user));
            return true;

        }else { return null; }

    }

    public function forgot($email){
        $user = $this->model->where('email', $email)->first();

        if($user){
            $response = Mail::to($email)->send(new ForgotPassword($user));
            return true;

        }else { return null; }

    }

    public function reset($id,$token){
        $check_user = $this->model->select('email')->find($id);
        $user = $check_user ? $check_user->toArray() : null;

        if($user && $token) {
            $credentials = JWT::decode($token, $user['email'], ['HS256']);
            return $credentials;

        }else {
            return null;
        }

    }
    
    public function change(array $data){
        $user_id = $data['user_id'];
        $newPassword['password'] = Hash::make($data['password']);
        
        $query = $this->model->findOrFail($user_id);
        $query->update($newPassword);
        
        return $this->query($user_id);

    }

    public function getToken($user_id){
        $query = null;
        $token = $query ? $query->toArray()['api_token'] : null;

        if($token){
            try { $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']); } catch(Exception $e) { $credentials = null; }
            $access_payload = $credentials ? array('exp' => $credentials->exp) : [ 'iss' => "bearer",  'sub' => $user_id, 'iat' => time(), 'exp' => time() + 60*15 ];
            $refresh_payload = [ 'iss' => "bearer",  'sub' => $user_id, 'iat' => time(), 'exp' => time() + 60*1440 ];
            $access_token = $credentials ? $token : JWT::encode($access_payload, config('services.jwt.secret'));
            $refresh_token = JWT::encode($refresh_payload, config('services.jwt.secret_refresh'));
            $update_api_token = $credentials ? null : $this->apiToken($user_id, $access_token);

        }else{
            $access_payload = [ 'iss' => "bearer",  'sub' => $user_id, 'iat' => time(), 'exp' => time() + 60*15 ];
            $refresh_payload = [ 'iss' => "bearer",  'sub' => $user_id, 'iat' => time(), 'exp' => time() + 60*1440 ];
            $access_token = JWT::encode($access_payload, config('services.jwt.secret'));
            $refresh_token = JWT::encode($refresh_payload, config('services.jwt.secret_refresh'));
            $update_api_token = $this->apiToken($user_id, $access_token);
        }
        
        return response()->json([
            'token_type' => 'Bearer',
            'expires_in' => $access_payload['exp'],
            'access_token' => $access_token,
            'refresh_token' => $refresh_token

        ]);
        
        
    }

    public function getRefreshToken($refresh_token){
        $credentials = JWT::decode($refresh_token, config('services.jwt.secret_refresh'), ['HS256']);
        $user_id = $credentials->sub;
        if($user_id){
            return $this->getToken($user_id);
        }else{
            return null;
        }
    }

    public function apiToken($user_id, $token){  
        $query = $this->model->findOrFail($user_id);
        $data = $query->toArray();
        $data['api_token'] = $token;
        $query->update($data);
        return $query;
    }

    public function query($user_id){  
        if($user_id){
            $query = $this->model
            ->select('users.id','shippings.id AS shipping_id', 'users.first_name', 'users.last_name', 'email', 'phone', 'company', 'address_1', 'address_2', 'postcode', 'city', 'state', 'country', 'label', 'position')
            ->join('shippings', 'shippings.user_id', '=', 'users.id')
            ->where('users.id', '=' , $user_id)
            ->limit(1)
            ->get();

            if(count($query)){
                return $query;
            }else{
                $query =  $this->model->where('users.id', '=' , $user_id)->get();
                return $query;
            }
        }else{
            return null;
        };
        
    }

    public function userUpdate($id, array $data)
    {
        $query = $this->model->findOrFail($id);
        $password = $data['password'];

        $data_user = array(
            "first_name" => $data['first_name'],
            "last_name" => $data['last_name'],
        );
        if($password){ $data_user['password'] = Hash::make($password); }
        
        $query->update(array_filter($data_user));
        return $query;
    }
    
}