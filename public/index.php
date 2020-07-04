<?php
require_once(__DIR__ . '/../core/route.php');

Route::import(__DIR__ . '/../conf.php');

Route::init()->debug(true);


Route::any('webhook', function() {
  Route::controller('webhook');
});

Route::g()->libs->session->init();

Route::any('demo', function() {
  debug($_SESSION);
});
Route::any('identificacion', function() {
  Route::controller('identificacion');
});
Route::any('', function() {
  Route::data('accion', 'EMPRESAS');
  Route::controller('dashboard');
});
Route::path(':controlador', array(
  'controlador' => '[\w\_\-]{3,20}',
), function($ce) {
  #if(file_exists(Route::attr('controllers') . $ce->current_route['controlador'] . '.php')) {
    Route::controller($ce->current_route['controlador']);
  #}
});

Route::else(function() {
  Route::response(404);
});
