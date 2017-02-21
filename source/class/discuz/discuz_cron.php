<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class discuz_cron
{
    public static function run($cronid = 0) {
        global $_G;
        $cron = $cronid ? C::t('common_cron')->fetch($cronid) : C::t('common_cron')->fetch_nextrun(TIMESTAMP);
    
        $processname ='DZ_CRON_'.(empty($cron) ? 'CHECKER' : $cron['cronid']);
    
        if($cronid && !empty($cron)) {
            discuz_process::unlock($processname);
        }
    
        if(discuz_process::islocked($processname, 600)) {
            return false;
        }
    
        if($cron) {
    
            $cron['filename'] = str_replace(array('..', '/', '\\'), '', $cron['filename']);
            $efile = explode(':', $cron['filename']);
            if(count($efile) > 1) {
                $cronfile = in_array($efile[0], $_G['setting']['plugins']['available']) ? DISCUZ_ROOT.'./source/plugin/'.$efile[0].'/cron/'.$efile[1] : '';
            } else {
                $cronfile = DISCUZ_ROOT.'./source/include/cron/'.$cron['filename'];
            }
    
            if($cronfile) {
                $cron['minute'] = explode("\t", $cron['minute']);
                self::setnextime($cron);
    
                @set_time_limit(1000);
                @ignore_user_abort(TRUE);
    
                if(!@include $cronfile) {
                    return false;
                }
            }
    
        }
    
        self::nextcron();
        discuz_process::unlock($processname);
        return true;
    }
}