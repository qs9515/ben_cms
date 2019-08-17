<?php
/**
 *
 * 文件说明: 文件缓存
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/26
 * Time: 11:44
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\cache\driver;
use core\cache\cache;

class file extends cache
{
    /**
     * get 获取缓存
     * @param $items 缓存的唯一键值
     * @return array|mixed|null
     */
    public static function get($items)
    {
        $options=parent::$_options;
        $cache_file=$options['store_path'].'/'.md5($items);
        $content=null;
        if(file_exists($cache_file))
        {
            $cache_time=time()-filemtime($cache_file);
            if($options['expire_time']==0 || $cache_time<$options['expire_time'])
            {
                //缓存文件未过期
                $data_arr=(array)parent::decode(file_get_contents($cache_file));
                $content=isset($data_arr['data'])?(is_object($data_arr['data'])?(array)$data_arr['data']:$data_arr['data']):null;
            }
            //缓存过期，清除缓存文件
            if($cache_time>$options['expire_time'] && $options['expire_time']!=0)
            {
                self::delete($items);
            }
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
        $cache_file=$options['store_path'].'/'.md5($items);
        $data['data']=$val;
        $content=parent::encode($data);
        return file_put_contents($cache_file,$content);
    }

    /**
     * delete 清除缓存
     * @param $items
     * @return mixed|void
     */
    public static function delete($items)
    {
        $options=parent::$_options;
        $cache_file=$options['store_path'].'/'.md5($items);
        $content=null;
        if(file_exists($cache_file))
        {
            unlink($cache_file);
        }
    }

    /**
     * is_exist 判定缓存是否存在
     * @param \core\cache\缓存键值 $items 缓存键值
     * @return bool|mixed true,false
     */
    public static function is_exist($items)
    {
        $options=parent::$_options;
        $cache_file=$options['store_path'].'/'.md5($items);
        $cache_time=time()-filemtime($cache_file);
        if($cache_time<$options['expire_time'] || $options['expire_time']==0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}