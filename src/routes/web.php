<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Auth::routes();

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
if (env("ALLOW_USER_REGISTRATION", false)) {
    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register');
}

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get("password/change", "ChangePasswordController@get")->name('password.change');
Route::post("password/change", "ChangePasswordController@post");

Route::get("/config", "ConfigurationController@index");

Route::post('rule', 'RuleController@create');
Route::get('rule', 'RuleController@retrieveAll');
Route::get('rule/{rule}', 'RuleController@retrieve');
Route::put('rule/{rule}', "RuleController@update");
Route::delete("rule/{rule}", "RuleController@delete");

Route::get("network", "NetworkController@get");
Route::put("network/update", "NetworkController@update");

// BlindFTP Server/Client and Python Pip Routes...
if (!env('DIODE_IN', false)) {
    // DIODE OUT
    Route::get('/storage', 'StorageController@listView');
    Route::get('/storage/{path}', 'StorageController@listView')->where('path', '(.*)'); // The "where" method permits the usage of a slash in the path variable
    Route::post('/download', 'StorageController@download');
} else {
    // DIODE IN
    Route::get('/upload', 'StorageController@uploadIndex');
    Route::post('/upload', 'StorageController@upload');
    Route::get('/pythonpip', 'PythonPipController@index');
    Route::post('/pythonpip', 'PythonPipController@runPip');
    Route::post('/channelAdd', 'UploadersController@add');
    Route::post('/channelDel', 'UploadersController@del');
    Route::post('/addPip', 'UploadersController@addPip');
    Route::post('/getPipPort', 'UploadersController@getPipPort');
    Route::post('/removePip', 'UploadersController@removePip');
}

Route::get('/channels', 'UploadersController@index');
Route::post('/channelUpdate', 'UploadersController@update');
Route::post('/channelStop', 'UploadersController@stop');
Route::post('/channelRestart', 'UploadersController@restart');
Route::post('/channelEmpty', 'UploadersController@empty');

// Main Route...
Route::get('/main', 'MainPageController@index');
Route::get('/', 'MainBisController@index');