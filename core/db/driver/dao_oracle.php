<?php
/**
 *
 * 文件说明: 基于PDO的oracle数据处理类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/21
 * Time: 15:23
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\db\driver;
use core\cache\cache;
use core\conf;
use core\db\db_abstract;
use core\log;
use PDO;
class dao_oracle implements db_abstract
{
    //当前数据库连接
    private $_dao_conn=null;
    //当前对象的属性值数组
    private $_dao_vars=null;
    //属性默认值
    private $_dao_init_value='';
    //调试开关
    private $_dao_debug=false;
    //where条件字句
    private $_dao_where=null;
    //where字句中的占位符数组
    private $_dao_where_params=array();
    //排序
    private $_dao_orderby='';
    //limit的值
    private $_dao_limit_start;
    private $_dao_limit_number;
    //分组
    private $_dao_groupby='';
    //having处理
    private $_dao_having;
    //连接查询
    private $_dao_join=array();
    private $_dao_join_table=array();
    private $_dao_join_table_alias=array();
    //别名操作
    private $_dao_filed_array=array();
    private $_dao_field_list='';
    private $_dao_field_list_add=false;
    private $_dao_direct_query=false;
    private $_dao_selectas_sign=false;
    //子查询
    private $_dao_subquery;
    //SQL语句
    private $_dao_query_string;
    //自定义查询标志
    private $_dao_user_query=false;
    //定义查询缓存
    private $_dao_cache=false;
    private $_dao_cache_key;

    public function __construct($host)
    {
        $this->_dao_conn=oracle_connect::getInstance($host);
        $this->_dao_init_value="#^&*^&*#";
        $this->_dao_debug=conf::get('system.db_debug');
        if($this->_dao_vars==null)
        {
            $this->_dao_vars=get_object_vars($this);
        }
        $this->_initValue();
    }
    /**
     * where whereAdd的别名
     * @param $where where条件
     * @param array $params 占位数组
     * @return dao_oracle
     */
    public function where($where,$params=array())
    {
        return $this->whereAdd($where,$params);
    }
    /**
     * whereAdd 构造where语句，where字句使用占位符时，不能有引号
     * @param $where where条件
     * @param array $params 占位数组
     * @return $this
     */
    public function whereAdd($where,$params=array())
    {
        if($this->_dao_where==null)
        {
            $this->_dao_where='('.$where.')';
        }
        else
        {
            $this->_dao_where=$this->_dao_where." and (".$where.")";
        }
        //合并占位数组
        if(!empty($params))
        {
            $this->_dao_where_params=array_merge($this->_dao_where_params,$params);
        }
        return $this;
    }
    /**
     * orderby 排序
     * @param $orderby
     * @return $this
     */
    public function orderby($orderby)
    {
        $this->_dao_orderby.=$orderby;
        return $this;
    }
    /**
     * limit 构造MySQL类似的语句
     * @param $start
     * @param $number
     * @return $this
     */
    public function limit($start,$number)
    {
        $this->_dao_limit_start=$start;
        $this->_dao_limit_number=$number;
        return $this;
    }
    /**
     * groupby 构造groupby字句
     * @param $groupby 直接写group by 字句内容
     * @return $this
     */
    public function groupby($groupby)
    {
        $this->_dao_groupby=$groupby;
        return $this;
    }
    /**
     * having 构造having字句内容，直接写having字句内容
     * @param $having
     */
    public function having($having)
    {
        $this->_dao_having=$having;
        return $this;
    }
    /**
     * join joinAdd的别名直接使用joinAdd
     * @param string $method 连接方法，inner,left,right
     * @param string $masterTable 主表对象
     * @param string $slaveTable 从表对象
     * @param string $masterKeyColumn 主表连接字段，可以是字符串，也可以是数组
     * @param string $slaveKeyColumn 从表连接字段，可以是字符串，也可以是数组，必须和主表连接字段一一对应
     * @return dao_oracle
     */
    public function join($method='inner',$masterTable='',$slaveTable='',$masterKeyColumn='',$slaveKeyColumn='')
    {
        return $this->joinAdd($method='inner',$masterTable='',$slaveTable='',$masterKeyColumn='',$slaveKeyColumn='');
    }
    /**
     * joinAdd 连接查询操作
     * @param string $method 连接方法，inner,left,right
     * @param string $masterTable 主表对象
     * @param string $slaveTable 从表对象
     * @param string $masterKeyColumn 主表连接字段，可以是字符串，也可以是数组
     * @param string $slaveKeyColumn 从表连接字段，可以是字符串，也可以是数组，必须和主表连接字段一一对应
     * @return $this
     */
    public function joinAdd($method='inner',$masterTable='',$slaveTable='',$masterKeyColumn='',$slaveKeyColumn='')
    {
        //join on on后面条件
        $join_on_str='';
        if(is_array($masterKeyColumn) && is_array($slaveKeyColumn))
        {
            if(count($masterKeyColumn)!=count($slaveKeyColumn))
            {
                new \Exception("masterKeyColumn 和 slaveKeyColumn 字段数必须一致！");
            }
            $masterKey=array_values($masterKeyColumn);
            $slaveKey =array_values($slaveKeyColumn);
        }
        else
        {
            $masterKey[0]=$masterKeyColumn;
            $slaveKey[0]=$slaveKeyColumn;
        }
        foreach ($masterKey as $key=>$value)
        {
            //判定主从表字段是否有函数修饰
            if(strpos($value,'(')!==false && strpos($slaveKey[$key],'(')!==false)
            {
                preg_match('|.*\(([a-zA-Z0-9_]+)\)|is',$value,$cols);
                preg_match('|.*\(([a-zA-Z0-9_]+)\)|is',$slaveKey[$key],$slave_cols);
                if(isset($cols[1]) && $cols[1]!='' && isset($slave_cols[1]) && $slave_cols[1]!='')
                {
                    $value=str_replace($cols[1],$masterTable->__table.'.'.$cols[1],$value);
                    $slaveKey[$key]=str_replace($slave_cols[1],$slaveTable->__table.'.'.$slave_cols[1],$slaveKey[$key]);
                    $join_on_str.=$value.'='.$slaveKey[$key] .' and ';
                }
                else
                {
                    $join_on_str.=$masterTable->__table.'.'.$value.'='.$slaveTable->__table.'.'.$slaveKey[$key] .' and ';
                }
            }
            elseif(strpos($value,'(')!==false && strpos($slaveKey[$key],'(')===false)
            {
                preg_match('|.*\(([a-zA-Z0-9_]+)\)|is',$value,$cols);
                if(isset($cols[1]) && $cols[1]!='')
                {
                    $value=str_replace($cols[1],$masterTable->__table.'.'.$cols[1],$value);
                    $join_on_str.=$value.'='.$slaveTable->__table.'.'.$slaveKey[$key] .' and ';
                }
                else
                {
                    $join_on_str.=$masterTable->__table.'.'.$value.'='.$slaveTable->__table.'.'.$slaveKey[$key] .' and ';
                }
            }
            elseif(strpos($value,'(')===false && strpos($slaveKey[$key],'(')!==false)
            {
                preg_match('|.*\(([a-zA-Z0-9_]+)\)|is',$slaveKey[$key],$slave_cols);
                if(isset($slave_cols[1]) && $slave_cols[1]!='')
                {
                    $slaveKey[$key]=str_replace($slave_cols[1],$slaveTable->__table.'.'.$slave_cols[1],$slaveKey[$key]);
                    $join_on_str.=$masterTable->__table.'.'.$value.'='.$slaveKey[$key] .' and ';
                }
                else
                {
                    $join_on_str.=$masterTable->__table.'.'.$value.'='.$slaveTable->__table.'.'.$slaveKey[$key] .' and ';
                }
            }
            else
            {
                $join_on_str.=$masterTable->__table.'.'.$value.'='.$slaveTable->__table.'.'.$slaveKey[$key] .' and ';
            }
        }
        //去掉末尾的 and
        $join_on_str = substr($join_on_str,0,-4);
        //------join on 后条件------end
        $join_string="";
        $new_join_table=true;
        switch ($method){
            case "inner":
                if(!in_array($slaveTable,$this->_dao_join_table)){
                    $join_string=$join_string." inner join ".$slaveTable->__table." on ".$join_on_str;
                    //如果关联的从表本身也要对数据进行过滤，则在此处理
                    if($slaveTable->getWhereString()!=''){
                        $join_string=$join_string.' and ('.$slaveTable->getWhereString().')';
                    }
                }else{
                    $join_string=$this->_dao_join[$slaveTable->__table]." and ".$masterTable->__table.".".$masterKeyColumn."=".$slaveTable->__table.".".$slaveKeyColumn." ";
                    $new_join_table=false;
                }
                break;
            case "left":
                if(!in_array($slaveTable,$this->_dao_join_table)){
                    $join_string=$join_string." left join ".$slaveTable->__table." on ".$join_on_str;
                    if($slaveTable->getWhereString()!=''){
                        $join_string=$join_string.' and ('.$slaveTable->getWhereString().')';
                    }
                }else{
                    $join_string=$this->_dao_join[$slaveTable->__table]." and ".$masterTable->__table.".".$masterKeyColumn."=".$slaveTable->__table.".".$slaveKeyColumn." ";
                    $new_join_table=false;
                }
                break;
            case "right":
                if(!in_array($slaveTable,$this->_dao_join_table)){
                    $join_string=$join_string." right join ".$slaveTable->__table." on ".$join_on_str;
                    if($slaveTable->getWhereString()!=''){
                        $join_string=$join_string.' and ('.$slaveTable->getWhereString().')';
                    }
                }else{
                    $join_string=$this->_dao_join[$slaveTable->__table]." and ".$masterTable->__table.".".$masterKeyColumn."=".$slaveTable->__table.".".$slaveKeyColumn." ";
                    $new_join_table=false;
                }
                break;
        }
        $this->_dao_join[$slaveTable->__table]=$join_string;
        if($new_join_table){
            $this->_dao_join_table[$slaveTable->__table]=$slaveTable;
            //将关联表名与形成的关联查询字串写进成员数组变量中存储
            $alphabet='bcdefghijklmnopqrstuvwxyz';
            $tableAlias=substr($alphabet,count($this->_dao_join_table_alias),1);
            $this->_dao_join_table_alias[$slaveTable->__table]=$tableAlias;
        }
        return $this;
    }
    /**
     * selectAdd
     * 添加自定义自段 如
     * sum('salary') as sum_salary,average('salary') as average_salary
     * 处理后将生成
     * sum('salary') as tableName_sum_salary,average('salary') as tableName_average_salary
     * @param $cols
     * @param string $type 为空时，将仅使用用户自定义的字段。为add的时候，则会将用户自定义字段与所有表字段融合在一起
     */
    public function selectAdd($cols,$type='')
    {
        $columns=explode(',',$cols);
        foreach ($columns as $key=>$value)
        {
            $level2=explode(' as ',$value);
            if(count($level2)<=1)
            {
                //echo "您的selectAdd使用上有错";
                continue;
            }
            $this->_dao_filed_array[]=$level2[1];
        }
        $this->_dao_field_list=$this->_dao_field_list.','.$cols;
        $this->_dao_field_list=ltrim($this->_dao_field_list,',');
        $this->_dao_field_list_add=true;
    }
    /**
     * selectAs
     *
     * 限制查询字段数
     *
     * @param $cols
     */
    public function selectAs($cols)
    {
        $columns=explode(',',$cols);
        foreach ($columns as $key=>$value)
        {
            $level2=explode(' as ',$value);
            if(count($level2)<=1)
            {
                //echo "您的selectAdd使用上有错";
                continue;
            }
            $tmp_arr=$level2[1];
            $this->_dao_filed_array[]=$level2[1];
        }
        $this->_dao_field_list=$cols;
        $this->_dao_selectas_sign=true;
        return $this;
    }
    /**
     * select 组装SQL语句
     * @param bool $invoke 默认为true，为false时，为一些只需要生成where语句的功能服务。
     * @return $this
     * @throws \Exception
     */
    public function select($invoke=true)
    {
        $this->_dao_direct_query=false;
        //生成sql语句
        $field_list="";
        $var_main=$this->_dao_vars;
        //先处理主表，即使只有一张表，也这样处理。这样，单表与多表的处理方式就一致了
        $temp_filed_list='';
        foreach ($var_main as $key=>$value)
        {
            //过滤内部dao变量，这里有一个不完善的地方，也就是说，表的字段名不能用'_'开头，否则冲突，一下个版本升级为'__'好一些
            if(substr($key,0,1)!='_')
            {
                //解决oracle不用能表名做前缀,否则会导致字段名过长的问题,其中主表的别名固定为a，从表依次
                $alias='a';
                //不在进行类型判断了
                $type="_".$key."_type";
                if(isset($var_main[$type]) && ($var_main[$type]=="date" || strpos($var_main[$type],'timestamp') !==false))
                {
                    $temp_filed_list=$temp_filed_list."to_char(".$var_main['__table'].".".$key.",'yyyy-mm-dd hh:mi:ss') as ".$alias."_".$key.",";
                }
                else {
                    $temp_filed_list=$temp_filed_list.$var_main['__table'].".".$key." as ".$alias."_".$key.",";
                }
            }
        }
        //这里把字段命名冲突的事交给使用者本身了，如果自定义字段与已有字段冲突，执行后系统报错
        if($this->_dao_field_list!='' and $this->_dao_field_list_add)
        {
            $temp_filed_list=$temp_filed_list.$this->_dao_field_list.',';
        }
        elseif($this->_dao_field_list!='' and !$this->_dao_field_list_add)
        {
            $temp_filed_list=$this->_dao_field_list.',';
        }
        $field_list=$field_list.$temp_filed_list;
        $join_string=" ";
        foreach ($this->_dao_join_table as $k=>$v)
        {
            //取表对象
            $table=$v;
            //获取实例的成员变量及其值
            $var=get_object_vars($table);
            //生成从表的字段列表
            $temp_filed_list='';
            //对是否进行了分组操作进行处理，如果主表有分组操作，则从表不能直接出现字段列表，只能从从表的selectAdd中取字段，否则从表的字段不出现
            if($this->_dao_groupby=='')
            {
                foreach ($var as $key=>$value)
                {
                    //过滤内部dao变量，这里有一个不完善的地方，也就是说，字段名不能用_开头
                    if(substr($key,0,1)!='_')
                    {
                        //生成形如user.name as user_name,user.age as user_age的字段列表
                        //解决oracle不用能表名做前缀导致过长的问题,
                        $alias=$this->_dao_join_table_alias[$var['__table']];
                        $type="_".$key."_type";
                        //自动处理日期时间字段
                        if(isset($var[$type]) && ($var[$type]=="date" || strpos($var[$type],'timestamp') !==false))
                        {
                            $temp_filed_list=$temp_filed_list."to_char(".$var['__table'].".".$key.",'yyyy-mm-dd hh:mi:ss') as ".$alias."_".$key.",";
                        }
                        else
                        {
                            $temp_filed_list=$temp_filed_list.$var['__table'].".".$key." as ".$alias."_".$key.",";
                        }
                    }
                }
                //生成从表的自定义字段列表 下面的代码是正确的20111022
                if($table->_dao_field_list!='' and $table->_dao_field_list_add)
                {
                    $temp_filed_list=$temp_filed_list.$table->_dao_field_list.',';
                }elseif($table->_dao_field_list!='' and !$table->_dao_field_list_add)
                {
                    $temp_filed_list=$table->_dao_field_list.',';
                }
            }else{
                //如主表有分组，仅添加从表的自定义字段到从表字段列表
                if($table->_dao_field_list!='')
                {
                    $temp_filed_list=$temp_filed_list.$table->_dao_field_list.',';
                }
            }
            //这句很重要，否则子查询带join时出错
            if(!$invoke and $this->_dao_field_list!=''){
                //$field_list=$field_list.$temp_filed_list;
            }
            //把得到的从表的字段列表与主表连在一起。分不同的条件处理
            if($invoke){
                $field_list=$field_list.$temp_filed_list;
            }
            $join_string=$join_string.$this->_dao_join[$k]." ";
        }
        $field_list=rtrim($field_list,',')." ";
        //增加selectAs方法时，只需要自定义字段即可
        if($this->_dao_selectas_sign===true)
        {
            $field_list=$this->_dao_field_list;
        }
        //如果是count操作，则只形成一个count(*) as conter 或是count(指定字段) as counter的字段列表
        if(!empty($this->_dao_count))
        {
            $field_list="count($this->_dao_count) as counter";
        }
        //增加对子查询的处理 luowei2011-02-15
        if($this->_dao_subquery!='')
        {
            $query_string="select  $field_list from (".$this->_dao_subquery.") ";
        }
        else {
            $query_string="select  $field_list from ".$var_main['__table']." ";
        }
        $query_string=$query_string.$join_string;
        if($this->_dao_where)
        {
            $query_string=$query_string." where ".$this->_dao_where." ";
        }
        if($this->_dao_groupby)
        {
            $query_string=$query_string." group by  ".$this->_dao_groupby." ";
        }
        if($this->_dao_having)
        {
            $query_string=$query_string." having  ".$this->_dao_having." ";
        }
        if($this->_dao_orderby){
            //在导入数据时，部分表的排序字段为Null导致列表错乱，数据假重复，增加一个排序条件
            //$temp_string=' order by '.$this->_dao_orderby;
            $temp_string=' order by '.$this->_dao_orderby;
            if(stripos($temp_string,'nulls last')===false)
            {
                $temp_string.=' nulls last';
            }
            $query_string=$query_string.$temp_string;
        }
        //如果有limit操作
        if($this->_dao_limit_number)
        {
            $tempLimitNumber=$this->_dao_limit_number+$this->_dao_limit_start;
            //增加oracle结果集缓存提示 whx 2017-2-11
            $query_string="select /*+ result_cache*/ * from (select A.*,ROWNUM as RN from ($query_string) A where ROWNUM <= $tempLimitNumber) where RN > $this->_dao_limit_start";
        }
        //将生成成功的sql串写入成员变量_dao_query_string
        $this->_dao_query_string=$query_string;
        //$invoke默认为true，为false时，为一些只需要生成where语句的功能服务。
        if($invoke)
        {
            $this->_dao_rs=$this->oracle_query($query_string);
        }
        return $this;
    }
    /**
     * oracle_query 执行SQL语句
     * @param $query_string SQL语句
     * @return mixed
     * @throws \Exception
     */
    private function oracle_query($query_string,$params=array())
    {
        $params=array_merge($this->_dao_where_params,$params);
        $sql = preg_replace_callback(
            '/[?]/',
            function ($k) use ($params) {
                static $i = 0;
                return sprintf("'%s'", $params[$i++]);
            },
            $this->_dao_query_string
        );
        $this->_dao_cache_key=md5($sql);
        $res=null;
        //调试
        $this->_debug_log($query_string,$params);
        //读取全局配置
        $expire_time=conf::get('system.cache_sql_time');
        $expire_time=$expire_time?$expire_time:10;
        $cache_conf=array('expire_time'=>$expire_time);
        $cache_conf=array_merge(conf::get('system.cache_conf'),$cache_conf);
        //初始化缓存
        cache::init($cache_conf);
        if($this->_dao_cache)
        {
            $res=cache::get($this->_dao_cache_key);
        }
        if($res==null)
        {
            $sth=$this->_dao_conn->prepare($query_string);
            $exec_res=false;
            if(!empty($params))
            {
                $exec_res=$sth->execute($params);
            }
            else
            {
                $exec_res=$sth->execute();
            }
            if($exec_res)
            {
                $res=$sth->fetchAll(PDO::FETCH_ASSOC);
                if($this->_dao_cache)
                {
                    cache::set($this->_dao_cache_key,$res);
                }
            }
            else
            {
                //SQL语句错误
                throw new \Exception('SQL语句错误，请检查！',500);
            }
        }
        return $res;
    }

    /**
     * cache
     * 设置缓存
     * @param int $expire_time
     */
    public function cache($expire_time=3600)
    {
        $this->_dao_cache=true;
        $this->_dao_cache_expire_time=$expire_time;
        return $this;
    }
    /**
     * query 执行自定SQL语句
     * @param $query_string SQL语句
     * @param array $params 要替换的占位符变量数组
     * @throws \Exception
     */
    public function query($query_string,$params=array())
    {
        $this->_dao_query_string=$query_string;
        $this->_dao_user_query=true;
        $this->_dao_rs=$this->oracle_query($query_string,$params);
    }

    /**
     * subSelect 构造子查询
     * @param $type 子查询类型，如in
     * @param $table 子查询的表对象
     * @param string $column 主表关联的字段名称，如uuid in (),这里的uuid即$column
     * @return string
     */
    public function subSelect($type,$table,$column='')
    {
        //以后补对参数有效性的判断
        //如果子表有自定义字段
        if($table->_dao_field_list=='')
        {
            $table->selectAdd($table->__table.".".$column);
        }
        $table->select(false);
        return $this->__table.".".$column.' '.$type.'('.$table->_dao_query_string.')';
    }

    /**
     * find 构造SQL后返回结果集
     * @param bool $autoFetch 为true时，获取第一条记录到对象
     * @throws \Exception
     */
    public function find($autoFetch=false)
    {
        $this->select();
        if($autoFetch)
        {
            $this->fetch();
        }
    }

    /**
     * fetch 从结果集中获取一条记录
     * @return mixed
     */
    public function fetch()
    {
        $res=array_shift($this->_dao_rs);
        if($res!==null)
        {
            //处理结果集到对象
            foreach ($res as $k=>$v)
            {
                $k=strtolower($k);
                if($this->_dao_user_query===false)
                {
                    $left_code=substr($k,0,2);
                    $cols=substr($k,2);
                    //处理对象中存在的表
                    if($left_code=='a_')
                    {
                        //主表
                        $this->$cols=is_resource($v)?stream_get_contents($v):$v;
                    }
                    else
                    {
                        //处理连接查询的从表
                        if(!empty($this->_dao_join_table_alias))
                        {
                            foreach ($this->_dao_join_table_alias as $m=>$n)
                            {
                                if($left_code==$n.'_')
                                {
                                    //从表
                                    $this->_dao_join_table[$m]->$cols=is_resource($v)?stream_get_contents($v):$v;
                                }
                            }
                        }
                    }
                    //处理自定义的字段，全部增加到主对象中
                    if(!empty($this->_dao_filed_array))
                    {
                        //自定义对象取全名，不去掉前缀
                        foreach ($this->_dao_filed_array as $x=>$y)
                        {
                            $y=strtoupper($y);
                            $cols_tmp=strtolower($y);
                            $this->$cols_tmp=is_resource($res[$y])?stream_get_contents($res[$y]):$res[$y];
                        }
                    }
                }
                else
                {
                    //自定义查询
                    $this->$k=is_resource($v)?stream_get_contents($v):$v;
                }
            }
        }
        return $res==null?false:$res;
    }

    /**
     * insert 写入一条记录到数据库
     * @return mixed 返回影响的行数
     * @throws \Exception
     */
    public function insert()
    {
        $var=$this->_dao_vars;
        //生成insert的sql语句
        $query_string="insert into ".$var['__table']."(";
        $columns="";
        $columnsValue=" values(";
        $data=array();
        foreach ($var as $key=>$value)
        {
            //反射形成的表对象属性包括表名，因此生成sql语句时将表名排除，也将没有初值的数据排除
            if(substr($key,0,1)!='_' and $this->$key!=$this->_dao_init_value)
            {
                //处理日期
                if($var['_'.$key.'_type']=='date')
                {
                    $columnsValue=$columnsValue."to_date(?,'yyyy-mm-dd hh24:mi:ss'),";
                }
                else
                {
                    $columnsValue=$columnsValue."?,";
                }
                $columns=$columns.$key.",";

                $data[]=$this->$key;
            }
        }
        //去掉尾部多于的','号
        $columns=rtrim($columns,',').")";
        $columnsValue=rtrim($columnsValue,',').")";
        $query_string=$query_string.$columns.$columnsValue;
        $sth=$this->_dao_conn->prepare($query_string);
        //调试
        $this->_debug_log($query_string,$data);
        if($sth->execute($data))
        {
            $nums=$sth->rowCount();
            $this->_last_insert_id=null;
            //释放游标
            $sth->closeCursor();
            return $nums;
        }
        else
        {
            //SQL语句错误
            throw new \Exception('SQL语句错误，请检查！',500);
        }
    }

    /**
     * create 扩展的insert，只需要传递一个数组，即可插入一条数据
     * @param array $data 关联数组，键为对象属性，值为对应值
     * @return int|mixed 返回本次新增写入的行数
     * @throws \Exception
     */
    public function create($data=array())
    {
        $res=0;
        if(!empty($data))
        {
            foreach ($data as $k=>$v)
            {
                $this->$k=$v;
            }
            $res=$this->insert();
        }
        return $res;
    }

    /**
     * update 更新数据库中的记录
     * @return mixed 返回影响的行数
     * @throws \Exception
     */
    public function update()
    {
        $var=$this->_dao_vars;
        if(empty($this->_dao_where))
        {
            throw new \Exception('update必须要有更新条件才能执行!','500');
        }
        $update_str="update ".$var['__table']." set ";
        $data=array();
        foreach ($var as $key=>$value)
        {
            //反射形成的表对象属性包括表名，因此生成sql语句时将表名排除，也将没有初值的数据排除
            if(substr($key,0,1)!='_' and $this->$key!=$this->_dao_init_value)
            {
                //处理日期
                if($var['_'.$key.'_type']=='date')
                {
                    $update_str=$update_str."$key=to_date(?,'yyyy-mm-dd hh24:mi:ss'),";
                }
                else
                {
                    $update_str=$update_str."$key=?,";
                }
                $data[]=$this->$key;
            }
        }
        //去掉尾部多于的','号
        $update_str=rtrim($update_str,',').' where '.$this->_dao_where;
        //处理where的占位,注意where字句的占位符，不能有引号
        $data=array_merge($data,$this->_dao_where_params);
        $sth=$this->_dao_conn->prepare($update_str);
        //调试
        $this->_debug_log($update_str,$data);
        if($sth->execute($data))
        {
            $nums=$sth->rowCount();
            //释放游标
            $sth->closeCursor();
            return $nums;
        }
        else
        {
            //SQL语句错误
            throw new \Exception('SQL语句错误，请检查！',500);
        }
    }

    /**
     * delete 删除数据库中的记录
     * @return mixed 返回影响的行数
     * @throws \Exception
     */
    public function delete()
    {
        $var=$this->_dao_vars;
        if(empty($this->_dao_where))
        {
            throw new \Exception('delete必须要有删除条件才能执行!','500');
        }
        $query_string="delete from ".$var['__table']."  where ".$this->_dao_where;
        $sth=$this->_dao_conn->prepare($query_string);
        //调试
        $this->_debug_log($query_string,$this->_dao_where_params);
        if($sth->execute($this->_dao_where_params))
        {

            $nums=$sth->rowCount();
            //释放游标
            $sth->closeCursor();
            //初始化当前类的值域
            $this->_initValue();
            return $nums;
        }
        else
        {
            //SQL语句错误
            throw new \Exception('SQL语句错误，请检查！',500);
        }
    }

    /**
     * count 快速获取记录数
     * @param string $column count中的参数，如count('distinct id')
     * @return mixed 返回记录数
     * @throws \Exception
     */
    public function count($column='*')
    {
        $this->_dao_count=$column;
        $this->select();
        //调试
        $this->_debug_log($this->_dao_query_string,$this->_dao_where_params);
        $res=isset($this->_dao_rs[0]['COUNTER'])?$this->_dao_rs[0]['COUNTER']:0;
        return $res;
    }

    /**
     * debug 以异常的方式，输出SQL语句信息
     * @param bool $params
     */
    public function debug($params=true)
    {
        if($params)
        {
            $this->_dao_debug=true;
        }
        return $this;
    }

    /**
     * debugLevel debug的别名
     * @param bool $params
     */
    public function debugLevel($params=true)
    {
        return $this->debug($params=true);
    }

    /**
     * free_statement 释放游标
     */
    public function free_statement()
    {
        if(isset($this->_dao_rs) && $this->_dao_rs)
        {
            //释放游标
            $this->_dao_rs->closeCursor();
        }
    }

    /**
     * _initValue 初始化表对象的数据
     */
    private function _initValue()
    {
        $var=$this->_dao_vars;
        foreach ($var as $key=>$value)
        {
            //数据表字段不能用_开头
            if(substr($key,0,1)!='_')
            {
                $this->$key=$this->_dao_init_value;
            }
        }
    }

    /**
     * _debug_log 获取并输出调试语句
     * @param $sql 占位符的SQL语句
     * @param $params 占位符要替换的数组
     * @throws \Exception
     */
    private function _debug_log($sql,$params)
    {
        if($this->_dao_debug===true)
        {
            $sql = preg_replace_callback(
                '/[?]/',
                function ($k) use ($params) {
                    static $i = 0;
                    return sprintf("'%s'", $params[$i++]);
                },
                $sql
            );
            //记录日志
            $loger=log::init();
            $loger->error($sql);
            debug_code($sql);
        }
    }
    /**
     * 获取whereString
     * _dao_where是private的，不能直接取
     * 主要用于join时，实现对从表记录的过滤
     *
     * @return unknown
     */
    public function getWhereString(){
        return $this->_dao_where;
    }
}
class oracle_connect
{
    private static $instance=array();
    public static function getInstance($host=1){
        $databaseConfig=conf::get('database.db');
        if(isset($databaseConfig[$host]) && is_array($databaseConfig[$host]) && !empty($databaseConfig))
        {
            if(!isset(self::$instance[$host]))
            {
                try
                {
                    self::$instance[$host] = new PDO('oci:dbname=//'.$databaseConfig[$host]['host'].';charset='.$databaseConfig[$host]['charset'],$databaseConfig[$host]['user'],$databaseConfig[$host]['password']);
                }
                catch (PDOException $e)
                {
                    throw new \Exception($e,'500');
                }
            }
            return self::$instance[$host];
        }
        else
        {
            throw new \Exception('还未配置数据库服务器【'.$host.'】的信息，请先配置信息后重试！','500');
        }
    }
}