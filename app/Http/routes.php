<?php

Route::get('/', function () {
	return redirect('projects');
});

Route::resource('projects', 'MainController');
Route::resource('configurations', 'ConfigurationsController');
