<?php
/**
 *
 * 文件说明: 公共函数库文件
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/21
 * Time: 14:35
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
function json($data,$code='200')
{
    if(is_array($data))
    {
        header('content-type:application/json;charset=utf-8');
        http_response($code);
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
        exit;
    }
}
//设置HTTP状态吗
function http_response($num) {
    $http = array(
        100 => "HTTP/1.1 100 Continue",
        101 => "HTTP/1.1 101 Switching Protocols",
        200 => "HTTP/1.1 200 OK",
        201 => "HTTP/1.1 201 Created",
        202 => "HTTP/1.1 202 Accepted",
        203 => "HTTP/1.1 203 Non-Authoritative Information",
        204 => "HTTP/1.1 204 No Content",
        205 => "HTTP/1.1 205 Reset Content",
        206 => "HTTP/1.1 206 Partial Content",
        300 => "HTTP/1.1 300 Multiple Choices",
        301 => "HTTP/1.1 301 Moved Permanently",
        302 => "HTTP/1.1 302 Found",
        303 => "HTTP/1.1 303 See Other",
        304 => "HTTP/1.1 304 Not Modified",
        305 => "HTTP/1.1 305 Use Proxy",
        307 => "HTTP/1.1 307 Temporary Redirect",
        400 => "HTTP/1.1 400 Bad Request",
        401 => "HTTP/1.1 401 Unauthorized",
        402 => "HTTP/1.1 402 Payment Required",
        403 => "HTTP/1.1 403 Forbidden",
        404 => "HTTP/1.1 404 Not Found",
        405 => "HTTP/1.1 405 Method Not Allowed",
        406 => "HTTP/1.1 406 Not Acceptable",
        407 => "HTTP/1.1 407 Proxy Authentication Required",
        408 => "HTTP/1.1 408 Request Time-out",
        409 => "HTTP/1.1 409 Conflict",
        410 => "HTTP/1.1 410 Gone",
        411 => "HTTP/1.1 411 Length Required",
        412 => "HTTP/1.1 412 Precondition Failed",
        413 => "HTTP/1.1 413 Request Entity Too Large",
        414 => "HTTP/1.1 414 Request-URI Too Large",
        415 => "HTTP/1.1 415 Unsupported Media Type",
        416 => "HTTP/1.1 416 Requested range not satisfiable",
        417 => "HTTP/1.1 417 Expectation Failed",
        500 => "HTTP/1.1 500 Internal Server Error",
        501 => "HTTP/1.1 501 Not Implemented",
        502 => "HTTP/1.1 502 Bad Gateway",
        503 => "HTTP/1.1 503 Service Unavailable",
        504 => "HTTP/1.1 504 Gateway Time-out"
    );
    header($http[$num]);
}

function debug_code($params)
{
    throw new \Exception(var_export($params,true));
}

/**
 * 函数名称:getClientIp
 * 说明:获取客户端ip地址
 * @return mixed|string
 */
function get_client_ip()
{
    $ip = 'unknown';
    $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
        // 使用透明代理、欺骗性代理的情况
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
        // 没有代理、使用普通匿名代理和高匿代理的情况
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // 处理多层代理的情况
    if (strpos($ip, ',') !== false) {
        // 输出第一个IP
        $ip = reset(explode(',', $ip));
    }
    return $ip;
}

/**
 * S session集成函数
 * @param $key 索引值
 * @param string $val 需要给session的值，为null时，表示获取session值
 * @return bool|mixed|null 设置session值时，总是返回true，获取值时，失败返回null，成功返回session对应的值
 * @throws Exception
 */
function S($key,$val=null)
{
    //判断session是否已经开启
    if (strnatcmp(phpversion(),'5.4.0') >= 0)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    else
    {
        if(session_id() == '') {
            session_start();
        }
    }
    //设置session前缀，避免本地session冲突
    $session_id=\core\conf::get('system.session_id');
    $session_id=$session_id?$session_id:md5('wsxxh.com');
    if($val!==null)
    {
        //设置session的值
        $_SESSION[$session_id][$key]=$val;
        return true;
    }
    else
    {
        if(isset($_SESSION[$session_id][$key]))
        {
            return $_SESSION[$session_id][$key];
        }
        else
        {
            return null;
        }
    }
}

/**
 * C cookie集成函数
 * @param $key 索引值 需要给cookie的值，为null时，表示获取cookie值
 * @param null $val 设置cookie值时，总是返回true，获取值时，失败返回null，成功返回cookie对应的值
 * @return bool|mixed|null
 * @throws Exception
 */
function C($key,$val=null)
{
    //设置session前缀，避免本地session冲突
    $session_id=\core\conf::get('system.session_id');
    $session_id=$session_id?$session_id:md5('wsxxh.com');
    if($val!==null)
    {
        $_COOKIE[$session_id][$key]=$val;
        return true;
    }
    else
    {
        if(isset($_COOKIE[$session_id][$key]))
        {
            return $_COOKIE[$session_id][$key];
        }
        else
        {
            return null;
        }
    }
}

/**
 * pager_search 构造分页时的条件
 * @param $search
 * @param string $urlPattern
 * @return string
 */
function pager_search($search,$urlPattern='')
{
    if(!empty($search))
    {
        foreach ($search as $k=>$v)
        {
            $urlPattern.=$k.'/'.$v.'/';
        }
    }
    return $urlPattern;
}

/**
 * pager_ajax ajax分页函数
 * @param $pager
 * @return string
 */
function pager_ajax($pager)
{
    $html='<div class="row">
        <div class="col-xs-12">
            <span>总共 '.$pager->getTotalItems().' 条记录</span>
            <ul class="pagination pull-right">';
    if($pager->getPrevUrl())
    {
        $html.='<li><a href="###" aria-label="Previous" onclick="ajax_page(\''.$pager->getPrevUrl().'\')"><span aria-hidden="true">&laquo;</span></a></li>';
    }
    else
    {
        $html.='<li class="disabled"><a href="###" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    }
    foreach ($pager->getPages() as $page)
    {
        if(isset($page['url']) && $page['url'])
        {
            if(isset($page['isCurrent']) && $page['isCurrent'])
            {
                $html.='<li class="active"><a href="###">'.$page['num'].'</a></li>';
            }
            else
            {
                $html.='<li><a href="###" onclick="ajax_page(\''.$page['url'].'\')">'.$page['num'].'</a></li>';
            }
        }
    }
    if($pager->getNextUrl())
    {
        $html.='<li><a href="###" aria-label="Next" onclick="ajax_page(\''.$pager->getNextUrl().'\')"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    else
    {
        $html.='<li class="disabled" aria-label="Next"><a href="###"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    $html.='
                            </ul>
                        </div>
                    </div>';
    return $html;
}

/**
 * 方法名称:pager
 * 说明: 普通分页
 * @param $pager
 * @return string
 */
function pager($pager)
{
    $html='<div class="row">
        <div class="col-xs-12">
            <span>总共 '.$pager->getTotalItems().' 条记录</span>
            <ul class="pagination pull-right">';
    if($pager->getPrevUrl())
    {
        $html.='<li><a href="'.$pager->getPrevUrl().'"><span aria-hidden="true">&laquo;</span></a></li>';
    }
    else
    {
        $html.='<li class="disabled"><a href="###"><span aria-hidden="true">&laquo;</span></a></li>';
    }
    foreach ($pager->getPages() as $page)
    {
        if(isset($page['url']) && $page['url'])
        {
            if(isset($page['isCurrent']) && $page['isCurrent'])
            {
                $html.='<li class="active"><a href="###">'.$page['num'].'</a></li>';
            }
            else
            {
                $html.='<li><a href="'.$page['url'].'">'.$page['num'].'</a></li>';
            }
        }
    }
    if($pager->getNextUrl())
    {
        $html.='<li><a href="'.$pager->getNextUrl().'"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    else
    {
        $html.='<li class="disabled"><a href="###"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    $html.='
                            </ul>
                        </div>
                    </div>';
    return $html;
}