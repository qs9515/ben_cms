<?php
/**
 *
 * 文件说明: 登陆的模型
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/17
 * Time: 21:33
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\models;
use core\db\models\m\ben_login_logs;
class loginModel extends baseModel
{
    /**
     * 方法名称:loginLogAdd
     * 说明: 新增日志信息
     * @param $data
     * @param $msg
     * @return int|mixed
     * @throws \Exception
     */
    static function loginLogAdd($data,$msg)
    {
        $data['source']=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
        $data['ua']=isset($_SERVER['HTTP_USER_AGENT'])?strtolower($_SERVER['HTTP_USER_AGENT']):'';
        $data['ip']=get_client_ip();
        $data['updated']=$data['created']=date('Y-m-d H:i:s');
        $data['res']=isset($msg['message'])?$msg['message']:'';
        $logs=new ben_login_logs('m');
        return $logs->create($data);
    }
}