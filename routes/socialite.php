<?php

Route::get('login/dhid', 'DreamHack\SDK\Http\Controllers\SocialiteController@redirectToProvider');
Route::get('login/dhid/callback', 'DreamHack\SDK\Http\Controllers\SocialiteController@handleProviderCallback');

