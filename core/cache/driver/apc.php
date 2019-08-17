<?php
/**
 *
 * 文件说明: apc缓存驱动
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/27
 * Time: 11:52
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\cache\driver;
class apc extends \core\cache\cache
{
    /**
     * get 获取缓存
     * @param $items 缓存的唯一键值
     * @return array|mixed|null
     */
    public static function get($items)
    {
        $content=null;
        if(apc_exists($items))
        {
            $data_arr=(array)parent::decode(apc_fetch($items));
            $content=isset($data_arr['data'])?(is_object($data_arr['data'])?(array)$data_arr['data']:$data_arr['data']):null;
        }
        return $content;
    }

    /**
     * set 设置缓存
     * @param $items 缓存的键
     * @param $val 缓存的值，支持字符串和数组
     * @return bool|int|mixed
     */
    public static function set($items, $val)
    {
        $options=parent::$_options;
        $data['data']=$val;
        $content=parent::encode($data);
        return apc_store($items,$content,$options['expire_time']);
    }

    /**
     * delete 清除缓存
     * @param $items
     * @return mixed|void
     */
    public static function delete($items)
    {
        $content=null;
        if(apc_exists($items))
        {
            apc_delete($items);
        }
    }
    /**
     * is_exist 判定缓存是否存在
     * @param \core\cache\缓存键值 $items 缓存键值
     * @return bool|mixed true,false
     */
    public static function is_exist($items)
    {
        if(apc_exists($items))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}