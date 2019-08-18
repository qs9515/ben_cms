<?php
/**
 *
 * 文件说明: 表单验证类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/18
 * Time: 11:16
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace library\exception;
use core\BaseException;
class ValidateException extends BaseException
{
    public $code = 500;
    public $msg = '输入不能为空';
    public $errorCode = 20001;
}