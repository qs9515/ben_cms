<?php
/**
 * oracle的ORM生成器
 *
 */
namespace core\db\map\driver;
class oci8{
    private $database;
    private $model_path;//存储生成表对象的路径
    private $view_path;//存储生成表现层代码片段的路径
    private $enumerate_path;//数组变量的存储位置
    private $code_path;//存储生成控制器中用到的代码片段的存储路径
    private $sql_path;//存储生成的sql代码的路径(此代码的主要作用重建表)
    private $xml_path;//存储生成的xml代码，用于接口文档
    private $doc_path;//存储生成的word文档，用于接口文档
    private $view_type;//分用smarty与不用
    private $link=null;//oci与PHP连接的resource
    private $db_key;//数据库索引文件夹，用于namespace;
    /**
     * 类初始化 指定 连接ORACLE的resource
     *
     * @param resourse $link
     */
    public function __construct($link,$key){
        if(is_resource($link)){
            $this->link=$link;
            $this->db_key=$key;
        }else{
            throw new Exception('请先建立数据库连接!');
        }
    }
    /**
     * 设置modle路径
     *
     * @param string $path
     */
    public function set_modle_path($path){
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
    /**
     * 返回model文件路径
     *
     * @return unknown
     */
    public function get_modle_path(){
        return $this->model_path;
    }
    /**
     *  设置view文件路径
     *
     * @param string $path
     */
    public function set_view_path($path){
        if($dir=$this->check_dir($path))
        {
            $this->view_path=$dir;
        }
        else
        {
            mkdir($path,0777,true);
            $this->view_path=$this->check_dir($path);
        }
    }
    /**
     * 返回view文件路径
     *
     * @return string
     */

    public function get_view_path(){
        return $this->view_path;
    }
    /**
     * 设置生成SQL文件路径
     *
     * @param string $path
     */
    public function set_sql_path($path)
    {
        if($dir=$this->check_dir($path))
        {
            $this->sql_path=$dir;
        }
        else
        {
            mkdir($path,0777,true);
            $this->sql_path=$this->check_dir($path);
        }
    }
    /**
     * 返回生成SQL文件路径
     *
     * @return string
     */
    public function get_sql_path(){
        return $this->sql_path;
    }
    /**
     * 设置 生成enumerate 文件路径
     *
     * @param string $path
     */
    public function set_enumerate_path($path)
    {
        if($dir=$this->check_dir($path))
        {
            $this->enumerate_path=$dir;
        }
        else
        {
            mkdir($path,0777,true);
            $this->enumerate_path=$this->check_dir($path);
        }
    }
    /**
     * 返回生成enumerate 文件路径
     *
     * @return string
     */
    public function get_enumerate_path(){
        return $this->enumerate_path;
    }
    /**
     * 设置 生成code文件路径
     *
     * @param string $path
     */
    public function set_code_path($path)
    {
        if($dir=$this->check_dir($path))
        {
            $this->code_path=$dir;
        }
        else
        {
            mkdir($path,0777,true);
            $this->code_path=$this->check_dir($path);
        }
    }
    /**
     * 设置 生成xml文件路径
     *
     * @param string $path
     */
    public function set_xml_path($path)
    {
        if($dir=$this->check_dir($path))
        {
            $this->xml_path=$dir;
        }
        else
        {
            mkdir($path,0777,true);
            $this->xml_path=$this->check_dir($path);
        }
    }
    /**
     * 设置 生成doc文件路径
     *
     * @param string $path
     */
    public function set_doc_path($path)
    {
        if($dir=$this->check_dir($path))
        {
            $this->doc_path=$dir;
        }
        else
        {
            mkdir($path,0777,true);
            $this->doc_path=$this->check_dir($path);
        }
    }
    /**
     * 返回 生成 code文件路径
     *
     * @return string
     */
    public function get_code_path(){
        return $this->code_path;
    }
    /**
     * 生成orm的入口程序
     *
     */
    public function start_mapping(){
        //所有表名,类型,表备注
        $sql="SELECT TABLE_NAME,TABLE_TYPE,COMMENTS FROM USER_TAB_COMMENTS";
        $statement =oci_parse($this->link,$sql);
        oci_execute($statement);
        while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {
            //echo $row['TABLE_NAME'].$row['COMMENTS'];
            $table_name=strtoupper($row['TABLE_NAME']);//表名
            if(strpos($table_name,'$')!==false)
            {
                echo '<span style="color: orangered">已跳过表'.$table_name.'!</span>'."<br/>";
                continue;
            }
            $table_comment=@$row['COMMENTS'];//表备注
            $this->create_model($table_name,$table_comment);
            //生成速度太慢，要产生zend_session超时，暂时关了下面几个生成功能
            //生成model文件
            //生成enumerate
            //$this->create_enumerate($table_name,$table_comment);
            //生成code
            //$this->create_code($table_name,$table_comment);
            //生成view文件
            //$this->create_view($table_name,$table_comment);
            //生成sql文件
            //$this->create_sql($table_name,$table_comment);
            //生成xml文件
            //$this->create_api_xml($table_name,$table_comment);
            //生成doc文件
            //$this->create_api_doc($table_name,$table_comment);
            //$this->create_sql_expansion($table_name,$table_comment);
            echo '表'.$table_name.'生成文件成功!'."<br/>";
        }
    }
    //==================
    /**
     * 根据表名创建model文件
     *
     * @param string $table
     * @param string $table_comment
     */
    public  function create_model($table,$table_comment=null){
        if($this->model_path){
            //生成的文件名
            $file_name=$this->model_path.'/'.strtolower(iconv('utf-8','gb2312//IGNORE',$table)).'.php';
            //打开文件
            //$file_name=iconv("utf-8","gbk",$file_name);
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件".iconv("gb2312","utf-8",$file_name)."失败,请检查权限");
            }
            //待写入的内容
            $content="<?php\r\n";
            $content.="namespace core\db\models\\".$this->db_key.";\r\n";
            $content.="use core\db\driver;\r\n";
            $content.="/**\r\n *注释:$table_comment\r\n *\r\n *\r\n **/\r\n";
            $content.="class ".strtolower($table)." extends driver\dao_oracle{\r\n";
            $content.="\t public ".'$__table'." = '".strtolower($table)."';\r\n";

            //查询 字段名,字段类型,字段长短,字段备注
            $sql="SELECT
			    A.COLUMN_NAME COLUN_NAME,A.DATA_TYPE DATA_TYPE,A.DATA_LENGTH DATA_LENGTH,B.COMMENTS COMMENTS ,A.COLUMN_ID COLUMN_ID
			FROM
			    USER_TAB_COLUMNS A,USER_COL_COMMENTS B
			WHERE
			    A.TABLE_NAME = B.TABLE_NAME
			    AND A.COLUMN_NAME = B.COLUMN_NAME
			    AND A.TABLE_NAME = '".$table."' ORDER BY COLUMN_ID ASC" ;

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {
                $row['COLUN_NAME']=strtolower($row['COLUN_NAME']);//将字段名转成小写
                //exit();
                $content.="\t/**\r\n \t * 注释:".@$row['COMMENTS']."\r\n\t * \r\n\t * \r\n\t * @var ".$row['DATA_TYPE']."(".$row['DATA_LENGTH'].")"."\r\n\t **/\r\n ";

                $content.="\t public $".$row['COLUN_NAME'].";\r\n";
                $content.="\t public $".'_'.$row['COLUN_NAME']."_type='".strtolower($row['DATA_TYPE'])."';\r\n";
            }

            $content.="}\r\n";

            //写入内容
            if (fwrite($fp,$content) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);

        }else{
            throw new Exception("请设置model路径!");
        }
    }
    /**
     * 生成接口所用的xml文档
     *
     * @param unknown_type $table
     * @param unknown_type $table_comment
     */
    public  function create_api_xml($table,$table_comment=null){
        if($this->xml_path){
            //生成的文件名
            $file_name=$this->xml_path.'/'.strtolower(iconv('utf-8','gb2312//IGNORE',$table)).'.xml';
            $json_name=$this->xml_path.'/'.strtolower(iconv('utf-8','gb2312//IGNORE',$table)).'.json';
            //打开文件
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件{$file_name}失败,请检查权限");
            }
            //待写入的内容
            $content="\xEF\xBB\xBF"."<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
            $content.="<data>\r\n\t<row>\r\n";
            //查询 字段名,字段类型,字段长短,字段备注
            $sql="SELECT
			    A.COLUMN_NAME COLUN_NAME,A.DATA_TYPE DATA_TYPE,A.DATA_LENGTH DATA_LENGTH,B.COMMENTS COMMENTS ,A.COLUMN_ID COLUMN_ID
			FROM
			    USER_TAB_COLUMNS A,USER_COL_COMMENTS B
			WHERE
			    A.TABLE_NAME = B.TABLE_NAME
			    AND A.COLUMN_NAME = B.COLUMN_NAME
			    AND A.TABLE_NAME = '".$table."' ORDER BY COLUMN_ID ASC" ;

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            $json_data=array();
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {
                $row['COLUN_NAME']=strtolower($row['COLUN_NAME']);//将字段名转成小写
                //$tmp=preg_split('|[，\|]*|',trim(@$row['COMMENTS']));
                $tmp=explode('，',@$row['COMMENTS']);
                $content.="\t\t<".$row['COLUN_NAME']." displayName=\"".@$tmp[0]."\"></".$row['COLUN_NAME'].">\r\n";
                $json_data[$row['COLUN_NAME']]=@$tmp[0];
            }

            $content.="\t</row>\r\n</data>";

            //写入内容
            if (fwrite($fp,$content) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);
            $json_fp=fopen($json_name,'w+');
            fwrite($json_fp,json_encode($json_data,JSON_UNESCAPED_UNICODE));
            fclose($json_fp);

        }else{
            throw new Exception("请设置xml路径!");
        }
    }
    /**
     * 生成接口所用的doc文档
     *
     * @param unknown_type $table
     * @param unknown_type $table_comment
     */
    public  function create_api_doc($table,$table_comment=null){
        if($this->doc_path){
            //生成的文件名
            $file_name=$this->doc_path.'/'.strtolower(iconv('utf-8','gb2312//IGNORE',$table)).'.doc';
            //打开文件
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件{$file_name}失败,请检查权限");
            }
            //待写入的内容
            $content=<<<start
<table class="MsoTableGrid" border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;mso-yfti-tbllook:480;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;">
 <tbody><tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;">
  <td width="182" valign="top" style="width:136.8pt;border:solid windowtext 1.0pt;mso-border-alt:solid windowtext .5pt;background:#D9D9D9;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><b style="mso-bidi-font-weight:normal;"><span style="mso-bidi-font-size:10.5pt;font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">数据项名称</span></b><b style="mso-bidi-font-weight: normal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p></o:p></span></b></p>
  </td>
  <td width="95" valign="top" style="width:70.95pt;border:solid windowtext 1.0pt;border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt: solid windowtext.5pt;background:#D9D9D9;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><b style="mso-bidi-font-weight:normal;"><span style="mso-bidi-font-size:10.5pt;font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">数据项含义</span></b><b style="mso-bidi-font-weight: normal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p></o:p></span></b></p>
  </td>
  <td width="97" valign="top" style="width:72.75pt;border:solid windowtext 1.0pt;border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt: solid windowtext.5pt;background:#D9D9D9;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><b style="mso-bidi-font-weight:normal;"><span style="mso-bidi-font-size:10.5pt;font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">类型</span></b><b style="mso-bidi-font-weight: normal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p></o:p></span></b></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border:solid windowtext 1.0pt;border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt: solid windowtext.5pt;background:#D9D9D9;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><b style="mso-bidi-font-weight:normal;"><span style="mso-bidi-font-size:10.5pt;font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">宽度</span></b><b style="mso-bidi-font-weight: normal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p></o:p></span></b></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border:solid windowtext 1.0pt;border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt: solid windowtext.5pt;background:#D9D9D9;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><b style="mso-bidi-font-weight:normal;"><span style="mso-bidi-font-size:10.5pt;font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">填报要求</span></b><b style="mso-bidi-font-weight: normal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p></o:p></span></b></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border:solid windowtext 1.0pt;border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt: solid windowtext.5pt;background:#D9D9D9;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><b style="mso-bidi-font-weight:normal;"><span style="mso-bidi-font-size:10.5pt;font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">标准</span></b><b style="mso-bidi-font-weight: normal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p></o:p></span></b></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border:solid windowtext 1.0pt;border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt: solid windowtext.5pt;background:#D9D9D9;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><b style="mso-bidi-font-weight:normal;"><span style="mso-bidi-font-size:10.5pt;font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">说明</span></b><b style="mso-bidi-font-weight: normal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p></o:p></span></b></p>
  </td>
 </tr>
start;
            //查询 字段名,字段类型,字段长短,字段备注
            $sql="SELECT
			    A.COLUMN_NAME COLUN_NAME,A.DATA_TYPE DATA_TYPE,A.DATA_LENGTH DATA_LENGTH,B.COMMENTS COMMENTS ,A.COLUMN_ID COLUMN_ID
			FROM
			    USER_TAB_COLUMNS A,USER_COL_COMMENTS B
			WHERE
			    A.TABLE_NAME = B.TABLE_NAME
			    AND A.COLUMN_NAME = B.COLUMN_NAME
			    AND A.TABLE_NAME = '".$table."' ORDER BY COLUMN_ID ASC" ;

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {
                $row['COLUN_NAME']=strtolower($row['COLUN_NAME']);//将字段名转成小写
                $row['DATA_TYPE']=strtolower($row['DATA_TYPE']);
                $date_type="";
                $date_width="";
                if (strpos($row['DATA_TYPE'],"varchar")!==false)
                {
                    $date_type="S";
                    $date_width="AN..".$row['DATA_LENGTH'];
                }
                elseif (strpos($row['DATA_TYPE'],"number")!==false)
                {
                    $date_type="D";
                    $date_width="N";
                }
                else
                {
                    $date_type="";
                }
                $row['COMMENTS']=isset($row['COMMENTS'])?$row['COMMENTS']:"";
                $content.=<<<doc
<tr style="mso-yfti-irow:1;">
  <td width="182" valign="top" style="width:136.8pt;border:solid windowtext 1.0pt;border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><span lang="EN-US">{$row['COLUN_NAME']}<o:p></o:p></span></p>
  </td>
  <td width="95" valign="top" style="width:70.95pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><span style="font-family:宋体;mso-ascii-font-family:&quot;Times New Roman&quot;;mso-hansi-font-family:&quot;Times New Roman&quot;">{$row['COMMENTS']}</span><span lang="EN-US"><o:p></o:p></span></p>
  </td>
  <td width="97" valign="top" style="width:72.75pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;">$date_type<o:p></o:p></span></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;">$date_width<o:p></o:p></span></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p>&nbsp;</o:p></span></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p>&nbsp;</o:p></span></p>
  </td>
  <td width="97" valign="top" style="width:72.8pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;">
  <p class="MsoNormal"><span lang="EN-US" style="mso-bidi-font-size:10.5pt;"><o:p>&nbsp;</o:p></span></p>
  </td>
 </tr>
doc;
            }

            $content.="</tbody>
</table>";
            $word_template=<<<doc
<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
<xml>
 <w:WordDocument>
  <w:View>Print</w:View>
</xml>
</head>
<body>
$content
</body>
</html>
doc;
            //写入内容
            if (fwrite($fp,$word_template) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);

        }else{
            throw new Exception("请设置doc路径!");
        }
    }
    /**
     * 生成enumerate文件
     * 把字段备注生成数组 字段备注|radio|1=>男,2=>女 生成 $字段=array(1=>男,2=>女);
     *
     * @param string $table
     * @param string $table_comment
     */
    private function create_enumerate($table,$table_comment=null){

        if($this->enumerate_path){
            //生成的文件名
            $file_name=$this->enumerate_path.'/'.iconv('utf-8','gb2312//IGNORE',$table).'.php';
            //打开文件
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件{$file_name}失败,请检查权限");
            }
            //待写入的内容
            $content="<?php\r\n";
            $content.="/**\r\n *注释:$table_comment\r\n *\r\n *\r\n **/\r\n";


            //查询 字段名,字段备注
            $sql="SELECT
					A.COLUMN_NAME COLUMN_NAME,COMMENTS,COLUMN_ID
				  FROM  
				  	USER_TAB_COLUMNS A ,USER_COL_COMMENTS B
				  WHERE 
				  	A.TABLE_NAME = B.TABLE_NAME
				  AND  
				  	A.COLUMN_NAME = B.COLUMN_NAME
			      AND
			      	A.TABLE_NAME = '".($table)."' ORDER BY COLUMN_ID ASC ";

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {
                $row['COLUMN_NAME']=strtolower($row['COLUMN_NAME']);//字段名转成小写
                $comments=@$row['COMMENTS'];//备注

                if(!empty($comments) && ($comments_array=explode("|",$comments))){

                    if(($count=count($comments_array))>2){
                        //print_r($comments_array);

                        $content.="\t/**\r\n \t * 表注释:".$row['COMMENTS']."\r\n\t * \r\n\t * \r\n\t **/\r\n ";
                        $comments=(preg_replace("~[0-9]{1,}~","'\\0'",$comments_array[$count-1]));//给下标加引号
                        //print_r($comments);

                        $comments=(preg_replace("~>~","\\0'",$comments));//在>后面加引号

                        $comments=(preg_replace("~,~","'\\0",$comments));//在逗号前面加引号

                        $comments=$comments.'\'';//结尾加引号

                        $content.='$'.$row['COLUMN_NAME'].'=array('.$comments.');'."\r\n";
                    }
                }
            }

            $content.="\r\n";

            //写入内容
            if (fwrite($fp,$content) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);

        }else{
            throw new Exception("请设置生成enumerate文件路径!");
        }
    }
    /**
     * 根据表结构生成代码文件
     *
     * @param string $table
     * @param string $table_comment
     */
    private function create_code($table,$table_comment=null){
        if($this->code_path){
            //生成的文件名
            $file_name=$this->code_path.'/'.iconv('utf-8','gb2312//IGNORE',$table).'.php';
            //打开文件
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件{$file_name}失败,请检查权限");
            }
            //待写入的内容更新记录
            $content_update="/**\r\n *注释:以下自动生成的代码主要用于editaction(编辑与更新)代码中，提交的数据对模型数据自动赋值\r\n *\r\n *\r\n **/\r\n";
            //待写入的内容view
            $content="<?php\r\n";
            $content.="/**\r\n *注释:$table_comment\r\n *\r\n *\r\n **/\r\n";
            $content.="/**\r\n *注释:以下自动生成的代码主要用于displayaction(显示)代码中，完成模型数据对表现层的自动赋值\r\n *\r\n *\r\n **/\r\n";

            //查询 字段名,字段备注
            $sql="SELECT
					A.COLUMN_NAME COLUMN_NAME,COMMENTS,COLUMN_ID
				  FROM  
				  	USER_TAB_COLUMNS A ,USER_COL_COMMENTS B
				  WHERE 
				  	A.TABLE_NAME = B.TABLE_NAME
				  AND  
				  	A.COLUMN_NAME = B.COLUMN_NAME
			      AND
			      	A.TABLE_NAME = '".($table)."' ORDER BY COLUMN_ID ASC ";

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            $table=strtolower($table);
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {

                $row['COLUMN_NAME']=strtolower($row['COLUMN_NAME']);//将字段名转成小写
                $comments=@$row['COMMENTS'];//备注


                $token=0;//备注中是数组的标识      $token=0/1备注中否/是出现 , 形如 备注|radio|1=>yes,2=>no
                $content_update.='$'.$table.'->'.$row['COLUMN_NAME'].' = $this->_request->getParam(\''.$row['COLUMN_NAME'].'\');//'.$comments."\r\n\r\n";


                if(!empty($comments) && ($comments_array=explode("|",$comments))){

                    if(($count=count($comments_array))>2){
                        //print_r($comments_array);
                        $token=1;
                        $content.="/**\r\n * 表注释:".$row['COMMENTS']."\r\n * \r\n * \r\n **/\r\n ";
                        $comment=(preg_replace("~[0-9]{1,}~","'\\0'",$comments_array[$count-1]));//给下标加引号
                        //print_r($comments);

                        $comment=(preg_replace("~>~","\\0'",$comment));//在>后面加引号

                        $comment=(preg_replace("~,~","'\\0",$comment));//在逗号前面加引号

                        $comment=$comment.'\'';//结尾加引号

                        $content.='$'. @$row['COLUMN_NAME']."=array($comment);\r\n";
                        $content.='$'."this->view->".$row['COLUMN_NAME']."_options =\$".$row['COLUMN_NAME']."; //$comments\r\n";
                    }
                }
                //是不是数组
                if($token==1){
                    $content.='$'.'this->view->'.$row['COLUMN_NAME']."_current = \$".$table."->".$row['COLUMN_NAME'].";//$comments\r\n"; //$comments\r\n\r\n";
                }else{
                    $content.='$'.'this->view->'.$row['COLUMN_NAME']." = \$".$table."->".$row['COLUMN_NAME'].";//$comments\r\n"; //$comments\r\n\r\n";
                }
            }



            $content.="\r\n";

            $content.=$content_update;//

            //写入内容
            if (fwrite($fp,$content) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);

        }else{
            throw new Exception("请设置生成code文件路径!");
        }
    }
    /**
     * 根据表字段备注,生成view文件
     *
     * @param string $table
     * @param string $table_comment
     */

    private function create_view($table,$table_comment=null){
        if($this->view_path){
            //生成的文件名
            $file_name=$this->view_path.'/'.iconv('utf-8','gb2312//IGNORE',$table).'.php';
            //打开文件
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件{$file_name}失败,请检查权限");
            }
            //待写入的内容
            $content="<?php\r\n";
            $content.="/**\r\n *注释:$table_comment\r\n *\r\n *\r\n **/\r\n";


            //查询 字段名,字段备注
            $sql="SELECT
					A.COLUMN_NAME COLUMN_NAME,COMMENTS,COLUMN_ID
				  FROM  
				  	USER_TAB_COLUMNS A ,USER_COL_COMMENTS B
				  WHERE 
				  	A.TABLE_NAME = B.TABLE_NAME
				  AND  
				  	A.COLUMN_NAME = B.COLUMN_NAME
			      AND
			      	A.TABLE_NAME = '".($table)."' ORDER BY COLUMN_ID ASC ";

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {
                $row['COLUMN_NAME']=strtolower($row['COLUMN_NAME']);//字段名转成小写
                $comments=@$row['COMMENTS'];//备注

                if(!empty($comments) && ($comments_array=explode("|",$comments))){

                    if(($count=count($comments_array))>1){
                        //print_r($comments_array);
                        $content.="//$comments \r\n";
                        switch (strtolower($comments_array[1])){
                            case  'text':
                                $content.="<input type='text' name='".$row['COLUMN_NAME']."' id='".$row['COLUMN_NAME']."' value='<!--{\$".$row['COLUMN_NAME']."}-->' length='' _note='$comments'/>\r\n";

                                break;
                            case  'radio':
                                $content.="<!--{html_radios name='".$row['COLUMN_NAME']."' options=\$".$row['COLUMN_NAME']."_options checked=\$".$row['COLUMN_NAME']."_current separator='<br />' _note='$comments' }-->\r\n";
                                break;

                            case  'textarea':
                                $content.="<textarea name='".$row['COLUMN_NAME']."' id='".$row['COLUMN_NAME']."' _note='$comments'/><!--{\$".$row['COLUMN_NAME']."}--></textarea>\r\n";
                                break;

                            case  'checkbox':;
                                $content.="<!--{html_checkboxes name='".$row['COLUMN_NAME']."' options=$".$row['COLUMN_NAME']."_options checked=$".$row['COLUMN_NAME']."_current separator='<br />' _note='$comments' }-->";
                                break;
                            case  'select':
                                $content.="<select name='".$row['COLUMN_NAME']."' id='".$row['COLUMN_NAME']."' _note='$comments'>\r\n";
                                $content.="\t<!--{html_options options=\$".$row['COLUMN_NAME']."_options selected=\$".$row['COLUMN_NAME']."_current }--> \r\n";
                                $content.="</select>\r\n";
                                break;
                                ;
                            default:
                                $content.="<input type='text' name='".$row['COLUMN_NAME']."' id='".$row['COLUMN_NAME']."' value='<!--{\$".$row['COLUMN_NAME']."}-->' length='30' _note='$comments'/>\r\n";
                                ;
                        }


                    }
                }
            }

            $content.="\r\n";

            //写入内容
            if (fwrite($fp,$content) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);

        }else{
            throw new Exception("请设置生成view文件路径!");
        }
    }
    /**
     * 根据表生成对应的SQL文件
     *
     * @param string $table
     * @param string $table_comment
     */
    private  function create_sql($table,$table_comment=null){
        if($this->sql_path){
            //生成的文件名
            $file_name=$this->sql_path.'/'.iconv('utf-8','gb2312//IGNORE',$table).'.sql';
            //打开文件
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件{$file_name}失败,请检查权限");
            }
            //生成字段备注
            $comment_fields="";
            //待写入的内容
            $content="";
            $content.="#*注释:{$table_comment}\r\n";
            $content.="CREATE TABLE \"{$table}\" (\r\n";

            //查询 字段名,字段备注
            //查询 字段名,字段类型,字段长短,允许为null,缺省值,字段备注
            $sql="SELECT
			    A.COLUMN_NAME COLUN_NAME,A.DATA_TYPE DATA_TYPE,A.DATA_LENGTH DATA_LENGTH,A.NULLABLE NULLABLE,A.DATA_DEFAULT DATA_DEFAULT,B.COMMENTS COMMENTS, A.COLUMN_ID COLUMN_ID
			FROM
			    USER_TAB_COLUMNS A,USER_COL_COMMENTS B
			WHERE
			    A.TABLE_NAME = B.TABLE_NAME
			    AND A.COLUMN_NAME = B.COLUMN_NAME
			    AND A.TABLE_NAME = '".($table)."' ORDER BY COLUMN_ID ASC";

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {

                //默认值
                $default=empty($row['DATA_DEFAULT'])?'':' DEFAULT '.$row['DATA_DEFAULT'];
                //是否允许为空
                $nullable=$row['NULLABLE']=='Y'? ' ': ' NOT NULL';

                $content.="\t\"".$row['COLUN_NAME']."\" ".$row['DATA_TYPE'].'('.$row['DATA_LENGTH'].') '.$nullable.' '.$default.",\r\n";
                //字段备注为空
                if(!empty($row['COMMENTS'])){
                    $comment_fields.="COMMENT ON COLUMN \"{$table}\".\"".$row['COLUN_NAME']."\" IS '".$row['COMMENTS']."';\r\n";
                }

            }
            //查询出字段主健
            $sql="SELECT
				COLUMN_NAME 
			FROM 
				USER_CONS_COLUMNS 
			WHERE 
				CONSTRAINT_NAME 
			IN 
				(SELECT 
					CONSTRAINT_NAME 
				FROM 
					USER_CONSTRAINTS 
				WHERE 
					TABLE_NAME ='".strtoupper($table)."' 
					AND CONSTRAINT_TYPE='P')";
            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            $primary_key_array=oci_fetch_array ($statement,OCI_ASSOC);
            $primary_key=$primary_key_array['COLUMN_NAME'];//取得主键名
            if(!empty($primary_key)){
                $content.="\tPRIMARY KEY ({$primary_key})\r\n";
            }
            //去掉最后一个逗号
            $content=rtrim(trim($content),',');

            $content.="\r\n);\r\n";
            //表备注存在
            if(!empty($table_comment)){
                $content.="COMMENT ON TABLE \"{$table}\" IS '{$table_comment}';\r\n";//表备注
            }
            $content.=$comment_fields;//字段所有备注

            //写入内容
            if (fwrite($fp,$content) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);

        }else{
            throw new Exception("请设置生成SQL文件路径!");
        }
    }
    /**
     * 根据表生成对应的SQL_expansion文件
     *
     * @param string $table
     * @param string $table_comment
     */
    private  function create_sql_expansion($table,$table_comment=null){
        set_time_limit(0);
        if($this->sql_path){
            //生成的文件名
            //$file_name=$this->sql_path.'/'.$table.'_expansion.sql';
            //不明白为什么不生成小写文件名 在xxx.sql变个文件名又对了，改回去又变大小了，怪
            $file_name=$this->sql_path.'/'.strtolower(iconv('utf-8','gb2312//IGNORE',$table)).'_expansion.sql';
            //echo $file_name;
            //打开文件
            if(($fp=fopen($file_name,'w+'))===false){
                throw new Exception("打开文件{$file_name}失败,请检查权限");
            }
            //生成字段备注
            $comment_fields="";
            //待写入的内容
            $content="";
            //$content.="#*注释:{$table_comment}\r\n";
            //查询 字段名,字段备注
            //查询 字段名,字段类型,字段长短,允许为null,缺省值,字段备注
            $sql="SELECT
			    A.COLUMN_NAME COLUN_NAME,A.DATA_TYPE DATA_TYPE,A.DATA_LENGTH DATA_LENGTH,A.NULLABLE NULLABLE,A.DATA_DEFAULT DATA_DEFAULT,B.COMMENTS COMMENTS, A.COLUMN_ID COLUMN_ID
			FROM
			    USER_TAB_COLUMNS A,USER_COL_COMMENTS B
			WHERE
			    A.TABLE_NAME = B.TABLE_NAME
			    AND A.COLUMN_NAME = B.COLUMN_NAME
			    AND A.TABLE_NAME = '".($table)."' ORDER BY COLUMN_ID ASC";
            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);
            $i=0;
            $fieldArray=array();
            while ($row = oci_fetch_array ($statement,OCI_ASSOC) ) {
                //默认值
                $default=empty($row['DATA_DEFAULT'])?'':' DEFAULT '.$row['DATA_DEFAULT'];
                if($i==0){
                    $content.="create table ".strtolower($table)."(";
                    $content.=" ".strtolower($row['COLUN_NAME'])." ".strtolower($row['DATA_TYPE']).'('.strtolower($row['DATA_LENGTH']).') '.$default;

                    //查询出字段主健
                    $sql="SELECT
				COLUMN_NAME 
			FROM 
				USER_CONS_COLUMNS 
			WHERE 
				CONSTRAINT_NAME 
			IN 
				(SELECT 
					CONSTRAINT_NAME 
				FROM 
					USER_CONSTRAINTS 
				WHERE 
					TABLE_NAME ='".strtoupper($table)."' 
					AND CONSTRAINT_TYPE='P')";
                    $statement1 =oci_parse($this->link,$sql);
                    oci_execute($statement1);
                    $primary_key_array=oci_fetch_array ($statement1,OCI_ASSOC);
                    $primary_key=$primary_key_array['COLUMN_NAME'];//取得主键名
                    $primary_key=strtolower($primary_key);
                    if(strtolower($row['COLUN_NAME'])!=$primary_key and !empty($primary_key)){
                        //如果第一个字段不是主关键字，则同时要建立这个字段
                        $content.=",$primary_key varchar2(30) ";
                    }
                    if(!empty($primary_key) and $primary_key!=''){
                        $content.=" ,PRIMARY KEY ($primary_key));\r\n";
                    }else{
                        $content.=");\r\n";
                    }
                }else{
                    if(strtolower($row['COLUN_NAME'])!=$primary_key){
                        if($default!=''){
                            $content.="alter table ".strtolower($table)." add ".strtolower($row['COLUN_NAME'])." ".strtolower($row['DATA_TYPE']).'('.$row['DATA_LENGTH'].') '.$default.";\r\n";
                        }else{

                            $content.="alter table ".strtolower($table)." add ".strtolower($row['COLUN_NAME'])." ".strtolower($row['DATA_TYPE']).'('.$row['DATA_LENGTH'].') '.$default.";\r\n";
                        }}else{
                        $content.="alter table ".strtolower($table)." modify ".strtolower($row['COLUN_NAME'])." ".strtolower($row['DATA_TYPE']).'('.$row['DATA_LENGTH'].') '.$default.";\r\n";

                    }
                }
                //字段备注不为空
                if(!empty($row['COMMENTS'])){
                    $comment_fields.="comment on column ".strtolower($table).'.'.strtolower($row['COLUN_NAME'])." is '".$row['COMMENTS']."';\r\n";
                }
                $i++;

            }
            $sql="select * from user_ind_columns where table_name='$table'";//表名必须大写

            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);

            //echo oci_error($statement);
            while(1 and $rows=oci_fetch_array ($statement,OCI_ASSOC)){
                if(strtolower($rows['TABLE_NAME'])==strtolower($table)){
                    $content.="create index ".strtolower($rows['INDEX_NAME'])." on ".strtolower($table)."(".strtolower($rows['COLUMN_NAME']).");\r\n";
                }
            }
            //表备注存在
            if(1 and !empty($table_comment)){
                $content.="comment on table ".strtolower($table)." is '".$table_comment."';\r\n";//表备注
            }
            $content.=$comment_fields;//字段所有备注


            //表数据，如果数据太多，只取前１００条用于测试
            if(strtolower($table)=='logs' or
                strtolower($table)=='family_archive' or
                strtolower($table)=='individual_core' or
                strtolower($table)=='individual_archive'
            ){

                $sql="select * from (select A.*,ROWNUM as RN from ".strtolower($table)." A where ROWNUM <=$number_of_records) where RN>0 ";

            }else{
                $sql="select * from ".strtolower($table);
            }
            $number_of_records=10000;
            $sql="select * from (select A.*,ROWNUM as RN from ".strtolower($table)." A where ROWNUM <=$number_of_records) where RN>0 ";
            //$sql="select * from student";
            //echo $sql."<br />";
            //echo $this->link;
            $statement =oci_parse($this->link,$sql);
            oci_execute($statement);

            while(1 and $rows=oci_fetch_array ($statement,OCI_ASSOC)){
                //echo $table."<br />";
                $queryString="insert into ".strtolower($table);
                $keys="(";
                $values=" values(";
                foreach ($rows as $key=>$value){
                    if(strtolower($key)=='rn'){
                        continue;
                    }
                    $keys=$keys.strtolower($key).',';
                    $values=$values."'".trim($value)."',";
                }
                $keys=rtrim($keys,',');
                $values=rtrim($values,',');
                $keys=$keys.')';
                //生成的sql后不能有';'号，日期与数字都不需要特别处理
                $values=$values.');';
                $queryString=$queryString.$keys.$values;
                //echo $queryString."<br />";
                $content.=$queryString."\r\n";//表备注
            }
            //写入内容
            if (fwrite($fp,$content) === FALSE) {
                throw new Exception("写入文件{$file_name}失败,请检查权限");
            }
            fclose($fp);
            $fileName1=$this->sql_path.'/all_data.sql';
            file_put_contents($fileName1,$content,FILE_APPEND);
        }else{
            throw new Exception("请设置生成SQL文件路径!");
        }
    }
    //删除手工生成的标准数据
    public function dropTables(){
        $sql="select table_name from tabs where table_name like 'WS_E%' or table_name like 'WS_H%'";//表名必须大写
        ///echo $sql;
        $statement =oci_parse($this->link,$sql);
        oci_execute($statement);

        //echo oci_error($statement);
        $table_names=array();
        while($rows=oci_fetch_array ($statement,OCI_ASSOC)){

            $sql1="drop table ". $rows['TABLE_NAME'];
            // echo $sql1;
            $statement_ =oci_parse($this->link,$sql1);
            oci_execute($statement_);
        }



    }
    //====
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