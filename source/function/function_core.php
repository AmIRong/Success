<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define('DISCUZ_CORE_FUNCTION', true);

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

function checkrobot($useragent = '') {
    static $kw_spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
    static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

    $useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
    if(strpos($useragent, 'http://') === false && dstrpos($useragent, $kw_browsers)) return false;
    if(dstrpos($useragent, $kw_spiders)) return true;
    return false;
}

function dstrpos($string, $arr, $returnvalue = false) {
    if(empty($string)) return false;
    foreach((array)$arr as $v) {
        if(strpos($string, $v) !== false) {
            $return = $returnvalue ? $v : true;
            return $return;
        }
    }
    return false;
}

function dhtmlspecialchars($string, $flags = null) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val, $flags);
        }
    } else {
        if($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
            if(strpos($string, '&amp;#') !== false) {
                $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            if(PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if(strtolower(CHARSET) == 'utf-8') {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }
    return $string;
}

function random($length, $numeric = 0) {
    $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
    if($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {

    global $_G;

    $config = $_G['config']['cookie'];

    $_G['cookie'][$var] = $value;
    $var = ($prefix ? $config['cookiepre'] : '').$var;
    $_COOKIE[$var] = $value;

    if($value == '' || $life < 0) {
        $value = '';
        $life = -1;
    }

    if(defined('IN_MOBILE')) {
        $httponly = false;
    }

    $life = $life > 0 ? getglobal('timestamp') + $life : ($life < 0 ? getglobal('timestamp') - 31536000 : 0);
    $path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'].'; HttpOnly' : $config['cookiepath'];

    $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
    if(PHP_VERSION < '5.2.0') {
        setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
    } else {
        setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
    }
}

function getglobal($key, $group = null) {
    global $_G;
    $key = explode('/', $group === null ? $key : $group.'/'.$key);
    $v = &$_G;
    foreach ($key as $k) {
        if (!isset($v[$k])) {
            return null;
        }
        $v = &$v[$k];
    }
    return $v;
}

function setglobal($key , $value, $group = null) {
    global $_G;
    $key = explode('/', $group === null ? $key : $group.'/'.$key);
    $p = &$_G;
    foreach ($key as $k) {
        if(!isset($p[$k]) || !is_array($p[$k])) {
            $p[$k] = array();
        }
        $p = &$p[$k];
    }
    $p = $value;
    return true;
}

function lang($file, $langvar = null, $vars = array(), $default = null) {
    global $_G;
    $fileinput = $file;
    list($path, $file) = explode('/', $file);
    if(!$file) {
        $file = $path;
        $path = '';
    }
    if(strpos($file, ':') !== false) {
        $path = 'plugin';
        list($file) = explode(':', $file);
    }

    if($path != 'plugin') {
        $key = $path == '' ? $file : $path.'_'.$file;
        if(!isset($_G['lang'][$key])) {
            include DISCUZ_ROOT.'./source/language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
            $_G['lang'][$key] = $lang;
        }
        if(defined('IN_MOBILE') && !defined('TPL_DEFAULT')) {
            include DISCUZ_ROOT.'./source/language/mobile/lang_template.php';
            $_G['lang'][$key] = array_merge($_G['lang'][$key], $lang);
        }
        if($file != 'error' && !isset($_G['cache']['pluginlanguage_system'])) {
            loadcache('pluginlanguage_system');
        }
        if(!isset($_G['hooklang'][$fileinput])) {
            if(isset($_G['cache']['pluginlanguage_system'][$fileinput]) && is_array($_G['cache']['pluginlanguage_system'][$fileinput])) {
                $_G['lang'][$key] = array_merge($_G['lang'][$key], $_G['cache']['pluginlanguage_system'][$fileinput]);
            }
            $_G['hooklang'][$fileinput] = true;
        }
        $returnvalue = &$_G['lang'];
    } else {
        if(empty($_G['config']['plugindeveloper'])) {
            loadcache('pluginlanguage_script');
        } elseif(!isset($_G['cache']['pluginlanguage_script'][$file]) && preg_match("/^[a-z]+[a-z0-9_]*$/i", $file)) {
            if(@include(DISCUZ_ROOT.'./data/plugindata/'.$file.'.lang.php')) {
                $_G['cache']['pluginlanguage_script'][$file] = $scriptlang[$file];
            } else {
                loadcache('pluginlanguage_script');
            }
        }
        $returnvalue = & $_G['cache']['pluginlanguage_script'];
        $key = &$file;
    }
    $return = $langvar !== null ? (isset($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : null) : $returnvalue[$key];
    $return = $return === null ? ($default !== null ? $default : $langvar) : $return;
    $searchs = $replaces = array();
    if($vars && is_array($vars)) {
        foreach($vars as $k => $v) {
            $searchs[] = '{'.$k.'}';
            $replaces[] = $v;
        }
    }
    if(is_string($return) && strpos($return, '{_G/') !== false) {
        preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
        foreach($gvar[0] as $k => $v) {
            $searchs[] = $v;
            $replaces[] = getglobal($gvar[1][$k]);
        }
    }
    $return = str_replace($searchs, $replaces, $return);
    return $return;
}

function loadcache($cachenames, $force = false) {
    global $_G;
    static $loadedcache = array();
    $cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
    $caches = array();
    foreach ($cachenames as $k) {
        if(!isset($loadedcache[$k]) || $force) {
            $caches[] = $k;
            $loadedcache[$k] = true;
        }
    }

    if(!empty($caches)) {
        $cachedata = C::t('common_syscache')->fetch_all($caches);
        foreach($cachedata as $cname => $data) {
            if($cname == 'setting') {
                $_G['setting'] = $data;
            } elseif($cname == 'usergroup_'.$_G['groupid']) {
                $_G['cache'][$cname] = $_G['group'] = $data;
            } elseif($cname == 'style_default') {
                $_G['cache'][$cname] = $_G['style'] = $data;
            } elseif($cname == 'grouplevels') {
                $_G['grouplevels'] = $data;
            } else {
                $_G['cache'][$cname] = $data;
            }
        }
    }
    return true;
}

