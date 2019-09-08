<?php
/**
 *
 * 文件说明: 后台首页
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/17
 * Time: 20:58
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\controller;
use application\admin\controller\baseController;
use application\admin\models\baseModel;
use application\admin\models\loginModel;

class indexController extends baseController
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }
    public function indexAction()
    {
        //后台菜单
        //图标
        $nav[0]['icon']='home';
        //菜单名称
        $nav[0]['nav_name']='首页';
        //URL地址
        $nav[0]['url']=BASE_PATH.'admin/index/main/';

        $nav[1]['icon']='cogs';
        $nav[1]['nav_name']='系统维护管理';
        $nav[1]['url']='';
        //子元素
        $nav[1]['son'][0]['icon']='cog';
        $nav[1]['son'][0]['nav_name']='系统设置管理';
        $nav[1]['son'][0]['url']=BASE_PATH.'admin/system/conf/';
        $nav[1]['son'][1]['icon']='cog';
        $nav[1]['son'][1]['nav_name']='登陆日志管理';
        $nav[1]['son'][1]['url']=BASE_PATH.'admin/system/loginLog/';
        $nav[1]['son'][2]['icon']='cog';
        $nav[1]['son'][2]['nav_name']='访问日志管理';
        $nav[1]['son'][2]['url']=BASE_PATH.'admin/system/eventLog/';

        $nav[2]['icon']='archive';
        $nav[2]['nav_name']='文章管理';
        $nav[2]['url']='';
        //子元素
        $nav[2]['son'][0]['icon']='bookmark';
        $nav[2]['son'][0]['nav_name']='分类管理';
        $nav[2]['son'][0]['url']=BASE_PATH.'admin/article/sortList/';
        $nav[2]['son'][1]['icon']='book';
        $nav[2]['son'][1]['nav_name']='文章管理';
        $nav[2]['son'][1]['url']=BASE_PATH.'admin/article/artList/';
        $nav[2]['son'][2]['icon']='book';
        $nav[2]['son'][2]['nav_name']='tag管理';
        $nav[2]['son'][2]['url']=BASE_PATH.'admin/article/tagList/';

        $nav[3]['icon']='database';
        $nav[3]['nav_name']='其他管理';
        $nav[3]['url']='';
        //子元素
        $nav[3]['son'][0]['icon']='cubes';
        $nav[3]['son'][0]['nav_name']='友情连接管理';
        $nav[3]['son'][0]['url']=BASE_PATH.'admin/other/linkList/';
        $nav[3]['son'][1]['icon']='cubes';
        $nav[3]['son'][1]['nav_name']='模板管理';
        $nav[3]['son'][1]['url']=BASE_PATH.'admin/other/templateList/';

        $this->view->assign('nav',$nav);
        $this->view->display("admin/manage/index.html");
    }
    public function mainAction()
    {
        $this->view->assign('login_count',baseModel::commCount('ben_login_logs',array()));
        $this->view->assign('article_count',baseModel::commCount('ben_article_base',array()));
        $this->view->assign('logs_count',baseModel::commCount('ben_views_logs',array()));
        $this->view->assign('tags_count',baseModel::commCount('ben_tags',array()));
        $this->view->assign("login_logs",loginModel::commList('ben_login_logs',0,8,array(),"updated desc"));
        $this->view->display("admin/manage/main.html");
    }
}