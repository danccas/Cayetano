<?php

Identify::direccionar_no_logueado();

Route::library('formity2');
Route::library('chartjs');

$db = Doris::init('hospital');

$form = Formity::getInstance('usuario');
$form->setTitle('Usuario');
$form->addField('documento:Documento Número', 'input:text');
$form->addField('nombres', 'input:text');
$form->addField('apellidos?', 'input:text');
$form->addField('direccion?', 'input:text');
$form->addField('telefono', 'input:text');
$form->addField('numero_colegio:Número de Colegio Médico', 'input:text');
$form->addField('anho_graduacion:Año Graduacion', 'input:date');
$form->addField('centro_laboral:Centro Laboral', 'input:text');
$form->addField('cargo', 'input:text');

$form->addField('horario_disponible', 'textarea');
$form->addField('usuario', 'input:text');
$form->addField('clave', 'input:password');

Route::any('', function() use($db) {
  $table = Tablefy::getInstance('usuarios');
  $table->setTitle('Relación de usuarios');
  $table->setHeader(array('USUARIO','NOMBRES','APELLIDOS'));
  $table->setData(function($e) use($db) {
    return $db->pagination("
      SELECT
        U.*
      FROM usuario U
      ORDER BY id ASC");
  }, function($n) {
    return array(
      $n['usuario'],
      $n['nombres'],
      $n['apellidos'],
    );
  });
  $table->setOption('editar', function($n) use($db) {
    $form = Formity::getInstance('usuario');
    $form->removeField('usuario');
    if($form->byRequest()) {
      if($form->isValid()) {
        $data = $form->getData();
        $data['clave'] = md5($data['clave']);
        $db->update('usuario', $data, 'id = ' . $n['id']);
        Route::go2Back();
      }
    } else {
      $n['clave'] = '';
      $form->setPreData($n);
    }
    Route::render($form);
  });
  $table->prepare();
  Route::setTitle('Usuarios');
  Route::setDescription('Se muestra la relación de usuarios registrados.');
  Route::nav('Nuevo', function() use($db) {
    $form = Formity::getInstance('usuario');
    if($form->byRequest()) {
      if($form->isValid()) {
        $data = $form->getData();
        $existe = $db->get("SELECT * FROM usuario WHERE usuario = :user LIMIT 1", true, false, array(
          'user' => $data['usuario'],
        ));
        if(!empty($existe)) {
          $form->setError('El usuario ya existe');
        } else {
          $data['clave'] = md5($data['clave']);
          $form_id = $db->insert('usuario', $data);
          Route::go2Back();
        }
      }
    }
    Route::render($form);
  });
  Route::render($table);
});
