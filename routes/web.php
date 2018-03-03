<?php

/////////////////////////
// Link Tracking
/////////////////////////
$trackLinks = function() {
    \Route::get('/', function(){ \abort(404); });
    \Route::get('/{link}', 'TrackingController@getIndex');
};
foreach (\config('app.enabled_link_domains') as $domain) {
    \Route::group(['domain' => $domain], $trackLinks);
}

/////////////////////////
// Frontend
/////////////////////////
\Route::get('/', 'WelcomeController@getIndex');
\Route::get('/login', 'WelcomeController@getLogin');
\Route::post('/login', 'WelcomeController@postLogin');
\Route::post('/register', 'WelcomeController@postRegister');
\Route::get('/forgot', 'WelcomeController@getForgot');
\Route::post('/forgot', 'WelcomeController@postForgot');

\Route::group(['middleware' => 'auth'], function() {
    \Route::get('/logout', 'WelcomeController@getLogout');
    \Route::get('/dash', 'WelcomeController@getDash');

    /////////////////////////
    // Campaigns
    /////////////////////////
    \Route::get('/i/{invite_code}', 'CampaignController@getInvite');
    \Route::group(['prefix' => '/c/{campaign}', 'middleware' => 'auth.joined'], function() {
        \Route::get('/', 'CampaignController@getIndex');
        \Route::get('/updates', 'CampaignController@getIndex');
        \Route::post('/link', 'CampaignController@postLink');

        \Route::get('/state.json', 'CampaignController@getState');
        \Route::get('/timeline.csv', 'CampaignController@getClicksTimeline');

        \Route::post('/share/custom', 'CampaignController@postShareCustom');
        \Route::get('/share/{site}', 'CampaignController@getShare');

        /////////////////////////
        // Campaign Admin
        /////////////////////////

        \Route::group(['middleware' => 'auth.admin'], function() {
            \Route::post('/announce', 'CampaignController@postAnnounce');
            \Route::get('/edit', 'CampaignController@getEdit');
            \Route::post('/edit', 'CampaignController@postEdit');
            \Route::post('/op/{username}', 'CampaignController@postOpDeop');
            \Route::post('/update', 'CampaignController@postUpdate');
        });
    });
});

/////////////////////////
// Superadmin
/////////////////////////
\Route::group(['prefix' => 'admin', 'middleware' => 'auth.superadmin'], function() {
    \Route::get('/', 'AdminController@getIndex');
    \Route::get('/new', 'AdminController@getNew');
    \Route::post('/new', 'AdminController@postNew');
});
