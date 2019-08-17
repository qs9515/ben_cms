<?php
/**
 *
 * 文件说明: 异常处理类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/21
 * Time: 11:41
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core;
use Exception;
class handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(Exception $e)
    {
        $title='系统发生错误';
        if ($e instanceof \core\BaseException)
        {
            //如果是自定义异常，则控制http状态码，不需要记录日志
            //因为这些通常是因为客户端传递参数错误或者是用户请求造成的异常
            //不应当记录日志
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
            $title='系统消息提示';
        }
        else {
            // 如果是服务器未处理的异常，将http状态码设置为500，并记录日志
            $this->code = 500;
            $this->msg = '对不起，系统发生了一些异常，请联系管理员处理！';
            $this->errorCode = 999;
            $this->recordErrorLog($e);
        }
        $result['error'] = [
            'message'  => $this->msg,
            'error_code' => $this->errorCode,
            'file' => $_SERVER['REQUEST_URI']
        ];
        if(\core\request::isAjaxRequest())
        {
            return json($result, $this->code);
        }
        else
        {
            return $this->show_error($result['error'], $this->code,$title);
        }
    }

    /*
     * 将异常写入日志
     */
    private function recordErrorLog(Exception $e)
    {
        $trace = explode("\n", $e->getTraceAsString());
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        //array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();
        for ($i = 0; $i < $length; $i++)
        {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }
        $err_msg = implode("\n\t", $result);
        $loger=\core\log::init();
        $loger->error('Msg:'.$e->getMessage().';Debug:'.$err_msg);
    }

    /**
     * show_error
     *
     * 展示html页面的错误报告
     *
     * @param $res
     * @param $code
     */
    private function show_error($res,$code,$title)
    {
        $html='
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>错误提示页！</title>
<style>
html,body{height:100vh}
html:before,html:after,body:before,body:after{content:\'\';background:linear-gradient(#203075,#233581);border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)}
html:before,body:before{background:linear-gradient(#233581,#203075)}
html{background:linear-gradient(#203075,#233581);overflow:hidden}
html:before{height:105vmax;width:105vmax;z-index:-4}
html:after{height:80vmax;width:80vmax;z-index:-3}
body{display:flex;justify-content:center;align-items:center;color:#FFF;font-family:\'Varela Round\',Sans-serif;text-shadow:0 30px 10px rgba(0,0,0,0.15)}
body:before{height:60vmax;width:60vmax;z-index:-2}
body:after{height:40vmax;width:40vmax;z-index:-1}
.main{text-align:center;z-index:5}
p{font-size:18px;margin-top:0}
h1{font-size:145px;margin:0}
button{background:linear-gradient(#EC5DC1,#D61A6F);padding:0 12px;border:none;border-radius:20px;box-shadow:0 30px 15px rgba(0,0,0,0.15);outline:none;color:#FFF;font:400 16px/2.5 Nunito,\'Varela Round\',Sans-serif;text-transform:uppercase;cursor:pointer}
.bubble{background:linear-gradient(#EC5DC1,#D61A6F);border-radius:50%;box-shadow:0 30px 15px rgba(0,0,0,0.15);position:absolute}
.bubble:before,.bubble:after{content:\'\';background:linear-gradient(#EC5DC1,#D61A6F);border-radius:50%;box-shadow:0 30px 15px rgba(0,0,0,0.15);position:absolute}
.bubble:nth-child(1){top:15vh;left:15vw;height:22vmin;width:22vmin}
.bubble:nth-child(1):before{width:13vmin;height:13vmin;bottom:-25vh;right:-10vmin}
.bubble:nth-child(2){top:20vh;left:38vw;height:10vmin;width:10vmin}
.bubble:nth-child(2):before{width:5vmin;height:5vmin;bottom:-10vh;left:-8vmin}
.bubble:nth-child(3){top:12vh;right:30vw;height:13vmin;width:13vmin}
.bubble:nth-child(3):before{width:3vmin;height:3vmin;bottom:-15vh;left:-18vmin;z-index:6}
.bubble:nth-child(4){top:25vh;right:18vw;height:18vmin;width:18vmin}
.bubble:nth-child(4):before{width:7vmin;height:7vmin;bottom:-10vmin;left:-15vmin}
.bubble:nth-child(5){top:60vh;right:18vw;height:28vmin;width:28vmin}
.bubble:nth-child(5):before{width:10vmin;height:10vmin;bottom:5vmin;left:-25vmin}</style>
</head>
<body>

<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="main">
	<h2>'.$title.'</h2>
	<p>【错误代码：'.$res['error_code'].'】 '.$res['message'].'</p>
</div>

</body>
</html>';
        exit($html);
    }
}