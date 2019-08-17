<?php
//引入请求对象类
//require_once(__SITEROOT.'library/request.php');//本句保留
//request类本来在requst.php中，在20110221优化工作中，移到了本文件中。luowei主要就是为少包含文件，提高速度
//require_once(__SITEROOT."model/acl.php");
namespace core;
class controller{
	private $_router;
	/**
	 * 模块名下面的属性只能为public，否则动态实例化时，无法将请求参数注入
	 */
	private $module_name;
	private $controller_name;
	private $action_name;
	/**
	 * Enter description here...
	 *
	 * @var request
	 */
	public $_request;
	//兼容zf的表现层实现
	/**
	 * Enter description here...
	 *
	 * @var mySmarty
	 */
	public $view;
	function __construct(){
		$this->module_name=router::$module_name;
		$this->controller_name=router::$controller_name;
		$this->action_name=router::$action_name;
		$this->_request=new request();//传入的requset对象
        //引入视图
        $this->view=new \Smarty();
        $this->view->setTemplateDir(conf::get('view.template_dir'));
        $this->view->setCacheDir(conf::get('view.cache_dir'));
        $this->view->setCompileDir(conf::get('view.compile_dir'));
        $this->view->setErrorReporting(conf::get('view.error_reporting'));
        $this->view->setLeftDelimiter(conf::get('view.left_delimiter'));
        $this->view->setRightDelimiter(conf::get('view.right_delimiter'));
	}
	public function _getParameter($_key){
		return $_REQUEST[$_key];
	}
	/**
	 * 控制器跳转
	 *
	 * @param string $action
	 */
	public function redirect($url,$type='php'){
		//$url=urlencode($url);
        if($type=='php') {
            header("location:$url");
            exit;
        }
        if($type=='js'){
            echo "<script>
window.location.href='$url';  
</script>";
            exit();
        }

	}
	public function forward($url){
		header("location:$url");
        exit;
	}
	public function getModuleName(){
		return $this->module_name;
	}
	public function getControllerName(){
		return $this->controller_name;
	}
	public function getActionName(){
		return $this->action_name;
	}
	public function getModuleControllerAction($separator='/'){
		return $this->module_name.$separator.$this->controller_name.$separator.$this->action_name;
	}

	/**
	 * show_message
	 *
	 * 我好笨
	 *
	 * 信息提示函数
	 *
	 *
	 * @param int $status 状态 1成功 2 失败 3不存在 4 非法
	 * @param string $msg 提示信息文本
	 * @param array $urls 数组，第一个作为默认跳转地址
	 * @param bool $auto 默认为假，不自动跳转，设置为数字时，为自动跳转，并且数字就是倒数时间
	 * @param string $target 默认跳转右边窗口，可定义跳转到顶窗口等
	 * @param string $return 返回的连接地址，默认使用javascript的返回
	 */
    public function show_message($msg='',$urls=array(),$status=1,$auto=false,$template_dir='',$target='',$return='javascript:history.go(-1)')
    {
        $template_dir=is_dir($template_dir)?$template_dir:__SITEROOT.'/views/admin/comm/';
        $this->view->assign('status',$status);
        $this->view->assign('msg',$msg);
        $this->view->assign('urls',$urls);
        $this->view->assign('auto',$auto);
        $this->view->assign('target',$target);
        $this->view->assign('return',$return);
        $this->view->setTemplateDir($template_dir);
        $this->view->display('message.html');
        exit();
    }

}