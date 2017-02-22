<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class template {
    function parse_template($tplfile, $templateid, $tpldir, $file, $cachefile) {
        $basefile = basename(DISCUZ_ROOT.$tplfile, '.htm');
        $file == 'common/header' && defined('CURMODULE') && CURMODULE && $file = 'common/header_'.CURMODULE;
        $this->file = $file;
    
        if($fp = @fopen(DISCUZ_ROOT.$tplfile, 'r')) {
            $template = @fread($fp, filesize(DISCUZ_ROOT.$tplfile));
            fclose($fp);
        } elseif($fp = @fopen($filename = substr(DISCUZ_ROOT.$tplfile, 0, -4).'.php', 'r')) {
            $template = $this->getphptemplate(@fread($fp, filesize($filename)));
            fclose($fp);
        } else {
            $tpl = $tpldir.'/'.$file.'.htm';
            $tplfile = $tplfile != $tpl ? $tpl.', '.$tplfile : $tplfile;
            $this->error('template_notfound', $tplfile);
        }
    
        $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
    
        $headerexists = preg_match("/{(sub)?template\s+[\w:\/]+?header\}/", $template);
        $this->subtemplates = array();
        for($i = 1; $i <= 3; $i++) {
            if(strexists($template, '{subtemplate')) {
                $template = preg_replace("/[\n\r\t]*(\<\!\-\-)?\{subtemplate\s+([a-z0-9_:\/]+)\}(\-\-\>)?[\n\r\t]*/ies", "\$this->loadsubtemplate('\\2')", $template);
            }
        }
    
        $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
        $template = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->languagevar('\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{block\/(\d+?)\}[\n\r\t]*/ie", "\$this->blocktags('\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{blockdata\/(\d+?)\}[\n\r\t]*/ie", "\$this->blockdatatags('\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{ad\/(.+?)\}[\n\r\t]*/ie", "\$this->adtags('\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{ad\s+([a-zA-Z0-9_\[\]]+)\/(.+?)\}[\n\r\t]*/ie", "\$this->adtags('\\2', '\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/ie", "\$this->datetags('\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{avatar\((.+?)\)\}[\n\r\t]*/ie", "\$this->avatartags('\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{eval\}\s*(\<\!\-\-)*(.+?)(\-\-\>)*\s*\{\/eval\}[\n\r\t]*/ies", "\$this->evaltags('\\2')", $template);
        $template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/ies", "\$this->evaltags('\\1')", $template);
        $template = preg_replace("/[\n\r\t]*\{csstemplate\}[\n\r\t]*/ies", "\$this->loadcsstemplate()", $template);
        $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
        $template = preg_replace("/\{hook\/(\w+?)(\s+(.+?))?\}/ie", "\$this->hooktags('\\1', '\\3')", $template);
        $template = preg_replace("/$var_regexp/es", "template::addquote('<?=\\1?>')", $template);
        $template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "\$this->addquote('<?=\\1?>')", $template);
    
        $headeradd = $headerexists ? "hookscriptoutput('$basefile');" : '';
        if(!empty($this->subtemplates)) {
            $headeradd .= "\n0\n";
            foreach($this->subtemplates as $fname) {
                $headeradd .= "|| checktplrefresh('$tplfile', '$fname', ".time().", '$templateid', '$cachefile', '$tpldir', '$file')\n";
            }
            $headeradd .= ';';
        }
    
        if(!empty($this->blocks)) {
            $headeradd .= "\n";
            $headeradd .= "block_get('".implode(',', $this->blocks)."');";
        }
    
        $template = "<? if(!defined('IN_DISCUZ')) exit('Access Denied'); {$headeradd}?>\n$template";
    
        $template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_:\/]+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? include template(\'\\1\'); ?>')", $template);
        $template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('<? include template(\'\\1\'); ?>')", $template);
        $template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('<? echo \\1; ?>')", $template);
    
        $template = preg_replace("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/ies", "\$this->stripvtags('\\1<? if(\\2) { ?>\\3')", $template);
        $template = preg_replace("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies", "\$this->stripvtags('\\1<? } elseif(\\2) { ?>\\3')", $template);
        $template = preg_replace("/\{else\}/i", "<? } else { ?>", $template);
        $template = preg_replace("/\{\/if\}/i", "<? } ?>", $template);
    
        $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2) { ?>')", $template);
        $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/ies", "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2 => \\3) { ?>')", $template);
        $template = preg_replace("/\{\/loop\}/i", "<? } ?>", $template);
    
        $template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
        if(!empty($this->replacecode)) {
            $template = str_replace($this->replacecode['search'], $this->replacecode['replace'], $template);
        }
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);
    
        if(!@$fp = fopen(DISCUZ_ROOT.$cachefile, 'w')) {
            $this->error('directory_notfound', dirname(DISCUZ_ROOT.$cachefile));
        }
    
        $template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "\$this->transamp('\\0')", $template);
        $template = preg_replace("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/ies", "\$this->stripscriptamp('\\1', '\\2')", $template);
        $template = preg_replace("/[\n\r\t]*\{block\s+([a-zA-Z0-9_\[\]]+)\}(.+?)\{\/block\}/ies", "\$this->stripblock('\\1', '\\2')", $template);
        $template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
        $template = preg_replace("/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template);
    
        flock($fp, 2);
        fwrite($fp, $template);
        fclose($fp);
    }
    
    function error($message, $tplname) {
        discuz_error::template_error($message, $tplname);
    }
    
    function languagevar($var) {
        $vars = explode(':', $var);
        $isplugin = count($vars) == 2;
        if(!$isplugin) {
            !isset($this->language['inner']) && $this->language['inner'] = array();
            $langvar = &$this->language['inner'];
        } else {
            !isset($this->language['plugin'][$vars[0]]) && $this->language['plugin'][$vars[0]] = array();
            $langvar = &$this->language['plugin'][$vars[0]];
            $var = &$vars[1];
        }
        if(!isset($langvar[$var])) {
            $this->language['inner'] = lang('template');
            if(!$isplugin) {
    
                if(defined('IN_MOBILE')) {
                    $mobiletpl = getglobal('mobiletpl');
                    list($path) = explode('/', str_replace($mobiletpl[IN_MOBILE].'/', '', $this->file));
                } else {
                    list($path) = explode('/', $this->file);
                }
    
                foreach(lang($path.'/template') as $k => $v) {
                    $this->language['inner'][$k] = $v;
                }
    
                if(defined('IN_MOBILE')) {
                    foreach(lang('mobile/template') as $k => $v) {
                        $this->language['inner'][$k] = $v;
                    }
                }
            } else {
                global $_G;
                if(empty($_G['config']['plugindeveloper'])) {
                    loadcache('pluginlanguage_template');
                } elseif(!isset($_G['cache']['pluginlanguage_template'][$vars[0]]) && preg_match("/^[a-z]+[a-z0-9_]*$/i", $vars[0])) {
                    if(@include(DISCUZ_ROOT.'./data/plugindata/'.$vars[0].'.lang.php')) {
                        $_G['cache']['pluginlanguage_template'][$vars[0]] = $templatelang[$vars[0]];
                    } else {
                        loadcache('pluginlanguage_template');
                    }
                }
                $this->language['plugin'][$vars[0]] = $_G['cache']['pluginlanguage_template'][$vars[0]];
            }
        }
        if(isset($langvar[$var])) {
            return $langvar[$var];
        } else {
            return '!'.$var.'!';
        }
    }
    
    function hooktags($hookid, $key = '') {
        global $_G;
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--HOOK_TAG_$i-->";
        $dev = '';
        if(isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] == 2) {
            $dev = "echo '<hook>[".($key ? 'array' : 'string')." $hookid".($key ? '/\'.'.$key.'.\'' : '')."]</hook>';";
        }
        $key = $key !== '' ? "[$key]" : '';
        $this->replacecode['replace'][$i] = "<?php {$dev}if(!empty(\$_G['setting']['pluginhooks']['$hookid']$key)) echo \$_G['setting']['pluginhooks']['$hookid']$key;?>";
        return $search;
    }
    
    function addquote($var) {
        return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
    }
    
    function stripvtags($expr, $statement = '') {
        $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace("\\\"", "\"", $statement);
        return $expr.$statement;
    }
}

?>