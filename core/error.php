<?php
/**
 *
 * 文件说明: 错误处理类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/21
 * Time: 14:18
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core;
use Whoops\Handler\JsonResponseHandler;
class error
{
    /**
     * 注册异常处理
     * @return void
     */
    public static function register()
    {
        if(DEBUG===true)
        {
            //开启调试模式时，显示系统错误信息
            $whoops=new \Whoops\Run();
            $page_title='<<<系统运行错误：';
            $option=new \Whoops\Handler\PrettyPageHandler();
            $option->setPageTitle($page_title);
            $whoops->prependHandler($option);
            if (\core\request::isAjaxRequest())
            {
                //设置处理ajax报错的信息
                $whoops->prependHandler(new JsonResponseHandler());
            }
            $whoops->register();
        }
        else
        {
            error_reporting(E_ALL);
            set_exception_handler([__CLASS__, 'appException']);
        }
    }
    public static function appException($e)
    {
        self::getExceptionHandler()->render($e);
    }
    public static function getExceptionHandler()
    {
        static $handle;
        if (!$handle) {
            // 异常处理handle
            $class = 'handle';
            if ($class && class_exists($class) && is_subclass_of($class, "\\core\\handle")) {
                $handle = new $class;
            } else {
                $handle = new handle();
            }
        }
        return $handle;
    }
}