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
use application\admin\models\baseModel;
use core\conf;
use core\controller;
use JasonGrimes\Paginator;

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
        $this->view->assign('company_name',conf::get('config.system_company'));
    }
    /**
     * 方法名称:_List
     * 说明:公共列表方法
     * @param $model_name 表对象名称
     * @param $search 检索条件
     * @param $uri 分页跳转地址
     * @param $currentPage 当前页
     * @param $itemsPerPage 每页显示记录数
     * @param array $like_arr like检索条件
     * @param array $exception_arr 独立的检索条件
     */
    protected function _list($model_name,$uri,$search,$orderBy,$like_arr=array(),$exception_arr=array())
    {
        $itemsPerPage = conf::get('system.pager_div_count');
        $currentPage = $this->_request->getParam('page',1);
        $totalItems = baseModel::commCount($model_name,$search,$like_arr,$exception_arr);
        $urlPattern = pager_search($search,$uri);
        $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
        $this->view->assign('pager',pager_ajax($paginator));
        $this->view->assign("search",$search);
        return baseModel::commList($model_name,($currentPage-1)*$itemsPerPage,$itemsPerPage,$search,$orderBy,$like_arr,$exception_arr);
    }

    /**
     * 方法名称:_delete
     * 说明: 公共删除方法
     * @param $model_name 表模型名称
     * @param $uuid 待删除数据主键
     * @param string $verify_model 需要验证是否存在子集的表模型数组
     * @param array $verify_search 验证子集是否存在数据的条件
     * @return mixed
     */
    protected function _delete($model_name,$uuid,$verify_model=array(),$verify_search=array())
    {
        $data['code']='500';
        $data['msg']='删除记录失败！';
        if(!is_array($uuid))
        {
            $uuid=array($uuid);
        }
        if(!empty($uuid))
        {
            $succ=0;
            $error=0;
            foreach ($uuid as $k=>$v)
            {
                $basic=baseModel::commGetDetailById($model_name,$v);
                if($basic!==false)
                {
                    if(!empty($verify_model))
                    {
                        foreach ($verify_model as $x=>$y)
                        {
                            if(!empty($verify_search[$y]))
                            {
                                //构建是否存在子集的条件，参数仅传递字段名称
                                $verify=array();
                                foreach ($verify_search[$y] as $m=>$n)
                                {
                                    $verify[$n]=$v;
                                }
                                //验证是否存在子集
                                if(baseModel::commCount($y,$verify)>0)
                                {
                                    $error++;
                                    //跳过第一层循环
                                    continue 1;
                                }
                            }
                        }
                    }
                    if(baseModel::commDelete($model_name,$v))
                    {
                        $succ++;
                    }
                    else
                    {
                        $error++;
                    }
                }
                else
                {
                    $error++;
                    continue;
                }
            }
            $data['code']='200';
            $data['msg']='删除完成，其中成功【'.$succ.'】条，失败【'.$error.'】条！';
        }
        else
        {
            $data['msg']='参数为空，删除失败！';
        }
        return $data;
    }

    /**
     * 方法名称:_status
     * 说明: 公共安全删除方法
     * @param $model_name 表模型名称
     * @param $uuid 待删除数据主键
     * @return mixed
     */
    protected function _status($model_name,$uuid)
    {
        $data['code']='500';
        $data['msg']='删除记录失败！';
        if(!is_array($uuid))
        {
            $uuid=array($uuid);
        }
        if(!empty($uuid))
        {
            $succ=0;
            $error=0;
            foreach ($uuid as $k=>$v)
            {
                $basic=baseModel::commGetDetailById($model_name,$v);
                if($basic!==false)
                {
                    $new_status=$basic->status==1?2:1;
                    if(baseModel::commEdit($model_name,array('status'=>$new_status),array('id'=>$v)))
                    {
                        $succ++;
                    }
                    else
                    {
                        $error++;
                    }
                }
                else
                {
                    $error++;
                    continue;
                }
            }
            $data['code']='200';
            $data['msg']='删除完成，其中成功【'.$succ.'】条，失败【'.$error.'】条！';
        }
        else
        {
            $data['msg']='参数为空，删除失败！';
        }
        return $data;
    }
}