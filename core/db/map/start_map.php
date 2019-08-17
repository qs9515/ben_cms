<?php
/**
 *
 * 文件说明: 刷表文件入口
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/3
 * Time: 8:43
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\db\map;
use core\conf;
use core\db\map\driver\oci8;
use core\db\map\driver\orm_mysql;

class start_map
{
    public function __construct()
    {
        $databaseConfig=conf::get('database.db');
        foreach ($databaseConfig as $key=>$value)
        {
            if($value['engine']=='mysql'){
                $link=mysqli_connect($value['host'], $value['user'],$value['password'],$value['database']) ;
                if($link) {
                    $mysql = new orm_mysql($link, $value['database'],$key);
                    $mysql->set_modle_path(CORE_PATH.'/db/models/'.$key);
                    $mysql->startMapping();
                }
            }
            if($value['engine']=='oracle')
            {
                //增加条件判定，原来遇到错误就终止了，后面的数据表都没法刷了
                $link=oci_connect($value['user'], $value['password'],$value['host'],$value['charset']);
                if($link)
                {
                    //$path_root=dirname(__FILE__);
                    $oci=new oci8($link,$key);
                    $oci->set_modle_path(CORE_PATH.'/db/models/'.$key);
                    //$oci->set_enumerate_path(__SITEROOT.'temp/enumerates');
                    //$oci->set_code_path(__SITEROOT.'temp/codes');
                    //$oci->set_view_path(__SITEROOT.'temp/views');
                    //$oci->set_sql_path(__SITEROOT.'temp/sql');
                    //$oci->set_xml_path(__SITEROOT.'temp/xml/'.$key);
                    //$oci->set_doc_path(__SITEROOT.'temp/doc/'.$key);
                    $oci->start_mapping();
                }
                else
                {
                    echo '数据库'.$value['host'].'连接失败!!!请检查！！！<br />';
                    continue;
                }
            }
        }
    }
}
