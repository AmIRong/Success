<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(1000);
@set_magic_quotes_runtime(0);

define('IN_DISCUZ', TRUE);
define('IN_COMSENZ', TRUE);
define('ROOT_PATH', dirname(__FILE__).'/../');

require ROOT_PATH.'./source/discuz_version.php';
require ROOT_PATH.'./install/include/install_var.php';
if(function_exists('mysql_connect')) {
    require ROOT_PATH.'./install/include/install_mysql.php';
} else {
    require ROOT_PATH.'./install/include/install_mysqli.php';
}

require ROOT_PATH.'./install/include/install_function.php';
require ROOT_PATH.'./install/include/install_lang.php';

$view_off = getgpc('view_off');

define('VIEW_OFF', $view_off ? TRUE : FALSE);

$allow_method = array('show_license', 'env_check', 'app_reg', 'db_init', 'ext_info', 'install_check', 'tablepre_check');

$step = intval(getgpc('step', 'R')) ? intval(getgpc('step', 'R')) : 0;
$method = getgpc('method');

if(empty($method) || !in_array($method, $allow_method)) {
    $method = isset($allow_method[$step]) ? $allow_method[$step] : '';
}

if(empty($method)) {
    show_msg('method_undefined', $method, 0);
}

if(file_exists($lockfile) && $method != 'ext_info') {
    show_msg('install_locked', '', 0);
} elseif(!class_exists('dbstuff')) {
    show_msg('database_nonexistence', '', 0);
}

timezone_set();

$uchidden = getgpc('uchidden');

if(in_array($method, array('app_reg', 'ext_info'))) {
    $isHTTPS = ($_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;
    $PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $bbserver = 'http'.($isHTTPS ? 's' : '').'://'.preg_replace("/\:\d+/", '', $_SERVER['HTTP_HOST']).($_SERVER['SERVER_PORT'] && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443 ? ':'.$_SERVER['SERVER_PORT'] : '');
    $default_ucapi = $bbserver.'/ucenter';
    $default_appurl = $bbserver.substr($PHP_SELF, 0, strrpos($PHP_SELF, '/') - 8);
}

if($method == 'show_license') {

    transfer_ucinfo($_POST);
    show_license();

}