<?php
/**
 *
 * 文件说明:
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019-06-05
 * Time: 9:24
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\index\controller;
use application\index\models\mysqlModel;
use application\index\models\testModel;
use core\controller;
class testController extends controller
{
    public function indexAction()
    {
        echo '<a href="'.BASE_PATH.'index/test/do4/?thread=30&close=true" target="myiframe">do_4 30</a><br>
<a href="'.BASE_PATH.'index/test/do4/?thread=2&close=true" target="myiframe">do_4 2</a><br>
<a href="'.BASE_PATH.'index/test/do6/?thread=30&close=true" target="myiframe">do_6 30</a><br>
<a href="'.BASE_PATH.'index/test/do6/?thread=2&close=true" target="myiframe">do_6 2</a><br>
<iframe src="" name="myiframe" width="600" height="300"></iframe>';
    }
    public function do4Action()
    {
        set_time_limit(0);
        if (isset($_GET["thread"])) {
            $thread = $_GET["thread"];
            $close = $_GET["close"];
        } else {
            $thread = 100;
            $close = true;
        }
        $start = microtime(true);
        $model=new testModel();
        $thread=$thread*100;
        for ($i = 0; $i <= $thread; $i++)
        {
            $model->oracle_test();
        }
        echo $i . "---------------------------------------------------<br>";
        echo microtime(true) - $start;
        echo "<br>";
        echo date("Y-m-d H:i:s",time());
    }
    public function do6Action()
    {

    }
}