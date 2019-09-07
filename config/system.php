<?php
/**
 *
 * 文件说明: 用于定义系统变量，如站点名称等
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/3
 * Time: 9:45
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
$sys_config=array();
//是否开启数据库调试模式
$sys_config['db_debug']=false;
//定义session唯一索引值
$sys_config['session_id']=md5('benCmsSeoSystem');
//定义软件版本
$sys_config['version']='1.0.'.date('Ymd');
//定义日志存储路径
$sys_config['log_file']=__SITEROOT.'/_cache/logs';
//定义缓存模式
$sys_config['cache_conf']=array('type'=>'file','expire_time'=>3600*24);
//定义SQL缓存时间
$sys_config['cache_sql_time']=5;
//定义分页显示数量
$sys_config['pager_div_count']=8;
//定义是否启用RSA加密
$sys_config['login_rsa'] = true;
//定义RAS加密证书有效日期
$sys_config['expire_time']= 3600*24*5;
//定义上传文件存储路径
$sys_config['upload_dir']=__SITEROOT.'/public/upload';
//定义加密私钥存储路径
$sys_config['private_key_dir']=__SITEROOT.'/config';
//定义日志级别
/**
 * 日志级别包含：至上往下，级别逐步升高
 * LogLevel::EMERGENCY;
 * LogLevel::ALERT;
 * LogLevel::CRITICAL;
 * LogLevel::ERROR;
 * LogLevel::WARNING;
 * LogLevel::NOTICE;
 * LogLevel::INFO;
 * LogLevel::DEBUG;
 */
$sys_config['log_level']=Psr\Log\LogLevel::WARNING;
return $sys_config;