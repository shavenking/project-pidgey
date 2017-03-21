<?php

// Authentication
Route::post('/v1/users', 'AuthenticationController@createUser');
Route::post('/v1/tokens', 'AuthenticationController@createToken');

Route::get('/v1/units', 'UnitController@list');
Route::get('/v1/cost-types', 'CostTypeController@list');
Route::get('/v1/engineering-types', 'EngineeringTypeController@list');

// Work
Route::group(['prefix' => '/v1/works'], function () {
    Route::get('/', 'WorkController@list');
    Route::post('/', 'WorkController@create');
    Route::delete('/{work}', 'WorkController@delete');
});

// WorkItem
Route::group(['prefix' => '/v1/work-items'], function () {
    Route::get('/', 'WorkItemController@listWithoutWork');
    Route::post('/', 'WorkItemController@createWithoutWork');
});

Route::group(['prefix' => '/v1/works/{work}/work-items'], function () {
    Route::get('/', 'WorkItemController@list');
    Route::get('/stats', 'WorkItemController@stats');
    Route::post('/', 'WorkItemController@create');
    Route::delete('/{workItem}', 'WorkItemController@delete');
});
