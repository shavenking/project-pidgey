<?php

Route::get('/v1/units', 'UnitController@list');
Route::get('/v1/cost-types', 'CostTypeController@list');
Route::get('/v1/engineering-types', 'EngineeringTypeController@list');

// Work
Route::group(['prefix' => '/v1/works'], function () {
    Route::get('/', 'WorkController@list');
});
