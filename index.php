<?php
require_once('conf.php');

Route::init();
#Route::getInstance()->debug = true;

if(empty($SIN_SESSION)) {
  ini_set('session.gc_maxlifetime', 7200);
  session_start();
}
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
), function($r) {
  if(file_exists(CONTROLLERS . $r['controlador'] . '.php')) {
    Route::controller($r['controlador']);
  } else {
    _404();
  }
});
Route::else('_404');
