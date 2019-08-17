<?php
/**
 *
 * 文件说明: 视图配置
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/2
 * Time: 21:34
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
return array(
    "template_dir"=>__SITEROOT."/views",
    "compile_dir"=>__SITEROOT."/_cache/views/templates_c",
    "left_delimiter"=>"<!--{",
    "right_delimiter"=> "}-->",
    "cache_dir"=>__SITEROOT."/_cache/views/caches",
    "error_reporting"=>E_ALL & ~E_NOTICE,
);