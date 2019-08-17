<?php
/**
 *
 * 文件说明:redis缓存驱动
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/27
 * Time: 12:00
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\cache\driver;
use Predis\Client;
class redis extends \core\cache\cache
{
    private $_instance=null;
    public function connect()
    {
        if($this->_instance==null)
        {
            $option=self::$_options;
            $this->_instance=new Client($option['server']);
        }
        else
        {
            return $this->_instance;
        }
    }
    /**
     * get 获取缓存
     * @param $items 缓存的唯一键值
     * @return array|mixed|null
     */
    public static function get($items)
    {
        $client=self::connect();
        $content=null;
        if($client->exists($items))
        {
            $data_arr=(array)parent::decode($client->get($items));
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
        $client=self::connect();
        return $client->setex($items,$options['expire_time'],$content);
    }

    /**
     * delete 清除缓存
     * @param $items
     * @return mixed|void
     */
    public static function delete($items)
    {
        $content=null;
        $client=self::connect();
        if($client->exists($items))
        {
            $client->del($items);
        }
    }
    /**
     * is_exist 判定缓存是否存在
     * @param \core\cache\缓存键值 $items 缓存键值
     * @return bool|mixed true,false
     */
    public static function is_exist($items)
    {
        $client=self::connect();
        if($client->exists($items))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}