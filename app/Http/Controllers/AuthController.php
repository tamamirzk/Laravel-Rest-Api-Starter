<?php

namespace App\Http\Controllers;

use Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Transformers\UserTransformer;
use App\Repositories\Contracts\IUserRepository;

class AuthController extends Controller
{
    private $repo;
    public function __construct(IUserRepository $repo){
        $this->repo = $repo;
    }

    
    public function login(Request $request)
    {
        // $password_hash = password_hash('password123', PASSWORD_DEFAULT);
        // $password_verify = password_verify('password', '$2y$10$Gs4PbeW2ZsLQ6P.nkalhXOI.TSAgIJi3lSfQiyvM4.m5wnCtIIT6m');
        // dd($password_verify);
        // try {
            $username = $request->username;
            $password = $request->password;

            $patch = new LoginRequest($request->all());
            $credentials = $patch->parse();

            if ($credentials){
                $user_id = $this->repo->login($credentials);
                if ($user_id){
                    $token = $this->repo->getToken($user_id);
                    return $this->buildResponseWithToken($result, new UserTransformer(), json_decode($token->getContent()), 'success');
                } else {
                    return $this->buildErrorResponse('error' , 'User Not Found', 404);
                }
            }
        // } catch (\Exception $exception) {
        //     return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        // }
    }

    public function register(Request $request)
    {
        try {
            $email = $request->email;
            $password = $request->password;

            $patch = new RegisterRequest($request->all());
            $credentials = $patch->parse();

            if ($credentials){
                $result = $this->repo->register($credentials);
                if ($result){
                    $user_id = $result->toArray()[0]['id'];
                    $token = $this->repo->getToken($user_id);
                    return $this->buildResponseWithToken($result, new UserTransformer(), json_decode($token->getContent()), 'success');
                } else {
                    return $this->buildErrorResponse('error' , 'User Not Found', 404);
                }
            }
        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
    }

    public function loginGoogle(Request $request)
    {
        try {
            if (request('access_token')){
                if (request('email')){
                    $result = $this->repo->loginGoogle(request('email'));
                    if ($result){
                        $user_id = $result->toArray()[0]['id'];
                        $token = $this->repo->getToken($user_id);
                        return $this->buildResponseWithToken($result, new UserTransformer(), json_decode($token->getContent()), 'success');
                    } else {
                        return $this->buildErrorResponse('error' , 'User Not Found', 404);
                    }
                } else {
                    return $this->buildErrorResponse('error' , 'User Not Found', 404);
                }
            }else {
                return $this->buildErrorResponse('error' , 'User Not Found', 404);
            }
        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
            
    }

    public function registerGoogle(Request $request)
    {
        try {
            if (request('access_token')){
                if (request('email')){
                    $result = $this->repo->registerGoogle(request('email'));
                    if ($result){
                        $user_id = $result->toArray()[0]['id'];
                        $token = $this->repo->getToken($user_id);
                        return $this->buildResponseWithToken($result, new UserTransformer(), json_decode($token->getContent()), 'success');
                    } else {
                        return $this->buildErrorResponse('error' , 'User Not Found', 404);
                    }
                } else {
                    return $this->buildErrorResponse('error' , 'User Not Found', 404);
                }
            }else {
                return $this->buildErrorResponse('error' , 'User Not Found', 404);
            }
        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
            
    }

    public function verify($id)
    {
        try {
            if ($id){
                $result = $this->repo->verify($id);
                if ($result){
                    return view('notifications.success');
                } else {
                    return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
                }
            }

        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
    }

    public function reqChangeEmail()
    {
        $email = Request('email');
        try {
            if ($email){
                $result = $this->repo->reqChangeEmail($email);
                if ($result){
                    return $this->buildResponse('success', 'Email Sent', 200);
                } else {
                    return $this->buildErrorResponse('error' , 'Email Not Found', 404);
                }
            }

        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
    }

    public function verifyChangeEmail($id)
    {
        try {
            $token = request('token');
            $result = $this->repo->reset($id,$token);
            if($result){
                return view('change-email')->with(['id' => $id]);
            } else {
                return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
            }

        } catch (\Exception $exception) {
            return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
        }

    }
    
    public function changeEmail(Request $request)
    {
        try {
            $patch = new ChangeEmailRequest($request->all());
            $data = $patch->parse();

            $result = $this->repo->changeEmail($data);
            if ($result){
                return view('notifications.general')->with(['message' => 'Please verify your account, we sent notification to your new email!']);
            } else {
                return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
            }

        } catch (\Exception $exception) {
            return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
        }
    }

    public function reset($id)
    {
        try {
            $token = request('token');
            $result = $this->repo->reset($id,$token);
            if($result){
                return view('change-password')->with(['id' => $id]);
            } else {
                return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
            }

        } catch (\Exception $exception) {
            return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
        }

    }

    public function forgot()
    {
        $email = Request('email');
        try {
            if ($email){
                $result = $this->repo->forgot($email);
                if ($result){
                    return $this->buildResponse('success', 'Email Sent', 200);
                } else {
                    return $this->buildErrorResponse('error' , 'Email Not Found', 404);
                }
            }

        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
    }
  
    public function change(Request $request)
    {
        try {
            $patch = new ChangePasswordRequest($request->all());
            $data = $patch->parse();

            $result = $this->repo->change($data);
            if ($result){
                return view('notifications.success');
            } else {
                return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
            }

        } catch (\Exception $exception) {
            return view('notifications.general')->with(['message' => 'Ups Something Wrong!']);
        }
    }
    
    public function logout()
    {
        try {
            if(auth()->user()){
                return $this->buildResponse('success' , 'Logout Successfull!');
            }else{
                return $this->buildErrorResponse('error' , 'User Not Found', 404);
            }

        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
    }
    
    public function refresh(Request $request)
    {
        try {
            $refresh_token = $request->refresh_token;
            if($refresh_token){
                $token = $this->repo->getRefreshToken($refresh_token);
                return $this->buildRefreshTokenResponse(json_decode($token->getContent()), 'success');
                
            } else {
                return $this->buildErrorResponse('error', 'Refresh Token Not Found', 404);
            }

        } catch (\Exception $exception) {
            return $this->buildErrorResponse('error' , $exception->getMessage(), $exception->getCode());
        }
    }

 
}
