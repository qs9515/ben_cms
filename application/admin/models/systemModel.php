<?php
/**
 *
 * 文件说明: 系统配置管理
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/18
 * Time: 11:00
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\models;
use application\admin\models\baseModel;
use core\conf;
use core\db\models\m\ben_system;

class systemModel extends baseModel
{
    /**
     * 方法名称:getConfDetail
     * 说明: 获取配置信息
     * @return ben_system
     * @throws \Exception
     */
    static function getConfDetail()
    {
        if(parent::commCount('ben_system',array('status'=>'1'))>0)
        {
            $db=new ben_system("m");
            $db->limit(0,1);
            $db->find(true);
        }
        else
        {
            $db=new \stdClass();
            $db->system_name=conf::get('config.system_name');
            $db->system_info=conf::get('config.system_info');
            $db->system_key=conf::get('config.system_key');
        }
        return $db;
    }

    /**
     * 方法名称:confSave
     * 说明: 信息保存
     * @param $data
     * @return bool|mixed
     */
    static function confSave($data)
    {
        $conf=parent::commCount('ben_system',array('status'=>'1'));
        //写入缓存
        $res="<?php\n \$arr=".var_export($data,true).";\n return \$arr;";
        file_put_contents(__SITEROOT.'/config/config.php',$res);
        if($conf)
        {
            //存在则修改
            unset($data['created']);
            return parent::commEdit('ben_system',$data,array('status'=>'1'));
        }
        else
        {
            return parent::commAdd('ben_system',$data);
        }
    }
}