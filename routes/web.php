<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/key-generate/{num}', function ($num=40) use ($router) {
  return \Illuminate\Support\Str::random($num);
});

$router->group(['prefix'=>'api/v1'],function() use($router){

  $router->group(['prefix'=>'user'],function() use($router){
    $router->get('/', ['uses'=>'UserController@index','as'=>'user.index']);
    $router->post('/register', ['uses'=>'UserController@register','as'=>'user.register']);
    $router->post('/login', ['uses'=>'UserController@login','as'=>'user.login']);
    $router->post('/logout', ['uses'=>'UserController@logout','as'=>'user.logout']);
  });

  $router->group(['prefix'=>'meet'],function() use($router){
    $router->get('/lists', ['uses'=>'MeetController@index','as'=>'meet.index']);
    $router->get('/meet-detail', ['uses'=>'MeetController@detail','as'=>'meet.detail']);
    $router->post('/create', ['uses'=>'MeetController@create','as'=>'meet.create']);
    $router->put('/update', ['uses'=>'MeetController@update','as'=>'meet.update']);
    $router->delete('/destroy', ['uses'=>'MeetController@destroy','as'=>'meet.destroy']);
    $router->patch('/active', ['uses'=>'MeetController@active','as'=>'meet.active']);
    $router->patch('/refresh-token', ['uses'=>'MeetController@refreshToken','as'=>'meet.refresh.token']);
    $router->post('/join-meet', ['uses'=>'MeetController@joinMeet','as'=>'meet.join']);
    $router->patch('/signin', ['uses'=>'MeetController@signIn','as'=>'meet.signin']);
    $router->patch('/signout', ['uses'=>'MeetController@signOut','as'=>'meet.signout']);
    $router->get('/print', ['uses'=>'MeetController@print','as'=>'meet.print']);
  });

});
