<?php
/**
 * 路由器
 * 如果没有指定模、控制器与动作，则默认的模块名/控制器名/动作名　可在配置文件model_config.php中指定
 * 为什么一样要模仿zf呢，也可以设计成index/index/index
 * 其所对应的目录为 application/default  对应的控制器文件为 index_controller.php 或 indexController 
 * 对应的对应为 indexAction
 * 设计思路：
 * 2009-12-22
 * 为了更好提高控制器的灵活性，把模块名，控制器名与动作名都设计为可自定义式
 * 按http://www.mymvc.cn/moduleName/controllerName/actionName/pareKey1/pareValue1.... 的方式来请求。
 * 如http://www.mymvc.cn/admin/user/add
 * 而对应的控制器文件的名称为 controllerName_controller.php或是controllerNameController.php
 * 对应的控制器类的名称为 controllerName_controller或是moduleName_controllerNameController
 * 
 * 
 * 在处理时要
 * 把moduleName映射成为实际的路径,这个映射表可在配置文件中定义，也可在主程序中定义，然后通过
 * 定义如下功能来处理模块设计
 * 
 * setModule($moduleArray)来现实。也可通过getModule([modeuleName])来获取映射表的所有项目或是一项。
 * 
 * 
 * $moduleArray=array('user'=>'application/user','news'=>'application/news','default'=>'application/default')

 * controllerName映射成为实际类名与文件名
 * 
 * controllerName的后缀名要根据用户在配置文件中的设置来处理，
 * 可根据配置修改。如后缀名可定义为_controller，则对应的类文件与类名分别为
 * user_controller.php user_controller (或是　admin_user_controller:保持与zf一致)
 * 如果后缀名定义为Controller,则对应的类文件与类名分别为
 * userController.php userController　(或是　admin_userController:保持与zf一致)
 * actionName要映射成为实际成员函数名
 * 如在配置文件中后缀名可定义为_action，则对应的动作名为
 * add_action
 * 如在配置文件中后缀名可定义为Action，则对应的动作名为
 * addAction
 * 希望在寒假可完成这项改进工作 --上面的工作已完成
 * 另，setModule等设计为与ZF的的接口名一样，这样方便移植
 * 
 * 详细说明见
 * 
 *
 */
namespace core;
use \core\BaseException;
class router{
    //自动加载类的缓存
    static public $class_map=array();
	/**
	 * 参数封装
	 * @var request
	 */
	private $_request;
	private $controller;
	/**
	 * 根
	 *
	 * @var char
	 */
	private $root;
	/**
	 * uri
	 *
	 * @var char
	 */
	private $uri;
	public static $module_name;
	public static $controller_name;
	public static $action_name;
    public static $halts = false;
    public static $routes = array();
    public static $methods = array();
    public static $callbacks = array();
    public static $maps = array();
    public static $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );
    public static $error_callback;
    public static function __callstatic($method, $params) {
        if ($method == 'map') {
            $maps = array_map('strtoupper', $params[0]);
            $uri = strpos($params[1], '/') === 0 ? $params[1] : '/' . $params[1];
            $callback = $params[2];
        } else {
            $maps = null;
            $uri = strpos($params[0], '/') === 0 ? $params[0] : '/' . $params[0];
            $callback = $params[1];
        }
        array_push(self::$maps, $maps);
        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, $callback);
    }
    /**
     * Defines callback if route is not found
     */
    public static function error($callback) {
        self::$error_callback = $callback;
    }
    public static function haltOnMatch($flag = true) {
        self::$halts = $flag;
    }
    public function init(){
		//设定根路径
		if(defined('__SITEROOT')){
			$this->root=__SITEROOT;
		}else{
            throw new \Exception('没有定义根文件路径，程序执行错误。!');
		}
		//获取uri相关参数
		$this->uri=$_SERVER['REQUEST_URI'];
		//转到router动作
		$this->routerAction();
	}
	public function routerAction(){
		$this->splitUri();
	}
	/**
	 * 对uri进行分解 分解成为 模/控制器/动作/参数
	 *
	 * @param unknown_type $uri
	 */
	public function splitUri(){
        //根据配置加载默认模块、控制器及动作
        self::$module_name=conf::get('router._default_module');
        self::$controller_name=conf::get('router._default_controller');
        self::$action_name=conf::get('router._default_action');
        if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!='/')
        {
            $path=$_SERVER['REQUEST_URI'];
            $path_arr=explode('/',trim($path,'/'));
            //设置模块
            if(isset($path_arr[0]) && $path_arr[0]!='' && strpos($path_arr[0],'?')===false)
            {
                self::$module_name=$path_arr[0];
            }
            //设置控制器
            if(isset($path_arr[1]) && $path_arr[1]!='')
            {
                //判定controller是否以？结束
                $end_postion=strpos($path_arr[1],'?');
                if($end_postion!==false)
                {
                    self::$controller_name=substr($path_arr[1],0,$end_postion);
                }
                else
                {
                    self::$controller_name=$path_arr[1];
                }
            }
            //设置action
            if(isset($path_arr[2]) && $path_arr[2]!='')
            {
                //判定action是否以？结束
                $end_postion=strpos($path_arr[2],'?');
                if($end_postion!==false)
                {
                    if($end_postion!==0)
                    {
                        self::$action_name=substr($path_arr[2],0,$end_postion);
                    }
                }
                else
                {
                    self::$action_name=$path_arr[2];
                }
            }
            //获取$_GET参数
            $count=count($path_arr);
            //处理URL中状态为：/index/index/?ac=**&bc=**的URL
            if($count>3)
            {
                $i=3;
                while ($i<$count+2)
                {
                    if(isset($path_arr[$i]) && isset($path_arr[$i+1]))
                    {
                        //移除通过GET传递的符号？
                        $end_postion=strpos($path_arr[$i+1],'?');
                        if(isset($path_arr[$i+1]) && $end_postion===false)
                        {
                            $_GET[$path_arr[$i]]=$path_arr[$i+1];
                        }
                        else
                        {
                            $_GET[$path_arr[$i]]=substr($path_arr[$i+1],0,$end_postion);
                        }
                    }
                    $i=$i+2;
                }
            }
        }
	}
	/**
	 * 分发
	 *
	 */
	static public function dispatch(){
	    $must_router=conf::get('router._must_router');
        $router_data_file=conf::get('router._router_config_file');
        if($must_router && is_file($router_data_file))
        {
            include $router_data_file;
        }
	    if($must_router)
        {
            try{
                //必须走路由
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $method = $_SERVER['REQUEST_METHOD'];
                $searches = array_keys(static::$patterns);
                $replaces = array_values(static::$patterns);
                $found_route = false;
                self::$routes = preg_replace('/\/+/', '/', self::$routes);
                // Check if route is defined without regex
                if (in_array($uri, self::$routes)) {
                    $route_pos = array_keys(self::$routes, $uri);
                    foreach ($route_pos as $route) {
                        // Using an ANY option to match both GET and POST requests
                        if (self::$methods[$route] == $method || self::$methods[$route] == 'ANY' || (!empty(self::$maps[$route]) && in_array($method, self::$maps[$route]))) {
                            $found_route = true;
                            // If route is not an object
                            if (!is_object(self::$callbacks[$route])) {
                                // Grab all parts based on a / separator
                                $parts = explode('/',self::$callbacks[$route]);
                                // Collect the last index of the array
                                $last = end($parts);
                                // Grab the controller name and method call
                                $segments = explode('\\',$last);
                                self::$module_name=isset($segments[0])?$segments[0]:conf::get('router._default_module');
                                self::$controller_name=isset($segments[1])?$segments[1]:conf::get('router._default_controller');
                                self::$action_name=isset($segments[2])?$segments[2]:conf::get('router._default_action');
                                // Instanitate controller
                                $controller_class=APP_SPACE.'\\'.self::$module_name.'\\controller\\'.self::$controller_name.'Controller';
                                $controller = new $controller_class();
                                //记录路由日志
                                self::logger();
                                // Fix multi parameters
                                if (!method_exists($controller, self::$action_name.'Action')) {
                                    throw new \Exception('找不到控制器文件：【'.$controller.'】对应的方法【'.self::$action_name.'Action】!','404');
                                } else {
                                    $action=self::$action_name.'Action';
                                    if(method_exists($controller,'init'))
                                    {
                                        $controller->init();
                                    }
                                    $controller->$action();
                                }
                                if (self::$halts) return;
                            } else {
                                // Call closure
                                call_user_func(self::$callbacks[$route]);
                                if (self::$halts) return;
                            }
                        }
                    }
                } else {
                    // Check if defined with regex
                    $pos = 0;
                    foreach (self::$routes as $route) {
                        if (strpos($route, ':') !== false) {
                            $route = str_replace($searches, $replaces, $route);
                        }
                        if (preg_match('#^' . $route . '$#', $uri, $matched)) {
                            if (self::$methods[$pos] == $method || self::$methods[$pos] == 'ANY' || (!empty(self::$maps[$pos]) && in_array($method, self::$maps[$pos]))) {
                                $found_route = true;
                                // Remove $matched[0] as [1] is the first parameter.
                                array_shift($matched);
                                if (!is_object(self::$callbacks[$pos])) {
                                    // Grab all parts based on a / separator
                                    $parts = explode('/',self::$callbacks[$pos]);
                                    // Collect the last index of the array
                                    $last = end($parts);
                                    // Grab the controller name and method call
                                    $segments = explode('\\',$last);
                                    self::$module_name=isset($segments[0])?$segments[0]:conf::get('router._default_module');
                                    self::$controller_name=isset($segments[1])?$segments[1]:conf::get('router._default_controller');
                                    self::$action_name=isset($segments[2])?$segments[2]:conf::get('router._default_action');
                                    // Instanitate controller
                                    $controller_class=APP_SPACE.'\\'.self::$module_name.'\\controller\\'.self::$controller_name.'Controller';
                                    $controller = new $controller_class();
                                    //记录路由日志
                                    self::logger();
                                    // Fix multi parameters
                                    if (!method_exists($controller, self::$action_name.'Action')) {
                                        throw new \Exception('找不到控制器文件：【'.$controller.'】对应的方法【'.self::$action_name.'Action】!','404');
                                    } else {
                                        if(method_exists($controller,'init'))
                                        {
                                            $controller->init();
                                        }
                                        call_user_func_array(array($controller, self::$action_name.'Action'), $matched);
                                    }
                                    if (self::$halts) return;
                                } else {
                                    call_user_func_array(self::$callbacks[$pos], $matched);
                                    if (self::$halts) return;
                                }
                            }
                        }
                        $pos++;
                    }
                }
                // Run the error callback if the route was not found
                if ($found_route == false) {
                    if (!self::$error_callback) {
                        self::$error_callback = function() {
                            //header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
                            //echo '404';
                            throw new \Exception($_SERVER['SERVER_PROTOCOL']." 404 Not Found",'404');
                        };
                    } else {
                        if (is_string(self::$error_callback)) {
                            self::get($_SERVER['REQUEST_URI'], self::$error_callback);
                            self::$error_callback = null;
                            self::dispatch();
                            return ;
                        }
                    }
                    call_user_func(self::$error_callback);
                }
            }catch (\ERROR $error)
            {
                throw new \Exception($error);
            }
        }
	    else
        {
            try{
                //传统Pathinfo模式
                $router=new router();
                $router->init();
                $class_file=APP.'/'.self::$module_name.'/controller/'.self::$controller_name.'Controller.php';
                $controller_class=APP_SPACE.'\\'.self::$module_name.'\\controller\\'.self::$controller_name.'Controller';
                $action=self::$action_name.'Action';
                //记录路由日志
                self::logger();
                if(is_file($class_file))
                {
                    include $class_file;
                    $ctrl=new $controller_class();
                    if(method_exists($ctrl,'init'))
                    {
                        $ctrl->init();
                    }
                    if(method_exists($ctrl,$action))
                    {
                        $ctrl->$action();
                    }
                    else
                    {
                        throw new \core\BaseException(['msg'=>'指定的方法：【'.$action.'】不存在!','code'=>'500']);
                    }
                }
                else
                {
                    throw new \Exception('找不到控制器文件：【'.$class_file.'】!','500');
                }
            }catch (\ERROR $error)
            {
                throw new \Exception($error);
            }
        }
	}
    /**
     * load
     *
     * 自动加载类
     *
     * @param $class_name 类名，通常可能为\core\router的格式，注意包含命名空间
     * @return bool
     */
    static public function load($class_name)
    {
        //处理类名中的|符号，通常class_name可能为\core\router的样式
        $class_name=str_replace('\\','/',$class_name);
        //判定是否已经缓存了类文件
        if(isset(self::$class_map[$class_name]))
        {
            return true;
        }
        else
        {
            $class_file=__SITEROOT.'/'.$class_name.'.php';
            //验证类文件是否已存在
            if(is_file($class_file))
            {
                include $class_file;
                self::$class_map[$class_name]=$class_name;
            }
            else
            {
                return false;
            }
        }
    }
	/**
	 * 控制器跳转
	 *
	 * @param string $action
	 */
	public static function redirect($url){
		//echo $url;
		header("location:$url");
	}

    /**
     * 方法名称:logger
     * 说明: 记录日志
     * @throws \Exception
     */
	private static function logger()
    {
        if (APP_LOGS==true)
        {
            $data['module']=self::$module_name.'/'.self::$controller_name.'/'.self::$action_name;
            $data['source']=$_SERVER['REQUEST_URI'];
            $data['ip']=get_client_ip();
            $data['created']=$data['updated']=date('Y-m-d H:i:s');
            $data['ua']=$_SERVER['HTTP_USER_AGENT'];
            $data['user_id']=S('user_token');
            $table_name=conf::get('system.logs_table');
            $obj_name="core\\db\\models\\m\\".$table_name;
            $db=new $obj_name("m");
            $db->create($data);
        }
    }
}