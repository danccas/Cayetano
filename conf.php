<?php
Route::config(function($app) {
  $app->attr('root', dirname(__FILE__) . '/');
  $app->attr('librarys', $app->attr('root') . 'app/librarys/');
  $app->attr('controllers', $app->attr('root') . 'app/controllers/');
  $app->attr('views', $app->attr('root') . 'app/views/');


  $app->library('Misc');
  $app->library('doris.pdo', 'Doris');
  $app->library('Pagination');
  $app->library('Tablefy');
  $app->library('Session');
  $app->libraryOwn('Identify');

  $app->attr('minds', dirname(__FILE__) . '/minds/');
  Doris::registerDSN('hospital', 'mysql://hospital:cayetano123@localhost:3306/cayetano'); 
});
