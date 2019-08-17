<?php
/**
 *
 * 文件说明:
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/2
 * Time: 22:18
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\data\controller;
use core\db\map\start_map;

class dataController extends \core\controller
{
    public function init()
    {
        echo 'init';
    }
    public function indexAction(){
        $map=new start_map();
        exit;
        //todo 后续操作
    }
}