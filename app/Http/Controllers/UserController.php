<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Exception;
use Validator;
use Api;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $data, $code;

    public function __construct()
    {
        $this->code = 200;
        $this->data = [];
    }

   public function register(Request $request)
   {
        try{
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'unique:users', 'max:255'],
                'password' => [
                    'required', 'string', 'min:8', 'confirmed', 'min:9',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&.s]/']
            ]);

            if ($validator->fails()) {
                return Api::apiRespond(400, $validator->errors()->all());
            }

            $this->data = User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'password' => Bcrypt($request->password),
            ]);
        } catch (Exception $e) {
            $this->code = 500;
            $this->data = $e;
        }

       return Api::apiRespond($this->code, $this->data);
   }

    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required']
            ]);

            if ($validator->fails()) {
                return Api::apiRespond(400, $validator->errors()->all());
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $data['user'] = $user;
                $data['token'] = $user->createToken('auth-api')->accessToken;
                $this->data = $data;
            }
        } catch (Exception $e) {
            $this->code = 500;
            $this->data = $e;
        }

        return Api::apiRespond($this->code, $this->data);
    }
}
