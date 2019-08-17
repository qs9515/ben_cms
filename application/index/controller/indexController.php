<?php
/**
 *
 * 文件说明:
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/2
 * Time: 21:29
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\index\controller;
use application\index\models\mysqlModel;
use application\index\models\testModel;
use core\cache\cache;
use core\controller;
use core\log;
use core\request;
use library\exception\UserException;

class indexController extends controller
{
    public function init()
    {
        //echo 'init';
        //$logger=log::init();
        //$logger->error('日志信息');
        //$logger->log(Psr\Log\LogLevel::WARNING,'ddd');
    }
    public function indexAction()
    {
        testModel::test_sql_error();
        exit;
        testModel::join_date_test();
        exit;
        $test=new mysqlModel();
        $test->dt();
        exit;
        $id=$this->_request->getParam('id');
        $model=new testModel();
        $model->get_one('a');
        exit;
        //$model->get_one('a');

        $model->get_query_one();
        $data['uuid']=uniqid('T_',true);
        $data['name']='cccc';
        $data['birth']=date('Y-m-d H:i:s');
        $data['sui']=rand(1,100);
        $data['jianjie']='这就是clob的简介'.$data['sui'];
        debug_code($model->set_one($data));
        //新增
        //$model->set_one($data);
        //更新
        //$model->update_one($data,"uuid=?",array('T_5c94494cf1a950.99506976'));
        //删除
        //$model->del_one("uuid='T_5c94494cf1a950.99506976'");
        //$model->del_one("uuid=?",array('T_5c94494cf1a950.99506976'));
        //获取行数
        //$model->get_count();
        //echo 'index';
        //缓存应用(文件)
        //缓存初始化
        cache::init();
        //设置缓存
        cache::set('aaa','ddsdfadf');
        cache::set('ccc',array('a'=>date('Y-m-d H:i:s'),'b'=>time()));
        //获取缓存
        var_dump(cache::get('ccc'));
        //删除缓存
        cache::delete('ccc');
        var_dump(cache::get('ccc'));
        //缓存应用（apc)
        cache::init(array('type'=>'apc','expire_time'=>10));
        //设置缓存
        cache::set('aaa','ddsdfadf');
        cache::set('ccc',array('a'=>date('Y-m-d H:i:s'),'b'=>time()));
        //获取缓存
        var_dump(cache::get('ccc'));
        //删除缓存
        cache::delete('ccc');
        var_dump(cache::get('ccc'));
        //缓存应用（redis)
        cache::init(array('type'=>'redis','expire_time'=>3,'cache_server'=>'tcp://192.168.200.4'));
        //设置缓存
        //cache::set('aaa','ddsdfadf');
        cache::set('eee',array('a'=>date('Y-m-d H:i:s'),'b'=>time()));
        //获取缓存
        var_dump(cache::get('eee'));
        sleep(5);
        var_dump(cache::get('eee'));
        //删除缓存
        cache::delete('eee');
        cache::is_exist('aaa');
        $this->view->assign('body','哈哈哈哈哈232323');
        $this->view->display('index.html');
    }
    public function mysqlAction()
    {
        $model=new mysqlModel();
        //$model->get_one('a');
        //$model->get_query_one();
        $data['uuid']=uniqid('T_',true);
        $data['name']='cccc';
        $data['birth']=date('Y-m-d H:i:s');
        $data['sui']=rand(1,100);
        $data['jianjie']='这就是clob的简介'.$data['sui'];
        $model->set_one($data);
        //新增
        //$model->set_one($data);
        //更新
        //$model->update_one($data,"uuid=?",array('T_5c987eac76e416.98577277'));
        //删除
        //$model->del_one("uuid='T_5c987ed86326c2.21672005'");
        //$model->del_one("uuid=?",array('T_5c94494cf1a950.99506976'));
        //获取行数
        //$model->get_count();
    }
    public function mysql_cacheAction()
    {
        $model=new mysqlModel();
        $model->get_cache_res();
    }
    public function exceptionAction()
    {
        $user=$this->_request->getParam('aa');
        if($user=='')
        {
            throw new UserException(['code'=>500,'msg'=>'测试用户自定义异常！']);
        }
    }

    /**
     * postAction
     *
     * 测试restful自定义路由
     *
     */
    public function postAction()
    {
        echo 'post方法';
    }

    /**
     * viewAction
     *
     * 测试参数传递
     *
     * @param $id
     */
    public function viewAction($id)
    {
        var_dump($id);
    }

    /**
     * deleteAction
     *
     * 测试delete方式
     *
     */
    public function deleteAction($id)
    {
        var_dump($id);
    }
}