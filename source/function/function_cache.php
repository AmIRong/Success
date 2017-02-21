<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function updatecache($cachename = '') {

    $updatelist = empty($cachename) ? array() : (is_array($cachename) ? $cachename : array($cachename));

    if(!$updatelist) {
        @include_once libfile('cache/setting', 'function');
        build_cache_setting();
        $cachedir = DISCUZ_ROOT.'./source/function/cache';
        $cachedirhandle = dir($cachedir);
        while($entry = $cachedirhandle->read()) {
            if(!in_array($entry, array('.', '..')) && preg_match("/^cache\_([\_\w]+)\.php$/", $entry, $entryr) && $entryr[1] != 'setting' && substr($entry, -4) == '.php' && is_file($cachedir.'/'.$entry)) {
                @include_once libfile('cache/'.$entryr[1], 'function');
                call_user_func('build_cache_'.$entryr[1]);
            }
        }
        foreach(C::t('common_plugin')->fetch_all_data(1) as $plugin) {
            $dir = substr($plugin['directory'], 0, -1);
            $cachedir = DISCUZ_ROOT.'./source/plugin/'.$dir.'/cache';
            if(file_exists($cachedir)) {
                $cachedirhandle = dir($cachedir);
                while($entry = $cachedirhandle->read()) {
                    if(!in_array($entry, array('.', '..')) && preg_match("/^cache\_([\_\w]+)\.php$/", $entry, $entryr) && substr($entry, -4) == '.php' && is_file($cachedir.'/'.$entry)) {
                        @include_once libfile('cache/'.$entryr[1], 'plugin/'.$dir);
                        call_user_func('build_cache_plugin_'.$entryr[1]);
                    }
                }
            }
        }
    } else {
        foreach($updatelist as $entry) {
            $entrys = explode(':', $entry);
            if(count($entrys) == 1) {
                @include_once libfile('cache/'.$entry, 'function');
                call_user_func('build_cache_'.$entry);
            } else {
                @include_once libfile('cache/'.$entrys[1], 'plugin/'.$entrys[0]);
                call_user_func('build_cache_plugin_'.$entrys[1]);
            }
        }
    }

}