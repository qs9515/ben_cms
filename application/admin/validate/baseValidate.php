<?php
/**
 *
 * 文件说明: 基础验证
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/18
 * Time: 11:12
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\validate;
use library\exception\ValidateException;
class baseValidate
{
    /**
     * 方法名称:isMust
     * 说明: 验证字符串不能为空
     * @param $params_name
     * @param $request
     * @param string $msg
     * @return mixed
     * @throws ValidateException
     */
    static function isMust($params_name,$request,$msg='')
    {
        $str=$request->getParam($params_name);
        if($str=='')
        {
            $exception=new ValidateException(['msg'=>$msg]);
            throw $exception;
        }
        return $str;
    }

    /**
     * 方法名称:defaultValue
     * 说明: 设置默认值
     * @param $params_name
     * @param $request
     * @param int $val
     */
    static function defaultStatus($params_name,$request,$val=1)
    {
        $str=$request->getParam($params_name);
        $str=($str==1?1:2);
        return $str;
    }
}