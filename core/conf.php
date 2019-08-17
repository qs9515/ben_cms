<?php
/**
 *
 * 文件说明: 框架核心配置类文件
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/2/24
 * Time: 15:58
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core;
class conf
{
    static public $conf=array();

    /**
     * get
     *
     * 获取配置文件
     *
     * @param $name 缓存名称 文件名.字段名的形式
     * @return mixed
     * @throws \Exception
     */
    static public function get($name)
    {
        $tmp=explode('.',$name);
        $file=isset($tmp[0])?$tmp[0]:'';
        $name=isset($tmp[1])?$tmp[1]:'';
        if($file=='')
        {
            throw new \Exception('参数错误，请给定文件名.配置字段名的形式！');
        }
        $file=$file.'.php';
        if(isset(self::$conf[$file]))
        {
            if($name!='')
            {
                return self::$conf[$file][$name];
            }
            else
            {
                return self::$conf[$file];
            }
        }
        else
        {
            $path=dirname(CORE_PATH).'/config/'.$file;
            if(is_file($path))
            {
                $conf= include $path;
                if($name!='')
                {
                    if(isset($conf[$name]))
                    {
                        self::$conf[$file]=$conf;
                        return $conf[$name];
                    }
                    else
                    {
                        throw new \Exception('配置文件【'.$file.'】中没有指定的配置项：【'.$name.'】！');
                    }
                }
                else
                {
                    return $conf;
                }
            }
            else
            {
                throw new \Exception('找不到配置文件：【'.$file.'】！');
            }
        }
    }
}