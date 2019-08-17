<?php
/**
 *
 * 文件说明: 数据库配置文件，需要注意索引要用于namespace，不能使用语言自身的关键字，如default等。
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/3
 * Time: 8:25
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
//配置mysql测试
$databaseConfig['db']['m']['charset'] = 'UTF8';
$databaseConfig['db']['m']['engine'] = 'mysql';
$databaseConfig['db']['m']['connectType'] = 1;
$databaseConfig['db']['m']['host'] = 'localhost';
$databaseConfig['db']['m']['database'] = 'ben_cms';
$databaseConfig['db']['m']['user'] = 'root';
$databaseConfig['db']['m']['password'] = '123456';
return $databaseConfig;