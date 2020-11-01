<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Meet;
use App\Models\JoinMeet;
use Storage;

use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;

class MeetController extends BaseController
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function generateToken($range=8)
  {
    $_token = strtoupper(Str::random($range));
    $cek = Meet::where('_token',$_token)
    ->where('active',1)
    ->first();
    if ($cek) {
      return $this->generateToken();
    }
    return $_token;
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
    $user = auth()->user();
    $meets1 = $user->meets()->orderBy('start','desc')->get();
    $meets2 = Meet::whereHas('join_meet',function($q) use($user){
      $q->where('user_id',$user->id)
      ->orderBy('created_at','desc');
    })
    ->orderBy('start','desc')
    ->get();
    $merge = $meets2->merge($meets1);
    $meets = $merge->all();
    if (count($meets)) {
      return response()->json([
        'status' => 'success',
        'code' => 200,
        'data' => $meets
      ],200);
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }

  public function detail(Request $r)
  {
    $user = auth()->user();
    $detail = $user->join_meet()->whereHas('meet',function($q) use($r){
      $q->where('uuid',$r->header('uuid'));
    })->first();
    if ($detail) {
      return response()->json([
        'status' => 'success',
        'code' => 200,
        'data' => $detail
      ],200);
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }

  public function create(Request $r)
  {
    $r->merge([
      'name' => $r->header('name'),
      'desc' => $r->header('desc'),
      'start' => $r->header('start'),
      'end' => $r->header('end'),
      'active' => $r->header('active'),
    ]);

    $this->validate($r,[
      'name' => 'required',
      'start' => 'required|date_format:Y-m-d H:i:s',
      'end' => 'required|date_format:Y-m-d H:i:s',
    ],[
      'name.required' => 'Nama pertemuan harus diisi',
      'start.required' => 'Waktu mulai pertemuan harus diisi',
      'start.date_format' => 'Waktu mulai pertemuan harus berupa tanggal dan jam',
      'end.required' => 'Waktu selesai pertemuan harus diisi',
      'end.date_format' => 'Waktu selesai pertemuan harus berupa tanggal dan jam',
    ]);

    $meet = auth()->user()->meets()->create([
      'uuid' => Str::uuid(),
      'name' => $r->header('name'),
      'desc' => $r->header('desc'),
      'start' => $r->header('start'),
      'end' => $r->header('end'),
      '_token' => $this->generateToken(),
    ]);

    return response()->json([
      'status' => 'success',
      'code' => 201,
      'data' => $meet
    ],201);
  }

  public function update(Request $r)
  {
    $r->merge([
      'uuid' => $r->header('uuid'),
      'name' => $r->header('name'),
      'desc' => $r->header('desc'),
      'start' => $r->header('start'),
      'end' => $r->header('end'),
      'active' => $r->header('active'),
    ]);

    $meet = auth()->user()->meets()->where('uuid',$r->uuid)->first();
    if (!$meet) {
      return response()->json([
        'status' => 'error',
        'code' => 406,
        'data' => ''
      ],406);
    }

    $this->validate($r,[
      'name' => 'required',
      'start' => 'required|date_format:Y-m-d H:i:s',
      'end' => 'required|date_format:Y-m-d H:i:s',
    ],[
      'name.required' => 'Nama pertemuan harus diisi',
      'start.required' => 'Waktu mulai pertemuan harus diisi',
      'start.date_format' => 'Waktu mulai pertemuan harus berupa tanggal dan jam',
      'end.required' => 'Waktu selesai pertemuan harus diisi',
      'end.date_format' => 'Waktu selesai pertemuan harus berupa tanggal dan jam',
    ]);

    $meet->name = $r->header('name');
    $meet->desc = $r->header('desc');
    $meet->start = $r->header('start');
    $meet->end = $r->header('end');
    $meet->save();

    return response()->json([
      'status' => 'success',
      'code' => 202,
      'data' => $meet
    ],202);
  }

  public function destroy(Request $r)
  {
    $meet = auth()->user()->meets()->where('uuid',$r->header('uuid'))->first();
    if ($meet) {
      $join = $meet->join_meet;

      if (count($join)) {
        foreach ($join as $key => $j) {
          Storage::disk('public')->delete('ttd/signin_'.$j->uuid.'.png');
          Storage::disk('public')->delete('ttd/signout_'.$j->uuid.'.png');
          $j->delete();
        }
      }

      $meet->delete();
      Storage::disk('public')->delete($meet->uuid.'.pdf');
      return response()->json([
        'status' => 'success',
        'code' => 202,
        'data' => $meet
      ],202);
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }

  public function active(Request $r)
  {
    $meet = auth()->user()->meets()->where('uuid',$r->header('uuid'))->first();
    if ($meet) {
      $meet->active = $r->header('active');
      $meet->save();
      return response()->json([
        'status' => 'success',
        'code' => 202,
        'data' => $meet
      ],202);
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }

  public function refreshToken(Request $r)
  {
    $meet = auth()->user()->meets()->where('uuid',$r->header('uuid'))->first();
    if ($meet) {
      $meet->_token = $this->generateToken();
      $meet->save();
      return response()->json([
        'status' => 'success',
        'code' => 202,
        'data' => $meet
      ],202);
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }

  public function joinMeet(Request $r)
  {
    $now = date('Y-m-d H:i:s');
    $meet = Meet::where('_token',$r->header('Meet-Token'))
    ->where('active',1)
    ->where('start','<=',$now)
    ->where('end','>=',$now)
    ->first();
    $user = auth()->user();
    if ($meet) {
      $join = new JoinMeet;
      $join->uuid = Str::uuid();
      $join->user_id = $user->id;
      $join->meet_id = $meet->id;
      $join->_token = $meet->_token;
      $join->user_data = [
        'name' => $user->name,
        'email' => $user->email,
        'telp' => $user->telp,
      ];

      if ($join->save()) {
        return response()->json([
          'status' => 'success',
          'code' => 201,
          'data' => $join
        ],201);
      }
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }

  public function signIn(Request $r)
  {
    if (!$r->ttd) {
      return response()->json([
        'status' => 'error',
        'code' => 406,
      ],406);
    }

    $now = date('Y-m-d H:i:s');
    $user = auth()->user();
    $join = $user->join_meet()
    ->whereHas('meet',function($q) use($r,$now){
      $q->where('uuid',$r->header('uuid'))
      ->where('active',1)
      ->where('start','<=',$now)
      ->where('end','>=',$now);
    })
    ->whereNull('end')
    ->first();
    if (!$join) {
      $meet = Meet::where('uuid',$r->header('uuid'))
      ->where('active',1)
      ->where('start','<=',$now)
      ->where('end','>=',$now)
      ->first();

      if (!$meet) {
        return response()->json([
          'status' => 'error',
          'code' => 406,
        ],406);
      }

      $join = new JoinMeet;
      $join->uuid = Str::uuid();
      $join->user_id = $user->id;
      $join->meet_id = $meet->id;
      $join->_token = $meet->_token;
      $join->user_data = [
        'name' => $user->name,
        'email' => $user->email,
        'telp' => $user->telp,
      ];
    }
    $join->start = $now;
    Storage::disk('public')->put('ttd/signin_'.$join->uuid.'.png',base64_decode($r->ttd));
    if ($join->save()) {
      return response()->json([
        'status' => 'success',
        'code' => 202,
        'data' => $join
      ],202);
    }
  }

  public function signOut(Request $r)
  {
    if (!$r->ttd) {
      return response()->json([
        'status' => 'error',
        'code' => 406,
      ],406);
    }

    $now = date('Y-m-d H:i:s');
    $user = auth()->user();
    $join = $user->join_meet()
    ->whereHas('meet',function($q) use($r,$now){
      $q->where('uuid',$r->header('uuid'))
      ->where('active',1)
      ->where('start','<=',$now)
      ->where('end','>=',$now);
    })
    ->whereNull('end')
    ->first();
    if ($join) {
      $join->end = $now;

      Storage::disk('public')->put('ttd/signout_'.$join->uuid.'.png',base64_decode($r->ttd));
      if ($join->save()) {
        return response()->json([
          'status' => 'success',
          'code' => 202,
          'data' => $join
        ],202);
      }
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }

  public function print(Request $r)
  {
    $uuid = $r->header('uuid');
    $meet = Meet::where('uuid',$uuid)->with('join_meet')->first();

    if ($meet) {
      $data = [
        'title' => $meet->name,
        'data' => $meet,
      ];

      $params = [
        'format'=>[215,330]
      ];

      $headers = [
        'Content-Type' => 'application/pdf',
      ];

      $filename = $meet->uuid.'.pdf';

      $pdf = PDF::loadView('print',$data,[],$params);
      $pdf->save(base_path('storage/app/public/'.$filename));
      return response()->download(base_path('storage/app/public/'.$filename));
    }
    return response()->json([
      'status' => 'error',
      'code' => 404,
      'data' => ''
    ],404);
  }
}
