<?php

Identify::direccionar_no_logueado();

Route::library('dashboard');
Route::library('chartjs');

$db = Doris::init('hospital');

$formularios = $db->get("
  SELECT COUNT(*) as cantidad
  FROM paciente_formulario PF
  WHERE PF.created_by  = " . Identify::g()->id, true);

$pacientes = $db->get("
  SELECT
    COUNT(P.id) as cantidad,
    COUNT(PF.id) as atendidos
  FROM paciente P
  LEFT JOIN paciente_formulario PF ON PF.paciente_id = P.id AND PF.created_by = " . Identify::g()->id, true);

$preguntas = $db->get("
  SELECT COUNT(PR.id) as cantidad
  FROM paciente_formulario PF
  JOIN paciente_respuesta PR ON PR.paciente_formulario_id = PF.id
  WHERE PF.created_by  = " . Identify::g()->id, true);

Route::theme('dashboard', array(
  'formularios' => $formularios,
  'pacientes'   => $pacientes,
  'preguntas'   => $preguntas,
));
