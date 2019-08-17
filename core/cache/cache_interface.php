<?php
/**
 *
 * 文件说明: 缓存接口
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/26
 * Time: 11:00
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\cache;
interface cache_interface
{
    public static function init($options=array());
    public static function set($items,$val);
    public static function get($items);
    public static function delete($items);
}