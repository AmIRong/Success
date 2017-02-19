<?php
if(!defined('IN_COMSENZ')) {
    exit('Access Denied');
}

function getgpc($k, $t='GP') {
    $t = strtoupper($t);
    switch($t) {
        case 'GP' : isset($_POST[$k]) ? $var = &$_POST : $var = &$_GET; break;
        case 'G': $var = &$_GET; break;
        case 'P': $var = &$_POST; break;
        case 'C': $var = &$_COOKIE; break;
        case 'R': $var = &$_REQUEST; break;
    }
    return isset($var[$k]) ? $var[$k] : null;
}

function timezone_set($timeoffset = 8) {
    if(function_exists('date_default_timezone_set')) {
        @date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
    }
}

function transfer_ucinfo(&$post) {
    global $uchidden;
    if(isset($post['ucapi']) && isset($post['ucfounderpw'])) {
        $arr = array(
            'ucapi' => $post['ucapi'],
            'ucfounderpw' => $post['ucfounderpw']
        );
        $uchidden = urlencode(serialize($arr));
    } else {
        $uchidden = '';
    }
}

function show_license() {
    global $self, $uchidden, $step;
    $next = $step + 1;
    if(VIEW_OFF) {

        show_msg('license_contents', lang('license'), 1);

    } else {

        show_header();

        $license = str_replace('  ', '&nbsp; ', lang('license'));
        $lang_agreement_yes = lang('agreement_yes');
        $lang_agreement_no = lang('agreement_no');
        echo <<<EOT
</div>
<div class="main" style="margin-top:-123px;">
	<div class="licenseblock">$license</div>
	<div class="btnbox marginbot">
		<form method="get" autocomplete="off" action="index.php">
		<input type="hidden" name="step" value="$next">
		<input type="hidden" name="uchidden" value="$uchidden">
		<input type="submit" name="submit" value="{$lang_agreement_yes}" style="padding: 2px">&nbsp;
		<input type="button" name="exit" value="{$lang_agreement_no}" style="padding: 2px" onclick="javascript: window.close(); return false;">
		</form>
	</div>
EOT;

        show_footer();

    }
}

function show_header() {
    define('SHOW_HEADER', TRUE);
    global $step;
    $version = DISCUZ_VERSION;
    $release = DISCUZ_RELEASE;
    $install_lang = lang(INSTALL_LANG);
    $title = lang('title_install');
    $charset = CHARSET;
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$title</title>
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}

	function showmessage(message) {
		document.getElementById('notice').innerHTML += message + '<br />';
	}
</script>
<meta content="Comsenz Inc." name="Copyright" />
</head>
<div class="container">
	<div class="header">
		<h1>$title</h1>
		<span>Discuz!$version $install_lang $release</span>
EOT;

    $step > 0 && show_step($step);
}

function lang($lang_key, $force = true) {
    return isset($GLOBALS['lang'][$lang_key]) ? $GLOBALS['lang'][$lang_key] : ($force ? $lang_key : '');
}

function show_footer($quit = true) {

    echo <<<EOT
		<div class="footer">&copy;2001 - 2013 <a href="http://www.comsenz.com/">Comsenz</a> Inc.</div>
	</div>
</div>
</body>
</html>
EOT;
    $quit && exit();
}

function env_check(&$env_items) {
    foreach($env_items as $key => $item) {
        if($key == 'php') {
            $env_items[$key]['current'] = PHP_VERSION;
        } elseif($key == 'attachmentupload') {
            $env_items[$key]['current'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';
        } elseif($key == 'gdversion') {
            $tmp = function_exists('gd_info') ? gd_info() : array();
            $env_items[$key]['current'] = empty($tmp['GD Version']) ? 'noext' : $tmp['GD Version'];
            unset($tmp);
        } elseif($key == 'diskspace') {
            if(function_exists('disk_free_space')) {
                $env_items[$key]['current'] = floor(disk_free_space(ROOT_PATH) / (1024*1024)).'M';
            } else {
                $env_items[$key]['current'] = 'unknow';
            }
        } elseif(isset($item['c'])) {
            $env_items[$key]['current'] = constant($item['c']);
        }

        $env_items[$key]['status'] = 1;
        if($item['r'] != 'notset' && strcmp($env_items[$key]['current'], $item['r']) < 0) {
            $env_items[$key]['status'] = 0;
        }
    }
}

function dirfile_check(&$dirfile_items) {
    foreach($dirfile_items as $key => $item) {
        $item_path = $item['path'];
        if($item['type'] == 'dir') {
            if(!dir_writeable(ROOT_PATH.$item_path)) {
                if(is_dir(ROOT_PATH.$item_path)) {
                    $dirfile_items[$key]['status'] = 0;
                    $dirfile_items[$key]['current'] = '+r';
                } else {
                    $dirfile_items[$key]['status'] = -1;
                    $dirfile_items[$key]['current'] = 'nodir';
                }
            } else {
                $dirfile_items[$key]['status'] = 1;
                $dirfile_items[$key]['current'] = '+r+w';
            }
        } else {
            if(file_exists(ROOT_PATH.$item_path)) {
                if(is_writable(ROOT_PATH.$item_path)) {
                    $dirfile_items[$key]['status'] = 1;
                    $dirfile_items[$key]['current'] = '+r+w';
                } else {
                    $dirfile_items[$key]['status'] = 0;
                    $dirfile_items[$key]['current'] = '+r';
                }
            } else {
                if(dir_writeable(dirname(ROOT_PATH.$item_path))) {
                    $dirfile_items[$key]['status'] = 1;
                    $dirfile_items[$key]['current'] = '+r+w';
                } else {
                    $dirfile_items[$key]['status'] = -1;
                    $dirfile_items[$key]['current'] = 'nofile';
                }
            }
        }
    }
}

function dir_writeable($dir) {
    $writeable = 0;
    if(!is_dir($dir)) {
        @mkdir($dir, 0777);
    }
    if(is_dir($dir)) {
        if($fp = @fopen("$dir/test.txt", 'w')) {
            @fclose($fp);
            @unlink("$dir/test.txt");
            $writeable = 1;
        } else {
            $writeable = 0;
        }
    }
    return $writeable;
}

function show_env_result(&$env_items, &$dirfile_items, &$func_items, &$filesock_items) {

    $env_str = $file_str = $dir_str = $func_str = '';
    $error_code = 0;

    foreach($env_items as $key => $item) {
        if($key == 'php' && strcmp($item['current'], $item['r']) < 0) {
            show_msg('php_version_too_low', $item['current'], 0);
        }
        $status = 1;
        if($item['r'] != 'notset') {
            if(intval($item['current']) && intval($item['r'])) {
                if(intval($item['current']) < intval($item['r'])) {
                    $status = 0;
                    $error_code = ENV_CHECK_ERROR;
                }
            } else {
                if(strcmp($item['current'], $item['r']) < 0) {
                    $status = 0;
                    $error_code = ENV_CHECK_ERROR;
                }
            }
        }
        if(VIEW_OFF) {
            $env_str .= "\t\t<runCondition name=\"$key\" status=\"$status\" Require=\"$item[r]\" Best=\"$item[b]\" Current=\"$item[current]\"/>\n";
        } else {
            $env_str .= "<tr>\n";
            $env_str .= "<td>".lang($key)."</td>\n";
            $env_str .= "<td class=\"padleft\">".lang($item['r'])."</td>\n";
            $env_str .= "<td class=\"padleft\">".lang($item['b'])."</td>\n";
            $env_str .= ($status ? "<td class=\"w pdleft1\">" : "<td class=\"nw pdleft1\">").$item['current']."</td>\n";
            $env_str .= "</tr>\n";
        }
    }

    foreach($dirfile_items as $key => $item) {
        $tagname = $item['type'] == 'file' ? 'File' : 'Dir';
        $variable = $item['type'].'_str';

        if(VIEW_OFF) {
            if($item['status'] == 0) {
                $error_code = ENV_CHECK_ERROR;
            }
            $$variable .= "\t\t\t<File name=\"$item[path]\" status=\"$item[status]\" requirePermisson=\"+r+w\" currentPermisson=\"$item[current]\" />\n";
        } else {
            $$variable .= "<tr>\n";
            $$variable .= "<td>$item[path]</td><td class=\"w pdleft1\">".lang('writeable')."</td>\n";
            if($item['status'] == 1) {
                $$variable .= "<td class=\"w pdleft1\">".lang('writeable')."</td>\n";
            } elseif($item['status'] == -1) {
                $error_code = ENV_CHECK_ERROR;
                $$variable .= "<td class=\"nw pdleft1\">".lang('nodir')."</td>\n";
            } else {
                $error_code = ENV_CHECK_ERROR;
                $$variable .= "<td class=\"nw pdleft1\">".lang('unwriteable')."</td>\n";
            }
            $$variable .= "</tr>\n";
        }
    }

    if(VIEW_OFF) {

        $str = "<root>\n";
        $str .= "\t<runConditions>\n";
        $str .= $env_str;
        $str .= "\t</runConditions>\n";
        $str .= "\t<FileDirs>\n";
        $str .= "\t\t<Dirs>\n";
        $str .= $dir_str;
        $str .= "\t\t</Dirs>\n";
        $str .= "\t\t<Files>\n";
        $str .= $file_str;
        $str .= "\t\t</Files>\n";
        $str .= "\t</FileDirs>\n";
        $str .= "\t<error errorCode=\"$error_code\" errorMessage=\"\" />\n";
        $str .= "</root>";
        echo $str;
        exit;

    } else {

        show_header();

        echo "<h2 class=\"title\">".lang('env_check')."</h2>\n";
        echo "<table class=\"tb\" style=\"margin:20px 0 20px 55px;\">\n";
        echo "<tr>\n";
        echo "\t<th>".lang('project')."</th>\n";
        echo "\t<th class=\"padleft\">".lang('ucenter_required')."</th>\n";
        echo "\t<th class=\"padleft\">".lang('ucenter_best')."</th>\n";
        echo "\t<th class=\"padleft\">".lang('curr_server')."</th>\n";
        echo "</tr>\n";
        echo $env_str;
        echo "</table>\n";

        echo "<h2 class=\"title\">".lang('priv_check')."</h2>\n";
        echo "<table class=\"tb\" style=\"margin:20px 0 20px 55px;width:90%;\">\n";
        echo "\t<tr>\n";
        echo "\t<th>".lang('step1_file')."</th>\n";
        echo "\t<th class=\"padleft\">".lang('step1_need_status')."</th>\n";
        echo "\t<th class=\"padleft\">".lang('step1_status')."</th>\n";
        echo "</tr>\n";
        echo $file_str;
        echo $dir_str;
        echo "</table>\n";

        foreach($func_items as $item) {
            $status = function_exists($item);
            $func_str .= "<tr>\n";
            $func_str .= "<td>$item()</td>\n";
            if($status) {
                $func_str .= "<td class=\"w pdleft1\">".lang('supportted')."</td>\n";
                $func_str .= "<td class=\"padleft\">".lang('none')."</td>\n";
            } else {
                $error_code = ENV_CHECK_ERROR;
                $func_str .= "<td class=\"nw pdleft1\">".lang('unsupportted')."</td>\n";
                $func_str .= "<td><font color=\"red\">".lang('advice_'.$item)."</font></td>\n";
            }
        }
        $func_strextra = '';
        $filesock_disabled = 0;
        foreach($filesock_items as $item) {
            $status = function_exists($item);
            $func_strextra .= "<tr>\n";
            $func_strextra .= "<td>$item()</td>\n";
            if($status) {
                $func_strextra .= "<td class=\"w pdleft1\">".lang('supportted')."</td>\n";
                $func_strextra .= "<td class=\"padleft\">".lang('none')."</td>\n";
                break;
            } else {
                $filesock_disabled++;
                $func_strextra .= "<td class=\"nw pdleft1\">".lang('unsupportted')."</td>\n";
                $func_strextra .= "<td><font color=\"red\">".lang('advice_'.$item)."</font></td>\n";
            }
        }
        if($filesock_disabled == count($filesock_items)) {
            $error_code = ENV_CHECK_ERROR;
        }
        echo "<h2 class=\"title\">".lang('func_depend')."</h2>\n";
        echo "<table class=\"tb\" style=\"margin:20px 0 20px 55px;width:90%;\">\n";
        echo "<tr>\n";
        echo "\t<th>".lang('func_name')."</th>\n";
        echo "\t<th class=\"padleft\">".lang('check_result')."</th>\n";
        echo "\t<th class=\"padleft\">".lang('suggestion')."</th>\n";
        echo "</tr>\n";
        echo $func_str.$func_strextra;
        echo "</table>\n";

        show_next_step(2, $error_code);

        show_footer();

    }

}
function show_step($step) {

    global $method;

    $laststep = 4;
    $title = lang('step_'.$method.'_title');
    $comment = lang('step_'.$method.'_desc');
    $step_title_1 = lang('step_title_1');
    $step_title_2 = lang('step_title_2');
    $step_title_3 = lang('step_title_3');
    $step_title_4 = lang('step_title_4');

    $stepclass = array();
    for($i = 1; $i <= $laststep; $i++) {
        $stepclass[$i] = $i == $step ? 'current' : ($i < $step ? '' : 'unactivated');
    }
    $stepclass[$laststep] .= ' last';

    echo <<<EOT
	<div class="setup step{$step}">
		<h2>$title</h2>
		<p>$comment</p>
	</div>
	<div class="stepstat">
		<ul>
			<li class="$stepclass[1]">$step_title_1</li>
			<li class="$stepclass[2]">$step_title_2</li>
			<li class="$stepclass[3]">$step_title_3</li>
			<li class="$stepclass[4]">$step_title_4</li>
		</ul>
		<div class="stepstatbg stepstat1"></div>
	</div>
</div>
<div class="main">
EOT;

}
    
function show_next_step($step, $error_code) {
    global $uchidden;
    echo "<form action=\"index.php\" method=\"post\">\n";
    echo "<input type=\"hidden\" name=\"step\" value=\"$step\" />";
    if(isset($GLOBALS['hidden'])) {
        echo $GLOBALS['hidden'];
    }
    echo "<input type=\"hidden\" name=\"uchidden\" value=\"$uchidden\" />";
    if($error_code == 0) {
        $nextstep = "<input type=\"button\" onclick=\"history.back();\" value=\"".lang('old_step')."\"><input type=\"submit\" value=\"".lang('new_step')."\">\n";
    } else {
        $nextstep = "<input type=\"button\" disabled=\"disabled\" value=\"".lang('not_continue')."\">\n";
    }
    echo "<div class=\"btnbox marginbot\">".$nextstep."</div>\n";
    echo "</form>\n";
}