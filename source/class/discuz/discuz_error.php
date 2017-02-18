<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class discuz_error
{
    public static function exception_error($exception) {
    
        if($exception instanceof DbException) {
            $type = 'db';
        } else {
            $type = 'system';
        }
    
        if($type == 'db') {
            $errormsg = '('.$exception->getCode().') ';
            $errormsg .= self::sql_clear($exception->getMessage());
            if($exception->getSql()) {
                $errormsg .= '<div class="sql">';
                $errormsg .= self::sql_clear($exception->getSql());
                $errormsg .= '</div>';
            }
        } else {
            $errormsg = $exception->getMessage();
        }
    
        $trace = $exception->getTrace();
        krsort($trace);
    
        $trace[] = array('file'=>$exception->getFile(), 'line'=>$exception->getLine(), 'function'=> 'break');
        $phpmsg = array();
        foreach ($trace as $error) {
            if(!empty($error['function'])) {
                $fun = '';
                if(!empty($error['class'])) {
                    $fun .= $error['class'].$error['type'];
                }
                $fun .= $error['function'].'(';
                if(!empty($error['args'])) {
                    $mark = '';
                    foreach($error['args'] as $arg) {
                        $fun .= $mark;
                        if(is_array($arg)) {
                            $fun .= 'Array';
                        } elseif(is_bool($arg)) {
                            $fun .= $arg ? 'true' : 'false';
                        } elseif(is_int($arg)) {
                            $fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? $arg : '%d';
                        } elseif(is_float($arg)) {
                            $fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? $arg : '%f';
                        } else {
                            $fun .= (defined('DISCUZ_DEBUG') && DISCUZ_DEBUG) ? '\''.dhtmlspecialchars(substr(self::clear($arg), 0, 10)).(strlen($arg) > 10 ? ' ...' : '').'\'' : '%s';
                        }
                        $mark = ', ';
                    }
                }
    
                $fun .= ')';
                $error['function'] = $fun;
            }
            $phpmsg[] = array(
                'file' => str_replace(array(DISCUZ_ROOT, '\\'), array('', '/'), $error['file']),
                'line' => $error['line'],
                'function' => $error['function'],
            );
        }
    
        self::show_error($type, $errormsg, $phpmsg);
        exit();
    
    }
    
    public static function show_error($type, $errormsg, $phpmsg = '', $typemsg = '') {
        global $_G;
    
        ob_end_clean();
        $gzip = getglobal('gzipcompress');
        ob_start($gzip ? 'ob_gzhandler' : null);
    
        $host = $_SERVER['HTTP_HOST'];
        $title = $type == 'db' ? 'Database' : 'System';
        echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>$host - $title Error</title>
	<meta http-equiv="Content-Type" content="text/html; charset={$_G['config']['output']['charset']}" />
	<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
	<style type="text/css">
	<!--
	body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
	#container { width: 1024px; }
	#message   { width: 1024px; color: black; }
    
	.red  {color: red;}
	a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
	a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
	h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
	.bg1{ background-color: #FFFFCC;}
	.bg2{ background-color: #EEEEEE;}
	.table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
	.info {
	    background: none repeat scroll 0 0 #F3F3F3;
	    border: 0px solid #aaaaaa;
	    border-radius: 10px 10px 10px 10px;
	    color: #000000;
	    font-size: 11pt;
	    line-height: 160%;
	    margin-bottom: 1em;
	    padding: 1em;
	}
    
	.help {
	    background: #F3F3F3;
	    border-radius: 10px 10px 10px 10px;
	    font: 12px verdana, arial, sans-serif;
	    text-align: center;
	    line-height: 160%;
	    padding: 1em;
	}
    
	.sql {
	    background: none repeat scroll 0 0 #FFFFCC;
	    border: 1px solid #aaaaaa;
	    color: #000000;
	    font: arial, sans-serif;
	    font-size: 9pt;
	    line-height: 160%;
	    margin-top: 1em;
	    padding: 4px;
	}
	-->
	</style>
</head>
<body>
<div id="container">
<h1>Discuz! $title Error</h1>
<div class='info'>$errormsg</div>
    
    
EOT;
        if(!empty($phpmsg)) {
            echo '<div class="info">';
            echo '<p><strong>PHP Debug</strong></p>';
            echo '<table cellpadding="5" cellspacing="1" width="100%" class="table">';
            if(is_array($phpmsg)) {
                echo '<tr class="bg2"><td>No.</td><td>File</td><td>Line</td><td>Code</td></tr>';
                foreach($phpmsg as $k => $msg) {
                    $k++;
                    echo '<tr class="bg1">';
                    echo '<td>'.$k.'</td>';
                    echo '<td>'.$msg['file'].'</td>';
                    echo '<td>'.$msg['line'].'</td>';
                    echo '<td>'.$msg['function'].'</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td><ul>'.$phpmsg.'</ul></td></tr>';
            }
            echo '</table></div>';
        }
    
    
        $helplink = '';
        if($type == 'db') {
            $helplink = "http://faq.comsenz.com/?type=mysql&dberrno=".rawurlencode(DB::errno())."&dberror=".rawurlencode(str_replace(DB::object()->tablepre, '', DB::error()));
            $helplink = "<a href=\"$helplink\" target=\"_blank\"><span class=\"red\">Need Help?</span></a>";
        }
    
        $endmsg = lang('error', 'error_end_message', array('host'=>$host));
        echo <<<EOT
<div class="help">$endmsg. $helplink</div>
</div>
</body>
</html>
EOT;
        $exit && exit();
    
    }
}