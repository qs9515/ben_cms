<?php
/**
 *
 * 文件说明: 公共模型类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/17
 * Time: 22:29
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\models;
class baseModel
{
    /**
     * 方法名称:setWhere
     * 说明: 构造where查询条件
     * @param $db
     * @param array $search 查询条件数组
     * @param array $cols_like 需要使用Like的字段数组
     * @return mixed
     */
    static function setWhere($db,$search=array(),$cols_like=array())
    {
        if(!empty($search))
        {
            foreach ($search as $k=>$v)
            {
                if($k=='keyword' && $v && !empty($cols_like))
                {
                    $where_str='';
                    $where_arr=array();
                    foreach ($cols_like as $m=>$n)
                    {
                        $where_str.="$n like ? or ";
                        $where_arr[]=$v.'%';
                    }
                    $where_str=rtrim($where_str,' or ');
                    $db->whereAdd($where_str,$where_arr);
                }
                else if($v)
                {
                    $db->whereAdd("$k=?",array($v));
                }
            }
        }
        return $db;
    }

    /**
     * 方法名称:toArray
     * 说明: 将查询结果集转换成数组
     * @param $db
     */
    static function toArray($db)
    {
        $data=array();
        if (is_object($db))
        {
            $items=get_object_vars($db);
            if(!empty($items))
            {
                foreach ($items as $k=>$v)
                {
                    if(stripos('_',$k)!==0)
                    {
                        $data[$k]=$v;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 方法名称:formatDate
     * 说明: 格式化日期
     * @param $data
     * @param string $format
     * @return false|string
     */
    static function formatDate($data,$format='Y-m-d')
    {
        return $data?date($format,strtotime($data)):$data;
    }
    /**
     * 方法名称:setObjName
     * 说明: 设置表对象命名空间
     * @param $obj_name
     * @return mixed
     */
    static protected function setObjName($obj_name)
    {
        $obj_name="core\\db\\models\\m\\".$obj_name;
        $db=new $obj_name("m");
        return $db;
    }
    /**
     * 方法名称:commCount
     * 说明:获取数据记录数
     * @param $obj_name 表对象名称
     * @param array $search 检索数组
     * @param array $like_arr like条件字段名
     * @param array $exception_arr 例外的检索条件数组，每一维为一个条件表达式
     */
    static function commCount($obj_name,$search=array(),$like_arr=array(),$exception_arr=array())
    {
        $db=self::setObjName($obj_name);
        self::setWhere($db,$search,$like_arr);
        if(!empty($exception_arr))
        {
            foreach ($exception_arr as $k=>$v)
            {
                //$v是条件表达式
                $db->whereAdd($v);
            }
        }
        return $db->count();
    }

    /**
     * 方法名称:commList
     * 说明: 数据列表
     * @param $obj_name 表对象名称
     * @param int $start 开始记录数
     * @param int $limit 页面显示记录数
     * @param array $search 查询条件
     * @param $orderby 排序表达式
     * @param array $like_arr like条件字段名
     * @param array $exception_arr 例外的检索条件数组，每一维为一个条件表达式
     * @return array
     */
    static function commList($obj_name,$start=0,$limit=8,$search=array(),$orderby,$like_arr=array(),$exception_arr=array())
    {
        $db=self::setObjName($obj_name);
        self::setWhere($db,$search,$like_arr);
        if(!empty($exception_arr))
        {
            foreach ($exception_arr as $k=>$v)
            {
                //$v是条件表达式
                $db->whereAdd($v);
            }
        }
        $db->limit($start,$limit);
        $db->orderby($orderby);
        $db->find();
        $data=array();
        $i=0;
        while ($db->fetch())
        {
            $data[$i]=self::toArray($db);
            $i++;
        }
        return $data;
    }
    /**
     * 方法名称:commGetDetailById
     * 说明: 获取表对象详细
     * @param $obj_name 表对象名称
     * @param $id
     * @return bool
     */
    static function commGetDetailById($obj_name,$id)
    {
        $data=false;
        if($id)
        {
            $db=self::setObjName($obj_name);
            $db->whereAdd("id='$id'");
            $db->find(true);
            return $db;
        }
        return $data;
    }

    /**
     * 方法名称:commAdd
     * 说明: 新增信息
     * @param $obj_name 表对象名称
     * @param $data 待写入数据数组
     * @return mixed
     */
    static function commAdd($obj_name,$data)
    {
        $db=self::setObjName($obj_name);
        $db->create($data);
        return $db->last_insert_id();
    }

    /**
     * 方法名称:commEdit
     * 说明: 修改信息
     * @param $obj_name
     * @param $data
     * @param array $where
     * @return bool
     */
    static function commEdit($obj_name,$data,$where=array())
    {
        $res=false;
        if(!empty($where) && !empty($data))
        {
            $db=self::setObjName($obj_name);
            foreach ($data as $k=>$v)
            {
                if($v!='')
                {
                    $db->$k=$v;
                }
            }
            foreach ($where as $k=>$v)
            {
                $db->whereAdd("$k=?",array($v));
            }
            $res=$db->update();
        }
        return $res;
    }
    /**
     * 方法名称:commDelete
     * 说明: 删除信息
     * @param $obj_name
     * @param $id
     * @return bool
     */
    static function commDelete($obj_name,$id)
    {
        $res=false;
        if($id)
        {
            $db=self::setObjName($obj_name);
            $db->whereAdd("id=?",array($id));
            $res=$db->delete();
        }
        return $res;
    }
}