<?php
namespace core\db\map\driver;
class orm_mysql{
    public $database;
    public $model_path;//存储生成表对象的路径
    public $view_path;//存储生成表现层代码片段的路径
    public $enumerate_path;//存储生成的枚举数据的路径
    public $code_path;//存储生成控制器中用到的代码片段的存储路径
    public $view_type;//分用smarty,lamp与标准的
    public $sql_path;//存储生成的sql代码的路径
    public $link;
    private $db_key;//数据库索引文件夹，用于namespace;
    /**
     * 类初始化 指定 连接ORACLE的resource
     *
     * @param resourse $link
     */
    public function __construct($link,$database,$key){
        if(is_object($link)){
            $this->link=$link;
            $this->database=$database;
            $this->db_key=$key;
        }else{
            throw new Exception('请先建立数据库连接!');
        }
    }
    public function startMapping(){
        //$host=$this->_request->getParam('host',1);

        //$this->model_path=__SITE_ROOT."temp/models";
        //$this->model_path=__SITE_ROOT."model";
        //require_once(__SITE_ROOT."config/model_config.php");

        //$this->model_path=__MODEL_PATH;
        //$this->sql_path=__MODEL_PATH;

        /*		$this->view_path=__SITE_ROOT."temp/views";
        $this->enumerate_path=__SITE_ROOT."temp/enumerates";
        $this->code_path=__SITE_ROOT."temp/codes";
        $this->sql_path=__SITE_ROOT."temp/sql";		*/
        //$this->database=$db_instance->get('database');
        //$this->view_type="lamp_html";

        define('CR',"\r\n");
        define('BLANK',"    ");
        mysqli_query($this->link,"set names utf8");
        $sql = "SHOW TABLES FROM ".$this->database;
        //echo $sql;

        $result = mysqli_query($this->link,$sql);
        $i=1;
        while ($row = mysqli_fetch_array($result)) {
            $table_name=strtolower($row['0']);
            if(strpos($table_name,'$')!==false)
            {
                continue;
            }
            $this->create_model($table_name);
            //以下生成辅助功能的生成器暂时先关闭
            //$this->create_view($row['0']);
            //$this->create_enumerate($row['0']);
            //$this->create_code($row['0']);
            //echo "table: {$row['0']} ORM-mapping finished<br>";
            echo $i++." 表: {$table_name} 生成成功<br>";
        }
        //清除缓存文件
        //echo "清除编译文件<br />";
        //$this->deleteDir($this->view->compile_dir);
        //echo "清除缓存文件<br />";
        //$this->deleteDir($this->view->cache_dir);
        //$this->generate_sql_action();
    }

    /**
     *
     * 自动生成代码片段
     * 分成两类
     * 1.用于显示的代码片段$this->view->name = $user->name; //类型|text
     * 2.用于存储的代码片段$user->name = $this->_request->getParam('name')
     * @param unknown_type $table
     */

    private function create_model($table){
        $table=strtolower($table);
        $result = mysqli_query($this->link,"show create table $table");
        if (!$result)
        {
            echo "show create table $table"."<br />";
            echo '代码有错: ' . mysqli_error();
            exit();
        }
        $class_file=$this->model_path.'/'.$table.".php";
        //echo $class_file."<br />";
        $fp=fopen($class_file,"w+");
        if(!$fp)
        {
            echo "创建文件出错： ".$table;
            exit();
        }
        $line="<?php".CR;
        $row = mysqli_fetch_array($result);
        preg_match('/COMMENT=[\'\"](.*)[\'\"]/',$row['1'], $table_name);
        //var_dump($row['1']);
        //var_dump($table_name);
        //exit;
        $line=$line."namespace core\db\models\\".$this->db_key.";".CR;
        $line.="use core\db\driver;".CR;
        $line=$line."/**".CR;
        $line=$line." * 注释:".(isset($table_name[0])?$table_name[0]:'').CR;
        $line=$line." *".CR;
        $line=$line." * @var ".CR;
        $line=$line." */".CR;


        $line=$line."class ".$table." extends driver\dao_mysql".CR;
        $line=$line."{".CR;
        $line=$line.BLANK.'public $__table = '."'".$table."';". CR;
        $result = mysqli_query($this->link,"SHOW FULL COLUMNS FROM $table");
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                $row['Field']=strtolower($row['Field']);
                if(substr($row['Field'],0,1)=='_'){
                    echo "字段名在本框架下不能以_开头,请修改字段名";
                    exit();
                }
                $line=$line.BLANK."/**".CR;
                $line=$line.BLANK." * 注释:".$row['Comment'].CR;
                $line=$line.BLANK." *".CR;
                $line=$line.BLANK." * @var ".$row['Type'].CR;
                $line=$line.BLANK." */".CR;
                $line=$line.BLANK."public $".$row['Field'].";".CR;
                $line=$line.BLANK."public $".'_'.$row['Field']."_type='".strtolower($row['Type'])."';\r\n";
            }
        }

        $line=$line."}".CR;
        fwrite($fp,$line);
        fclose($fp);
    }


    public function set_enumerate_path($path){
        //$root=$_SERVER['DOCUMENT_ROOT'];
        //$path=$root."/".$path;
        if(is_dir($path)){
            $this->enumerate_path=$path;
        }
        else{
            echo "enums path error";
            exit();
        }
    }
    public function set_code_path($path){
        //$root=$_SERVER['DOCUMENT_ROOT'];
        //$path=$root."/".$path;
        if(is_dir($path)){
            $this->code_path=$path;
        }else {
            echo "code path error";
            exit();
        }
    }
    public function set_modle_path($path){
        //$root=$_SERVER['DOCUMENT_ROOT'];
        //$path=$root."/".$path;
        if($dir=$this->check_dir($path))
        {
            $this->model_path=$dir;
        }
        else
        {
            mkdir($path,0777,true);
            $this->model_path=$this->check_dir($path);
        }
    }
    public function set_sql_path($path){
        //$root=$_SERVER['DOCUMENT_ROOT'];
        //$path=$root."/".$path;
        if(is_dir($path)){
            $this->model_path=$path;
        }else{
            echo "sql path error";
            exit();
        }
    }
    public function set_view_path($path){
        //$root=$_SERVER['DOCUMENT_ROOT'];
        //$path=$root."/".$path;
        if(is_dir($path)){
            $this->view_path=$path;
        }else{
            echo "views path error";
            exit();
        }
    }
    /**
     * 检查是$dir是否是目录
     *
     * @param string $dir
     * @return bool
     */
    private function check_dir($dir){

        if(!empty($dir)){

            if(is_dir($dir)){

                return 	 str_replace('\\','/',$dir);
            }else{

                return false;
            }
        }else{
            return false;
        }

    }
}