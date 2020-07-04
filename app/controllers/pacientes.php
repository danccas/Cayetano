<?php

Identify::direccionar_no_logueado();

Route::library('formity2');
Route::library('excelity');


$db = Doris::init('hospital');

function renderizar_formulario($db, $pf_id) {
  $pf = $db->get("
    SELECT PF.*
    FROM paciente_formulario PF
    WHERE PF.id = {$pf_id}", true);
  if(empty($pf)) {
    return;
  }
  $preguntas = $db->get("
    SELECT
      P.*,
      PR.respuesta
    FROM formulario_pregunta FP
    JOIN pregunta P ON P.id = FP.pregunta_id
    LEFT JOIN paciente_respuesta PR ON PR.paciente_formulario_id = {$pf_id} AND PR.pregunta_id = P.id
    WHERE FP.formulario_id = {$pf['formulario_id']}
    ORDER BY FP.orden ASC");
  ob_start();
  require_once(Route::g()->attr('views') . 'cuestionario.php');
  return ob_get_clean();
}

$re = Formity::getInstance('carga_masiva');
$re->addField('archivo:Seleccione', 'input:file');

$form = Formity::getInstance('paciente');
$form->addField('documento_tipo', 'input:text');
$form->addField('documento_numero', 'input:text');
$form->addField('nombres', 'input:text');
$form->addField('apellidos?', 'input:text');
$form->addField('direccion?', 'input:text');
$form->addField('telefono1', 'input:text');
$form->addField('fecha_atencion', 'input:date');
$form->addField('edad', 'input:number');
$form->addField('sexo', 'input:text');
$form->addField('pais_infeccion', 'input:text');
$form->addField('provincia_actual', 'input:text');
$form->addField('distrito_actual', 'input:text');
$form->addField('direccion_actual', 'input:text');
$form->addField('inicio_sintomas', 'input:date');
$form->addField('clasificacion', 'input:text');
$form->addField('destino', 'input:text');
$form->addField('destino_lugar', 'input:text');
$form->addField('notas', 'textarea');


Route::any('', function() use($db) {
  $table = Tablefy::getInstance('listado');
  $table->setTitle('Relación de Pacientes');
  $table->setHeader(array('DOCUMENTO','NOMBRES','APELLIDOS','TELEFONO','ÚLTIMO CONTACTO'));
  $table->setData(function($e) use($db) {
    return $db->get("
      SELECT
        P.*,
        (SELECT MAX(created_on) FROM paciente_formulario WHERE paciente_id = P.id) as ultimo
      FROM paciente P");
  }, function($n) {
    return array(
      $n['documento_tipo'] . ' ' . $n['documento_numero'],
      $n['nombres'],
      $n['apellidos'],
      $n['telefono1'],
      $n['ultimo'],
    );
  });
  $table->setOption('LLAMAR', '/pacientes/llamar-:id');
  $table->setOption('editar', function($n) use($db) {
    $form = Formity::getInstance('paciente');
    if($form->byRequest()) {
      if($form->isValid()) {
        $data = $form->getData();
        $db->update('paciente', $data, 'id = ' . $n['id']);
        Route::go2Back();
      }
    } else {
      $form->setPreData($n);
    }
    Route::render($form);
  });
  $table->prepare();
  Route::setTitle('Pacientes');
  Route::setDescription('Se muestra la relación de pacientes registrados.');
  Route::nav('Nuevo Paciente', function() use($db) {
    $form = Formity::getInstance('paciente');
    if($form->byRequest()) {
      if($form->isValid()) {
        $data = $form->getData();
        $db->insert('paciente', $data);
        Route::go2Back();
      }
    }
    Route::render($form);
  });
  Route::nav('Carga Masiva', function() use($db) {
    $re = Formity::getInstance('carga_masiva');
    if($re->byRequest()) {
      if($re->isValid($err)) {
        $data = $re->getData();
        $excel = new Excelity($data['archivo']['tmp_name']);
        $datos = $excel->parseLoad(1);
        if(empty($datos)) {
          $re->setError("No se ha encontrado datos (1)");
        } else {
          unset($datos[0]);
          foreach($datos as $d) {
            if(empty($d[3]) || strpos($d[3], 'DNI') !== false) {
              continue;
            }
            $existe = $db->get("SELECT * FROM paciente WHERE documento_numero = :documento", false, array(
              'documento' => $d[3],
            ));
            if(empty($existe)) {
              $notas = '';
              if(!empty($d[13])) {
                $notas .= 'MUESTRA 1 (' . date('d/m/Y', PHPExcel_Shared_Date::ExcelToPHP($d[13])). ")\n";
                $notas .= $d[14] . ' => ' . $d[15] . "\n";
              }
              if(!empty($d[16])) {
                $notas .= 'MUESTRA 2 (' . date('d/m/Y', PHPExcel_Shared_Date::ExcelToPHP($d[16])) . ")\n";
                $notas .= $d[17] . ' => ' . $d[18] . "\n";
              }
              if(!empty($d[19])) {
                $notas .= 'MUESTRA 3 (' . date('d/m/Y', PHPExcel_Shared_Date::ExcelToPHP($d[19])) . ")\n";
                $notas .= $d[20] . ' => ' . $d[21] . "\n";
              }
              if(!empty($d[25])) {
                $notas .= 'EVOLUCION 1: ' . $d[25];
              }
              if(!empty($d[26])) {
                $notas .= 'EVOLUCION 2: ' . $d[26];
              }
              try {
              $db->insert('paciente', array(
                'documento_tipo' => 'DNI',
                'documento_numero' => $d[3],
                'nombres' => $d[4],
                'apellidos' => null,
                'telefono1' => $d[7],
                'fecha_atencion' => Doris::time(PHPExcel_Shared_Date::ExcelToPHP($d[2])),
                'edad' => $d[5],
                'sexo' => $d[6],
                'pais_infeccion' => $d[8],
                'provincia_actual' => $d[9],
                'distrito_actual' => $d[10],
                'direccion_actual' => $d[11],
                'inicio_sintomas' => Doris::time(PHPExcel_Shared_Date::ExcelToPHP($d[12])),
                'clasificacion' => $d[24],
                'destino' => $d[22],
                'destino_lugar' => $d[23],
                'notas' => $notas,
              ));
              } catch(Exception $err) {
              }
            }
          }
        }
        Route::go2Back();
      }
    }
    Route::render($re);
  });
  Route::render($table);
});
Route::path('llamar-:id', function($route) use($db) {
  $paciente = $db->get("
    SELECT
      P.*,
      (SELECT MAX(created_on) FROM paciente_formulario WHERE paciente_id = P.id) as ultima
    FROM paciente P
    WHERE id = " . $route->current_route['id'], true);
  if(empty($paciente)) {
    _404();
  }
  Route::any('', function($route) use($db, $paciente) {
    $formularios = $db->get("
      SELECT
        F.tipo,
        F.nombre,
        PF.created_on
      FROM paciente_formulario PF
      JOIN formulario F ON F.id = PF.formulario_id
      WHERE PF.paciente_id = {$paciente['id']}");
    $formularios = array_group_by($formularios, array(
      array('key' => 'tipo', 'only' => array('tipo','nombre')),
    ));
    $next = $db->get("
      SELECT P.*
      FROM paciente P
      LEFT JOIN paciente_formulario PF ON PF.paciente_id = P.id
      WHERE P.id <> {$paciente['id']}
      ORDER BY PF.created_on ASC, P.id ASC
      LIMIT 1", true);
    Route::theme('pacientes.llamada', array(
      'paciente'    => $paciente,
      'formularios' => $formularios,
      'next'        => $next,
    ));
  });
  Route::any('cuestionario', function() use($db, $paciente) {
    $fecha = $_POST['fecha'];
    $tipo  = $_POST['tipo'];
    $query = "SELECT PF.*
      FROM paciente_formulario PF
      JOIN formulario F ON F.id = PF.formulario_id
      WHERE PF.paciente_id = {$paciente['id']} AND F.tipo = '{$tipo}'";
    if(in_array($tipo, array('DIARIO','ADICIONAL'))) {
      $query .= " AND DATE(PF.created_on) = '{$fecha}'";
    }
    $cuesti = $db->get($query, true);
    if(empty($cuesti) && !empty($_POST['click'])) {
      $form = $db->get("SELECT * FROM formulario WHERE tipo = '{$tipo}' LIMIT 1", true);
      if(!empty($form)) {
        $cuesti = array();
        $cuesti['id'] = $db->insert('paciente_formulario', array(
          'paciente_id'   => $paciente['id'],
          'formulario_id' => $form['id'],
          'created_by'    => Identify::g()->id,
        ));
        echo renderizar_formulario($db, $cuesti['id']);
        exit;
      }
    }
    return_json(array(
      'estado' => !empty($cuesti),
      'id'     => !empty($cuesti) ? $cuesti['id'] : null,
      'html'   => !empty($cuesti) ? renderizar_formulario($db, $cuesti['id']) : null,
    ));
  });
  Route::any('guardar', function() use($db, $paciente) {
    $pf_id = $_POST['pf_id'];
    $pregunta_id = $_POST['pregunta_id'];
    $respuesta   = trim($_POST['text']);
    $respuesta   = str_replace('"', '', $respuesta);
    $db->insert('paciente_respuesta', array(
      '*paciente_formulario_id' => $pf_id,
      '*pregunta_id' => $pregunta_id,
      'respuesta'    => $respuesta,
      'created_by'   => Identify::g()->id,
    ));
  });
  Route::any('interpretar', function() use($db, $paciente) {
    $pf_id = $_POST['pf_id'];
    $pregunta_id = $_POST['pregunta_id'];
    $respuesta   = trim($_POST['text']);
    
    $cmd = "/usr/bin/python3.6 /var/www/html/cayetano.anccas.org/util/sentimiento2_run.py \"" . $respuesta . "\"";
    $out = shell_exec($cmd);
    $out = trim($out);
    $bool = $out === 'positivo';

    $db->insert('paciente_respuesta', array(
      '*paciente_formulario_id' => $pf_id,
      '*pregunta_id' => $pregunta_id,
      'respuesta'    => $bool ? 'SI' : 'NO',
      'created_by'   => Identify::g()->id,
    ));
    Route::responseJSON(200, array(
      'pln'  => $out,
      'bool' => $bool,
    ));
  });
  Route::any('resultadoia', function() use($db, $paciente) {
   $pf_id = $_POST['pf_id'];
   $pf = $db->get("
    SELECT PF.*
    FROM paciente_formulario PF
    WHERE PF.id = {$pf_id}", true);
  if(empty($pf)) {
    return;
  }
    $preguntas = $db->get("
    SELECT
      P.id,
      PR.respuesta
    FROM formulario_pregunta FP
    JOIN pregunta P ON P.id = FP.pregunta_id
    LEFT JOIN paciente_respuesta PR ON PR.paciente_formulario_id = {$pf_id} AND PR.pregunta_id = P.id
    WHERE FP.formulario_id = {$pf['formulario_id']}
    ORDER BY FP.orden ASC");
   $preguntas = array_map(function($n) { return $n['respuesta'] == 'SI' ? 1 : 0; }, $preguntas);
   if(count($preguntas) === 7) {
	   $cadena = implode(',', $preguntas);
	   $cmd = "/usr/bin/python3.6 /var/www/html/cayetano.anccas.org/util/covid2_run.py \"" . $cadena . "\"";
	   $out = shell_exec($cmd);
	   $part = explode('[[', $out);
	   $part = explode(']]', $out[1]);
	   $part = trim($part[0]);
	   $part = explode(' ', $part);
	   $part = round($part[0]);
	   if($part == 0) {
		   echo "<h1 style='color:green'>Baja probabilidad</h1>";
	   } else {
		   echo "<h1 style='color:red'>Alta Probabilidad</h1>";
	   }
	   #print_r($out);

     } else {
	echo "<h3># Es necesario completar la encuesta #</h3>";
     }
  });
  Route::any('campo', function() use($db, $paciente) {
    $campo  = $_POST['campo'];
    $valor  = trim($_POST['valor']);
    $valor = str_replace("\n", '', $valor);
    $valor = preg_replace('#<br\s*/?>#i', "\n", $valor);
    $valor = strip_tags($valor);

    $db->update('paciente', array(
      $campo => $valor,
    ), 'id = ' . $paciente['id']);
  });
});
