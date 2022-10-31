<?php
    use Laravel\Socialite\Facades\Socialite;
    use App\Controllers\Auth\LoginController;

    Route::redirect('/', '/home');

    Route::get('/auth/redirect', function () {
        return Socialite::driver('ivao')->redirect();
    })->name('login');
    Route::get('/auth/callback', 'Auth\LoginController@callback');

    Route::group(['middleware' => ['auth']], function () {
        Route::get('/home', function () {
            return view('welcome');
        })->name('home');
        Route::get('/logout', 'Auth\LoginController@logout');
    });