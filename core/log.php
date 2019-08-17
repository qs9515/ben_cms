<?php
/**
 *
 * 文件说明:
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/21
 * Time: 11:25
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core;
use Katzgrau\KLogger\Logger;
class log extends Logger
{
    public static $loger=null;
    public static function init()
    {
        if(self::$loger==null)
        {
            self::$loger=new Logger(conf::get('system.log_file'), conf::get('system.log_level'), array('extension' => 'log','flushFrequency'=>true));
        }
        return self::$loger;
    }
}