<?php
Route::library('formity2');

Identify::direccionar_no_logueado();

$db = Doris::init('hospital');

$form = Formity::getInstance('perfil');
$form->setTitle('Complete su información personal.');
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
$form->addField('clave?', 'input:password')->setMin(6)->setMax(15);

if($form->byRequest()) {
  if($form->isValid($error)) {
    $data = $form->getData();
    if(empty($data['clave'])) {
      unset($data['clave']);
    } else {
      $data['clave'] = md5($data['clave']);
    }
    $db->update('usuario', $data, 'id = ' . Identify::g()->id);
  }
} else {
  $usuario = $db->get("SELECT * FROM usuario WHERE id = " . Identify::g()->id, true);
  unset($usuario['clave']);
  $form->setPreData($usuario);
}
Route::data('clear', true);
Route::setTitle('Mis datos');
Route::setDescription('Formulario de información');
Route::render($form);
