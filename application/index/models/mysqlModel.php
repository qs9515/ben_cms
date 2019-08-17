<?php
/**
 *
 * 文件说明: 测试mysql的使用
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/22
 * Time: 17:44
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\index\models;
use core\db\models;
use core\db\models\mysql\model_t2;
use core\db\models\mysql\model_test;

class mysqlModel
{
    public function dt()
    {
        $table='core\\db\\models\\mysql\\model_t2';
        $obj=new $table("mysql");
        echo($obj->count("distinct uuid"));
    }
    public function get_one($id)
    {
        $org=new model_test('mysql');
        $org->whereAdd("uuid is not null")->whereAdd("uuid>0")->orderby("uuid desc")->limit(0,5)->debug(5)->find();
        while ($org->fetch())
        {
            var_dump($org->jianjie);
        }
        exit;
        $m2=new model_t2('mysql');
        $org->joinAdd('left',$org,$m2,'uuid','base_uuid');
        $org->selectAdd("model_test.uuid as muuid,model_t2.uuid as m2uuid");
        //$org->debug(5);
        $org->limit(0,10);
        $org->find();
        while ($org->fetch())
        {
            var_dump($org->jianjie);
        }
        $org->free_statement();
        //var_dump($org);
        //$org=new model_test('master');
        //var_dump($org);
    }
    public function set_one($data)
    {
        /*$org=new model_test('mysql');
        foreach ($data as $k=>$v)
        {
            $org->$k=$v;
        }
        //$org->debug(5);
        if($org->insert())
        {
            $m2=new model_t2('mysql');
            $m2->uuid=uniqid('M2_',true);
            $m2->base_uuid=$data['uuid'];
            $m2->sys_date=date('Y-m-d H:i:s');
            $m2->content='从表的内容！';
            $m2->insert();
        }*/
        //传递数组
        $org=new model_test('mysql');
        debug_code($org->create($data));
    }
    public function update_one($data,$where,$where_arr)
    {
        $org=new model_test('mysql');
        foreach ($data as $k=>$v)
        {
            $org->$k=$v;
        }
        $org->whereAdd($where,$where_arr);
        debug_code($org->update());
    }
    public function del_one($where,$where_arr=array())
    {
        $org=new model_test('mysql');
        $org->whereAdd($where,$where_arr);
        debug_code($org->delete());
    }
    public function get_count()
    {
        $org=new model_test('mysql');
        //$m2=new model_t2('mysql');
        //$org->joinAdd('left',$org,$m2,'uuid','base_uuid');
        //$org->debug(5);
        echo($org->count("distinct uuid"));
    }
    public function get_query_one()
    {
        $org=new model_t2('mysql');
        $org->query("select jianjie from model_test inner join model_t2 on model_test.uuid=model_t2.base_uuid");
        $i=0;
        while ($org->fetch())
        {
            $i++;
        }
    }
    public function get_cache_res()
    {
        $org=new model_t2('mysql');
        $org->whereAdd("1=1")->cache()->find();
        $i=0;
        while ($org->fetch())
        {
            var_dump($org->content);
            $i++;
        }
    }
}