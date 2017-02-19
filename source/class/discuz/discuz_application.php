<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class discuz_application extends discuz_base{
    var $init_db = true;
    static function &instance() {
        static $object;
        if(empty($object)) {
            $object = new self();
        }
        return $object;
    }
    
    public function __construct() {
        $this->_init_env();
        $this->_init_config();
        $this->_init_input();
        $this->_init_output();
    }
    
    private function _init_env() {
    
        error_reporting(E_ERROR);
        if(PHP_VERSION < '5.3.0') {
            set_magic_quotes_runtime(0);
        }
    
        define('MAGIC_QUOTES_GPC', function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());
        define('ICONV_ENABLE', function_exists('iconv'));
        define('MB_ENABLE', function_exists('mb_convert_encoding'));
        define('EXT_OBGZIP', function_exists('ob_gzhandler'));
    
        define('TIMESTAMP', time());
        $this->timezone_set();
    
        if(!defined('DISCUZ_CORE_FUNCTION') && !@include(DISCUZ_ROOT.'./source/function/function_core.php')) {
            exit('function_core.php is missing');
        }
    
        if(function_exists('ini_get')) {
            $memorylimit = @ini_get('memory_limit');
            if($memorylimit && return_bytes($memorylimit) < 33554432 && function_exists('ini_set')) {
                ini_set('memory_limit', '128m');
            }
        }
    
        define('IS_ROBOT', checkrobot());
    
        foreach ($GLOBALS as $key => $value) {
            if (!isset($this->superglobal[$key])) {
                $GLOBALS[$key] = null; unset($GLOBALS[$key]);
            }
        }
    
        global $_G;
        $_G = array(
            'uid' => 0,
            'username' => '',
            'adminid' => 0,
            'groupid' => 1,
            'sid' => '',
            'formhash' => '',
            'connectguest' => 0,
            'timestamp' => TIMESTAMP,
            'starttime' => microtime(true),
            'clientip' => $this->_get_client_ip(),
            'remoteport' => $_SERVER['REMOTE_PORT'],
            'referer' => '',
            'charset' => '',
            'gzipcompress' => '',
            'authkey' => '',
            'timenow' => array(),
            'widthauto' => 0,
            'disabledwidthauto' => 0,
    
            'PHP_SELF' => '',
            'siteurl' => '',
            'siteroot' => '',
            'siteport' => '',
    
            'pluginrunlist' => !defined('PLUGINRUNLIST') ? array() : explode(',', PLUGINRUNLIST),
    
            'config' => array(),
            'setting' => array(),
            'member' => array(),
            'group' => array(),
            'cookie' => array(),
            'style' => array(),
            'cache' => array(),
            'session' => array(),
            'lang' => array(),
            'my_app' => array(),
            'my_userapp' => array(),
    
            'fid' => 0,
            'tid' => 0,
            'forum' => array(),
            'thread' => array(),
            'rssauth' => '',
    
            'home' => array(),
            'space' => array(),
    
            'block' => array(),
            'article' => array(),
    
            'action' => array(
                'action' => APPTYPEID,
                'fid' => 0,
                'tid' => 0,
            ),
    
            'mobile' => '',
            'notice_structure' => array(
                'mypost' => array('post','pcomment','activity','reward','goods','at'),
                'interactive' => array('poke','friend','wall','comment','click','sharenotice'),
                'system' => array('system','myapp','credit','group','verify','magic','task','show','group','pusearticle','mod_member','blog','article'),
                'manage' => array('mod_member','report','pmreport'),
                'app' => array(),
            ),
            'mobiletpl' => array('1' => 'mobile', '2' => 'touch', '3' => 'wml', 'yes' => 'mobile'),
        );
        $_G['PHP_SELF'] = dhtmlspecialchars($this->_get_script_url());
        $_G['basescript'] = CURSCRIPT;
        $_G['basefilename'] = basename($_G['PHP_SELF']);
        $sitepath = substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'));
        if(defined('IN_API')) {
            $sitepath = preg_replace("/\/api\/?.*?$/i", '', $sitepath);
        } elseif(defined('IN_ARCHIVER')) {
            $sitepath = preg_replace("/\/archiver/i", '', $sitepath);
        }
        $_G['isHTTPS'] = ($_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS']) != 'off') ? true : false;
        $_G['siteurl'] = dhtmlspecialchars('http'.($_G['isHTTPS'] ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$sitepath.'/');
    
        $url = parse_url($_G['siteurl']);
        $_G['siteroot'] = isset($url['path']) ? $url['path'] : '';
        $_G['siteport'] = empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' || $_SERVER['SERVER_PORT'] == '443' ? '' : ':'.$_SERVER['SERVER_PORT'];
    
        if(defined('SUB_DIR')) {
            $_G['siteurl'] = str_replace(SUB_DIR, '/', $_G['siteurl']);
            $_G['siteroot'] = str_replace(SUB_DIR, '/', $_G['siteroot']);
        }
    
        $this->var = & $_G;
    
    }
    
    public function timezone_set($timeoffset = 0) {
        if(function_exists('date_default_timezone_set')) {
            @date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
        }
    }
    
    private function _get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }
    
    private function _get_script_url() {
        if(!isset($this->var['PHP_SELF'])){
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if(basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
                $this->var['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
            } else if(basename($_SERVER['PHP_SELF']) === $scriptName) {
                $this->var['PHP_SELF'] = $_SERVER['PHP_SELF'];
            } else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
                $this->var['PHP_SELF'] = $_SERVER['ORIG_SCRIPT_NAME'];
            } else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$scriptName)) !== false) {
                $this->var['PHP_SELF'] = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
            } else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT']) === 0) {
                $this->var['PHP_SELF'] = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
                $this->var['PHP_SELF'][0] != '/' && $this->var['PHP_SELF'] = '/'.$this->var['PHP_SELF'];
            } else {
                system_error('request_tainting');
            }
        }
        return $this->var['PHP_SELF'];
    }
    
    private function _init_config() {
    
        $_config = array();
        @include DISCUZ_ROOT.'./config/config_global.php';
/**        if(empty($_config)) {
            if(!file_exists(DISCUZ_ROOT.'./data/install.lock')) {
                header('location: install');
                exit;
            } else {
                system_error('config_notfound');
            }
        }**/
    
        if(empty($_config['security']['authkey'])) {
            $_config['security']['authkey'] = md5($_config['cookie']['cookiepre'].$_config['db'][1]['dbname']);
        }
    
        if(empty($_config['debug']) || !file_exists(libfile('function/debug'))) {
            define('DISCUZ_DEBUG', false);
            error_reporting(0);
        } elseif($_config['debug'] === 1 || $_config['debug'] === 2 || !empty($_REQUEST['debug']) && $_REQUEST['debug'] === $_config['debug']) {
            define('DISCUZ_DEBUG', true);
            error_reporting(E_ERROR);
            if($_config['debug'] === 2) {
                error_reporting(E_ALL);
            }
        } else {
            define('DISCUZ_DEBUG', false);
            error_reporting(0);
        }
        define('STATICURL', !empty($_config['output']['staticurl']) ? $_config['output']['staticurl'] : 'static/');
        $this->var['staticurl'] = STATICURL;
    
        $this->config = & $_config;
        $this->var['config'] = & $_config;
    
        if(substr($_config['cookie']['cookiepath'], 0, 1) != '/') {
            $this->var['config']['cookie']['cookiepath'] = '/'.$this->var['config']['cookie']['cookiepath'];
        }
        $this->var['config']['cookie']['cookiepre'] = $this->var['config']['cookie']['cookiepre'].substr(md5($this->var['config']['cookie']['cookiepath'].'|'.$this->var['config']['cookie']['cookiedomain']), 0, 4).'_';
    
    
    }
    
    private function _init_input() {
        if (isset($_GET['GLOBALS']) ||isset($_POST['GLOBALS']) ||  isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            system_error('request_tainting');
        }
    
        if(MAGIC_QUOTES_GPC) {
            $_GET = dstripslashes($_GET);
            $_POST = dstripslashes($_POST);
            $_COOKIE = dstripslashes($_COOKIE);
        }
    
        $prelength = strlen($this->config['cookie']['cookiepre']);
        foreach($_COOKIE as $key => $val) {
            if(substr($key, 0, $prelength) == $this->config['cookie']['cookiepre']) {
                $this->var['cookie'][substr($key, $prelength)] = $val;
            }
        }
    
    
        if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
            $_GET = array_merge($_GET, $_POST);
        }
    
        if(isset($_GET['page'])) {
            $_GET['page'] = rawurlencode($_GET['page']);
        }
    
        if(!(!empty($_GET['handlekey']) && preg_match('/^\w+$/', $_GET['handlekey']))) {
            unset($_GET['handlekey']);
        }
    
        if(!empty($this->var['config']['input']['compatible'])) {
            foreach($_GET as $k => $v) {
                $this->var['gp_'.$k] = daddslashes($v);
            }
        }
    
        $this->var['mod'] = empty($_GET['mod']) ? '' : dhtmlspecialchars($_GET['mod']);
        $this->var['inajax'] = empty($_GET['inajax']) ? 0 : (empty($this->var['config']['output']['ajaxvalidate']) ? 1 : ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || $_SERVER['REQUEST_METHOD'] == 'POST' ? 1 : 0));
        $this->var['page'] = empty($_GET['page']) ? 1 : max(1, intval($_GET['page']));
        $this->var['sid'] = $this->var['cookie']['sid'] = isset($this->var['cookie']['sid']) ? dhtmlspecialchars($this->var['cookie']['sid']) : '';
    
        if(empty($this->var['cookie']['saltkey'])) {
            $this->var['cookie']['saltkey'] = random(8);
            dsetcookie('saltkey', $this->var['cookie']['saltkey'], 86400 * 30, 1, 1);
        }
        $this->var['authkey'] = md5($this->var['config']['security']['authkey'].$this->var['cookie']['saltkey']);
    
    }
    
    private function _init_output() {
    
    
        if($this->config['security']['attackevasive'] && (!defined('CURSCRIPT') || !in_array($this->var['mod'], array('seccode', 'secqaa', 'swfupload')) && !defined('DISABLEDEFENSE'))) {
            require_once libfile('misc/security', 'include');
        }
    
        if(!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
            $this->config['output']['gzip'] = false;
        }
    
        $allowgzip = $this->config['output']['gzip'] && empty($this->var['inajax']) && $this->var['mod'] != 'attachment' && EXT_OBGZIP;
        setglobal('gzipcompress', $allowgzip);
    
        if(!ob_start($allowgzip ? 'ob_gzhandler' : null)) {
            ob_start();
        }
    
        setglobal('charset', $this->config['output']['charset']);
        define('CHARSET', $this->config['output']['charset']);
        if($this->config['output']['forceheader']) {
            @header('Content-Type: text/html; charset='.CHARSET);
        }
    
    }
    
    public function init() {
        if(!$this->initated) {
            $this->_init_db();
            $this->_init_setting();
            $this->_init_user();
            $this->_init_session();
            $this->_init_mobile();
            $this->_init_cron();
            $this->_init_misc();
        }
        $this->initated = true;
    }
    
    private function _init_db() {
        if($this->init_db) {
            $driver = function_exists('mysql_connect') ? 'db_driver_mysql' : 'db_driver_mysqli';
            if(getglobal('config/db/slave')) {
                $driver = function_exists('mysql_connect') ? 'db_driver_mysql_slave' : 'db_driver_mysqli_slave';
            }
            DB::init($driver, $this->config['db']);
        }
    }
}