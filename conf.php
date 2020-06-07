<?php
define('RAIZ', dirname(__FILE__) . '/');
define('LIBRARYS', RAIZ . 'app/librarys/');
define('VIEWS', RAIZ . 'app/views/');
define('CONTROLLERS', RAIZ . 'app/controllers/');
define('DEVEL_MODE', false);
define('RAIZ_WEB', '/');
define('FILE_ERROR', VIEWS . '404.php');

require_once(LIBRARYS . 'misc.php');
require_once(LIBRARYS . 'route.php');
require_once(LIBRARYS . 'doris.pdo.php');
require_once(LIBRARYS . 'pagination.php');
require_once(LIBRARYS . 'identify.php');
require_once(LIBRARYS . 'tablefy.php');

Doris::registerDSN('hospital', 'mysql://root@localhost:3306/cayetano');

define('DOMINIO_ACTUAL', '');
define('SUBDOMINIO_ACTUAL', '');

define('ES_AJAX', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('ES_POPY', ES_AJAX && !empty($_SERVER['HTTP_X_POPY']) && strtolower($_SERVER['HTTP_X_POPY']) == '9435');

$DIAS  = array('domingo','lunes','martes','miercoles','jueves','viernes','sabado','domingo');
$MESES = array('enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre');

$FECHA_COMPLETA = $DIAS[date('N')] . ', ' . date('d') . ' de ' . $MESES[date('m') - 1] . ' de ' . date('Y');
$HORA_COMPLETA  = date("h:i:s A");
