<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(1000);
@set_magic_quotes_runtime(0);

define('IN_DISCUZ', TRUE);
define('IN_COMSENZ', TRUE);
define('ROOT_PATH', dirname(__FILE__).'/../');

require ROOT_PATH.'./source/discuz_version.php';
require ROOT_PATH.'./install/include/install_var.php';

