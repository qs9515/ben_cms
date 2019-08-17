<?php
/**
 *
 * 文件说明: 自定义异常演示
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/31
 * Time: 8:32
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace library\exception;
use core\BaseException;
class UserException extends BaseException
{
    public $code = 500;
    public $msg = '用户名不符合系统要求。';
    public $errorCode = 10001;
}