<?php
/**
 *
 * 文件说明: 文章模型
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/18
 * Time: 21:39
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace application\admin\models;
use application\admin\models\baseModel;
use core\db\models\m\ben_article_content;

class articleModel extends baseModel
{
    /**
     * 方法名称:getContentByAid
     * 说明: 根据文章ID获取文章内容
     * @param $aid
     * @throws \Exception
     */
    static function getContentByAid($aid)
    {
        $db=new ben_article_content("m");
        $db->whereAdd("aid='$aid'");
        return $db->find(true);
    }
}