<?php

Route::get('/v1/units', 'UnitController@list');
Route::get('/v1/cost-types', 'CostTypeController@list');
Route::get('/v1/engineering-types', 'EngineeringTypeController@list');
