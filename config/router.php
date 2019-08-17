<?php
/**
 *
 * 文件说明: 路由配置文件
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/2
 * Time: 21:33
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
return array(
    "_default_module"=>"index",
    "_default_controller"=>"index",
    "_default_action"=>"index",
    '_must_router'=>false,//是否必须自定义路由
    '_router_config_file'=>__SITEROOT.'/config/router_data.php',//自定义路由文件路径
);