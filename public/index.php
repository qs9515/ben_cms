<?php
/**
 *
 * 文件说明: 入口文件
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/2
 * Time: 20:41
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace web_dir;
ini_set("session.cookie_httponly", 'on');
session_start();
set_time_limit(0);
ini_set("display_errors",'on');
header("Content-type: text/html;charset=utf-8");
//发送xss安全头
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("X-Content-Security-Policy: default-src 'self'");
date_default_timezone_set('Asia/Shanghai');
//定义包含文件绝对路径
define('__SITEROOT',dirname(dirname(__FILE__)));
//定义url访问的相对路径
define('BASE_PATH','/');
//定义基础框架核心文件路径
define('CORE_PATH',__SITEROOT.'/core');
//定义控制器文件路径
define('APP',__SITEROOT.'/application');
define('APP_SPACE','\\application');
//composer自动加载类
include __SITEROOT.'/vendor/autoload.php';
//加载框架基础公共函数库
include CORE_PATH.'/common/functions.php';
//加载框架核心文件
include CORE_PATH.'/router.php';
spl_autoload_register("\\core\\router::load");
//定义系统是否运行于调试模式
define('DEBUG',true);
//定义系统是否开启审计日志
define('APP_LOGS',true);
//注册错误执行类
\core\error::register();
//执行框架
\core\router::dispatch();