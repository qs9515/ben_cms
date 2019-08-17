<?php
/**
 *
 * 文件说明: 文件缓存类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/26
 * Time: 11:05
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\cache;
class cache implements cache_interface
{
    //初始配置数组
    protected static $_options=array();
    //存储驱动方法
    private static $_class='';

    /**
     * init 缓存初始化
     * @param array $options
     * @throws \Exception
     */
    public static function init($options = array())
    {
        try
        {
            if(empty($options))
            {
                //缓存类型
                self::$_options['type']='file';
                //超时时间，秒，0为永不过期
                self::$_options['expire_time']=0;
                //缓存文件路径，默认为系统临时文件
                self::$_options['store_path']=sys_get_temp_dir();
                //如果是redis，配置redis的服务器路径
                self::$_options['cache_server']='tcp://127.0.0.1';
            }
            else
            {
                //缓存类型
                self::$_options['type']=isset($options['type'])?$options['type']:'file';
                //超时时间，秒，0为永不过期
                self::$_options['expire_time']=isset($options['expire_time'])?$options['expire_time']:0;
                //缓存文件路径，默认为系统临时文件
                self::$_options['store_path']=isset($options['store_path'])?$options['store_path']:sys_get_temp_dir();
                //如果是redis，配置redis的服务器路径
                self::$_options['cache_server']=isset($options['cache_server'])?$options['cache_server']:'tcp://127.0.0.1';
            }
            self::$_class='core\cache\driver\\'.self::$_options['type'];
        }
        catch (PDOException $e)
        {
            throw new \Exception($e,'500');
        }
    }

    /**
     * set 设置缓存
     * @param $items 缓存键值
     * @param $val 缓存内容，字符串或者数组
     * @return mixed
     */
    public static function set($items,$val)
    {
        try{
            return call_user_func_array(array(self::$_class, "set"), array($items, $val));
        }catch (Exception $exception)
        {
            throw new Exception($exception,500);
        }
    }

    /**
     * get 获取缓存内容
     * @param $items 缓存键值
     * @return mixed
     */
    public static function get($items){
        try{
            return call_user_func_array(array(self::$_class, "get"), array($items));
        }catch (Exception $exception)
        {
            throw new Exception($exception,500);
        }
    }

    /**
     * delete 删除缓存
     * @param $items 缓存键值
     * @return mixed
     */
    public static function delete($items)
    {
        try{
            return call_user_func_array(array(self::$_class, "delete"), array($items));
        }catch (Exception $exception)
        {
            throw new Exception($exception,500);
        }
    }

    /**
     * is_exist 判断缓存是否存在
     * @param $items 缓存键值
     * @return mixed
     */
    public static function is_exist($items)
    {
        try{
            return call_user_func_array(array(self::$_class, "is_exist"), array($items));
        }catch (Exception $exception)
        {
            throw new Exception($exception,500);
        }
    }

    /**
     * decode 解码缓存内容
     * @param $contents 待解码的内容
     * @return mixed
     */
    protected static function decode($contents)
    {
        $contents=json_decode(base64_decode($contents),true);
        return $contents;
    }

    /**
     * encode 编码缓存的内容
     * @param $contents 待编码的内容
     * @return string
     */
    protected static function encode($contents)
    {
        $contents=base64_encode(json_encode($contents,JSON_UNESCAPED_UNICODE));
        return $contents;
    }
}