<?php
/**
 *
 * 文件说明: 自定义路由文件
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/3/10
 * Time: 16:02
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
//路由定义规则 get,post,delete方法，参数一为访问路径,不能以/结尾，参数二为模型\控制器\动作，支持简单正则表达式
\core\router::get('/index', 'index\index\index');
\core\router::post('/post', 'index\index\post');
\core\router::get('view/(:num)', 'index\index\view');
\core\router::delete('/delete/(:all)', 'index\index\delete');