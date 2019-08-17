<?php
/**
 *
 * 文件说明: 后台操作基类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/17
 * Time: 21:15
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\controller;
use core\conf;
use core\controller;
class baseController extends controller
{
    public function init()
    {
        //进行权限验证
        $user_token=S('user_token');
        if($user_token===null)
        {
            //未登陆
            $this->forward(BASE_PATH.'admin/login/login/');
        }
        $this->view->assign('user_token',$user_token);
        $this->view->assign('current_year',date('Y'));
        $this->view->assign('base_path',BASE_PATH);
        //系统版本
        $this->view->assign('version',conf::get('system.version'));
        //系统名称
        $this->view->assign('system_name',conf::get('config.system_name'));
        //厂商名称
        $this->view->assign('company_name',conf::get('system.company_name'));
    }
}