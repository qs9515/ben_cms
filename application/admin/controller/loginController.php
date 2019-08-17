<?php
/**
 *
 * 文件说明: 登录页
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/17
 * Time: 21:26
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\controller;
use application\admin\models\loginModel;
use core\controller;
use core\conf;
use library\code;
class loginController extends controller
{
    public function init()
    {
        $this->view->assign('current_year',date('Y'));
        $this->view->assign('base_path',BASE_PATH);
        //系统名称
        $this->view->assign('system_name',conf::get('config.system_name'));
    }

    /**
     * 方法名称:loginAction
     * 说明: 登录页展示
     */
    public function loginAction()
    {
        $this->view->assign("phone",C("user_token"));
        $this->view->display("admin/manage/login.html");
    }

    /**
     * 方法名称:codeAction
     * 说明: 输出验证码
     * @throws \Exception
     */
    public function codeAction()
    {
        $code=new code(4,80);
        $code->draw('png');
        //写入session
        S('login_code',$code->code);
    }

    /**
     * 方法名称:saveAction
     * 说明: 登录验证
     */
    public function saveAction()
    {
        //获取参数
        $phone=$this->_request->getParam('phone');
        $password=$this->_request->getParam('password');
        $code=trim(strtolower($this->_request->getParam('code')));
        //返回页面信息
        $msg['code']=0;
        $msg['message']='登录失败！';
        //记录日志
        $logs_data['username']=$phone;
        $logs_data['status']=2;
        if(!$phone)
        {
            $msg['message']='登录失败，账号不能为空！';
            //记录登录日志
            loginModel::loginLogAdd($logs_data,$msg);
            json($msg);
            exit;
        }
        if(!$password)
        {
            $msg['message']='登录失败，密码不能为空！';
            //记录登录日志
            loginModel::loginLogAdd($logs_data,$msg);
            json($msg);
            exit;
        }
        if(!$code)
        {
            $msg['message']='登录失败，验证码不能为空！';
            //记录登录日志
            loginModel::loginLogAdd($logs_data,$msg);
            json($msg);
            exit;
        }
        if($code!=S('login_code'))
        {
            $msg['message']='登录失败，验证码错误！';
            //记录登录日志
            loginModel::loginLogAdd($logs_data,$msg);
            json($msg);
            exit;
        }
        if(true)
        {
            //登录成功
            C('user_token',$phone);
            S('user_token',$phone);
            $msg['code']=200;
            $msg['message']='登录成功！';
            $logs_data['status']=1;
            //记录登录日志
            loginModel::loginLogAdd($logs_data,$msg);
            json($msg);
        }
        else
        {
            $msg['message']='登录失败，账号密码不匹配！';
            //记录登录日志
            loginModel::loginLogAdd($logs_data,$msg);
            json($msg);
        }
    }

    /**
     * 方法名称:logoutAction
     * 说明: 退出登录
     */
    public function logoutAction()
    {
        session_destroy();
        $this->forward(BASE_PATH.'admin/login/login/');
    }
}