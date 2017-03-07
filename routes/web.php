<?php

\Route::get('/', 'WelcomeController@getIndex');
\Route::get('/login', 'WelcomeController@getLogin');
\Route::post('/login', 'WelcomeController@postLogin');
\Route::post('/register', 'WelcomeController@postRegister');
\Route::get('/logout', 'WelcomeController@getLogout');
