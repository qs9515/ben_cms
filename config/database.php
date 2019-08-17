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
$databaseConfig['db'][1]['charset'] = 'UTF8';
$databaseConfig['db'][1]['engine'] = 'mysql';
$databaseConfig['db'][1]['connectType'] = 1;
$databaseConfig['db'][1]['host'] = 'localhost';
$databaseConfig['db'][1]['database'] = 'bencms';
$databaseConfig['db'][1]['user'] = 'root';
$databaseConfig['db'][1]['password'] = '123456';
return $databaseConfig;