<?php
/**
 *
 * 文件说明: 数据模型测试
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/3
 * Time: 9:15
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\index\models;
use core\db\models\master\individual_core;
use core\db\models\master\model_t2;
use core\db\models\master\model_test;
use core\db\models\master\test;
use core\db\models\master\test2;

class testModel
{
    public function get_one($id)
    {
        $core=new individual_core('master');
        //$core->selectAdd("name");
        $core->limit(0,10);
        $core->find();
        while ($core->fetch())
        {
            var_dump($core->name);
        }
        exit;
        $org=new model_test('master');
        $m2=new model_t2('master');
        $org->joinAdd('left',$org,$m2,'uuid','base_uuid');
        $org->selectAdd("model_test.uuid as muuid,model_t2.uuid as m2uuid");
        $org->limit(0,10);
        $org->find();
        while ($org->fetch())
        {
            var_dump($org->jianjie);
        }
        $org->free_statement();
        var_dump($org);
        //$org=new model_test('master');
        //var_dump($org);
    }
    public function set_one($data)
    {
        $org=new model_test('master');
        foreach ($data as $k=>$v)
        {
            $org->$k=$v;
        }
        //$org->debug(5);
        if($org->insert())
        {
            $m2=new model_t2('master');
            $m2->uuid=uniqid('M2_',true);
            $m2->base_uuid=$data['uuid'];
            $m2->sys_date=date('Y-m-d H:i:s');
            $m2->content='从表的内容！';
            $m2->insert();
        }
    }
    public function update_one($data,$where,$where_arr)
    {
        $org=new model_test('master');
        foreach ($data as $k=>$v)
        {
            $org->$k=$v;
        }
        $org->whereAdd($where,$where_arr);
        debug_code($org->update());
    }
    public function del_one($where,$where_arr=array())
    {
        $org=new model_test('master');
        $org->whereAdd($where,$where_arr);
        debug_code($org->delete());
    }
    public function get_count()
    {
        $org=new model_test('master');
        $m2=new model_t2('master');
        $org->joinAdd('left',$org,$m2,'uuid','base_uuid');
        $org->debug(5);
        debug_code($org->count("distinct uuid"));
    }
    public function get_query_one()
    {
        $org=new model_t2('master');
        $org->query("select jianjie from model_test inner join model_t2 on model_test.uuid=model_t2.base_uuid");
        while ($org->fetch())
        {
            var_dump($org->jianjie);
        }
    }
    public function oracle_test()
    {
        $test=new individual_core('master');
        $test->whereAdd("status=?",array('1'));
        $test->limit(0,50);
        $test->find();
        while ($test->fetch())
        {
            $test->uuid;
        }
    }
    static function join_date_test()
    {
        /*
        $test=new test("master");
        $data['id']='1';
        $data['age']='28';
        $data['name']='测试日期';
        $data['created']=date('Y-m-d H:i:s');
        $test->create($data);
        $test2=new test2("master");
        $data=array();
        $data['uuid']=uniqid('T_',true);
        $data['tuuid']='1';
        $data['content']='这个就是测试date转义问题的。';
        $data['creatd']=date('Y-m-d H:i:s');
        $test2->debug(5);
        $test2->create($data);
        */
        //开始测试查询
        $test=new test("master");
        $test2=new test2("master");
        $test->joinAdd('inner',$test,$test2,'id','tuuid');
        $test->selectAdd("test.name as tname");
        //$test->debug(5);
        $test->find();
        $data=array();
        $i=0;
        while ($test->fetch())
        {
            $data[$i]['id']=$test->id;
            $data[$i]['name']=$test->tname;
            $data[$i]['content']=$test2->content;
            $data[$i]['created']=$test->created;
            $data[$i]['tcreated']=$test2->creatd;
            $i++;
        }
        var_dump($data);

    }


    static function test_sql_error()
    {
        $test=new test("master");
        $test->aaa='aaa';
        //$test->debug(5);
        $test->whereAdd("uuid='1212'");
        $test->update();
    }
}