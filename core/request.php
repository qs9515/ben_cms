<?php
namespace core;
class request{
	private $request=array();
	public function __construct(){
        if(is_array($_GET))
        {
            foreach ($_GET as $k=>$v)
            {
                $this->setParam($k,$v,'get');
            }
        }
        if(is_array($_POST))
        {
            foreach ($_POST as $k=>$v)
            {
                $this->setParam($k,$v,'post');
            }
        }
        if(is_array($_REQUEST))
        {
            foreach ($_REQUEST as $k=>$v)
            {
                $this->setParam($k,$v,'request');
            }
        }
        if(is_array($_REQUEST))
        {
            foreach ($_REQUEST as $k=>$v)
            {
                $this->setParam($k,$v,'request');
            }
        }
		if($_FILES){
			$this->request['_FILES']=$_FILES;
		}
	}
	public function setParam($key,$value,$type=''){
	    //赋值两个数组
        if(in_array(strtolower($type),array('get','post','request')))
        {
            $this->request[$type][$key]=$value;
        }
        $this->request[$key]=$value;
	}
	/**
	 * 兼容zf的写法
	 * @param string $key 键
	 * @param string $value 默认值
	 */
	public function getParam($key='',$value='',$type=''){
	    if(in_array(strtolower($type),array('get','post','request')))
        {
            if(isset($this->request[$type][$key])){
                return $this->request[$type][$key];
            }else{
                return $value;
            }
        }
		else
        {
            if(isset($this->request[$key])){
                return $this->request[$key];
            }else{
                return $value;
            }
        }
	}
	public function getBasePath(){
		return __BASEPATH;
	}
	public static function isAjaxRequest()
    {
        return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['VAR_AJAX_SUBMIT']) || !empty($_GET['VAR_AJAX_SUBMIT'])) ? true : false;
    }
}