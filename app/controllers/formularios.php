<?php

Identify::direccionar_no_logueado();

Route::library('formity2');
Route::library('chartjs');

$db = Doris::init('hospital');

$form = Formity::getInstance('formulario');
$form->setTitle('Formulario');
$form->addField('nombre', 'input:text');
$form->addField('tipo', 'input:text');

$form = Formity::getInstance('pregunta');
$form->setTitle('Pregunta');
$form->addField('texto', 'textarea');
$form->addField('tipo', 'input:text');

Route::any('', function() use($db) {
  $table = Tablefy::getInstance('formularios');
  $table->setTitle('Relación de formularios');
  $table->setHeader(array('FORMULARIO','TIPO','PREGUNTAS'));
  $table->setData(function($e) use($db) {
    return $db->pagination("
      SELECT
        F.*,
        (SELECT COUNT(*) FROM formulario_pregunta WHERE formulario_id = F.id) as preguntas
      FROM formulario F
      ORDER BY id ASC");
  }, function($n) {
    return array(
      $n['nombre'],
      $n['tipo'],
      $n['preguntas'],
    );
  });
  $table->setOption('editar', function($n) use($db) {
    $form = Formity::getInstance('formulario');
    if($form->byRequest()) {
      if($form->isValid()) {
        $data = $form->getData();
        $db->update('formulario', $data, 'id = ' . $n['id']);
        Route::go2Back();
      }
    } else {
      $form->setPreData($n);
    }
    Route::render($form);
  });
  $table->setOption('preguntas', function($n) use($db) {
    $table = Tablefy::getInstance('preguntas');
    $table->setTitle('Relación de preguntas de: ' . $n['nombre']);
    $table->setHeader(array('PREGUNTA','TIPO'));
    $table->setData(function($e) use($db, $n) {
      return $db->pagination("
        SELECT
          P.*,
          FP.orden
        FROM formulario_pregunta FP
        JOIN pregunta P ON P.id = FP.pregunta_id
        WHERE FP.formulario_id = {$n['id']}
        ORDER BY orden ASC");
    }, function($n) {
      return array(
        $n['texto'],
        $n['tipo'],
      );
    });
    $table->setOption('eliminar', function($n) use($db) {
      $db->transaction();
      $db->delete('pregunta', 'id = ' . $n['id']);
      $db->delete('formulario_pregunta', 'pregunta_id = ' . $n['id']);
      $db->commit();
      Route::go2Back();
    });
    $table->setOption('editar', function($n) use($db) {
      $form = Formity::getInstance('pregunta');
      if($form->byRequest()) {
        if($form->isValid()) {
          $data = $form->getData();
          $db->update('pregunta', $data, 'id = ' . $n['id']);
          Route::go2Back();
        }
      } else {
        $form->setPreData($n);
      }
      Route::render($form);
    });
    $table->prepare();
    Route::nav('Nueva Pregunta', function() use($db, $n) {
      $form = Formity::getInstance('pregunta');
      if($form->byRequest()) {
        if($form->isValid()) {
          $data = $form->getData();
          $db->transaction();
          $pregunta_id = $db->insert('pregunta', $data);
          $db->insert('formulario_pregunta', array(
            'formulario_id' => $n['id'],
            'pregunta_id'   => $pregunta_id,
          ));
          $db->commit();
          Route::go2Back();
        }
      }
      Route::render($form);
    });
    Route::setTitle('Preguntas');
    Route::render($table);
  });
  $table->prepare();
  Route::setTitle('Formularios');
  Route::setDescription('Se muestra la relación de formularios registrados.');
  Route::nav('Nuevo', function() use($db) {
    $form = Formity::getInstance('formulario');
    if($form->byRequest()) {
      if($form->isValid()) {
        $data = $form->getData();
        $form_id = $db->insert('formulario', $data);
        Route::go2Back();
      }
    }
    Route::render($form);
  });
  Route::render($table);
});
