<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UserController extends BaseController
{
  public function __construct()
  {
    $this->middleware('auth',[
      'except'=>['register','login']
    ]);
  }

  protected function buildFailedValidationResponse(Request $request, array $errors) {
    $err = [];
    foreach ($errors as $key => $e) {
      array_push($err,$e[0]);
    }
    return response()->json([
      "status" => "error",
      "code" => 406,
      "message" => $err,
    ],406);
  }

  public function index()
  {
    return response()->json([
      'status' => 'success',
      'code' => 200,
      'data' => auth()->user()->with('meets')->first()
    ],200);
  }

  public function register(Request $r)
  {
    $r->merge([
      'name' => $r->header('name'),
      'email' => $r->header('email'),
      'telp' => $r->header('telp'),
      'password' => $r->header('password'),
      'password_confirmation' => $r->header('repassword'),
    ]);

    $this->validate($r,[
      'name' => 'required',
      'email' => 'required|email|unique:users',
      'telp' => 'required|numeric|unique:users',
      'password' => 'required|confirmed|min:8',
    ],[
      'name.required' => 'Nama lengkap harus diisi',
      'email.required' => 'Alamat email harus diisi',
      'email.email' => 'Format email tidak benar',
      'email.unique' => 'Alamat email telah digunakan',
      'telp.required' => 'Nomor telepon harus diisi',
      'telp.numeric' => 'Format nomor telepon tidak benar',
      'telp.unique' => 'Nomor telepon telah digunakan',
      'password.required' => 'Password harus diisi',
      'password.confirmed' => 'Perulangan password tidak sesuai',
      'password.min' => 'Password minimal 8 karakter',
    ]);

    $user = User::create([
      'uuid' => Str::uuid(),
      'name' => $r->header('name'),
      'email' => $r->header('email'),
      'telp' => $r->header('telp'),
      'password' => app('hash')->make($r->header('password')),
      'api_token' => Str::random(100),
      'opt' => $r->header('opt'),
    ]);

    return response()->json([
      'status' => 'success',
      'code' => 201,
      'data' => $user
    ],201);

  }

  public function login(Request $r)
  {
    $user = User::where('email',$r->header('username'))
    ->orWhere('telp',$r->header('username'))->first();

    if ($user && app('hash')->check($r->header('password'),$user->password)) {
      $user->update([
        'api_token' => Str::random(100)
      ]);
      return response()->json([
        'status' => 'success',
        'code' => 202,
        'data' => $user
      ],202);
    }else {
      return response()->json([
        'status' => 'error',
        'code' => 406,
        'data' => ''
      ],406);
    }
  }

  public function logout(Request $r)
  {
    auth()->user()->update([
      'api_token' => null
    ]);
    return response()->json([
      'status' => 'success',
      'code' => 202,
      'data' => ''
    ],202);
  }
}
