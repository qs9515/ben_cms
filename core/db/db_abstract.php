<?php
/**
 *
 * 文件说明:
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/3
 * Time: 8:22
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace core\db;
interface db_abstract{
    public function insert();
    public function update();
    public function delete();
    public function select();
    public function find();//用于兼容pear dataobjcet
    public function fetch();
    public function where($where);
    public function orderby($orderby);
    public function limit($start,$number);
    public function groupby($groupby);
    public function having($having);
    public function join($method='inner',$slaver_table,$key_column);
    public function count($column='*');
}