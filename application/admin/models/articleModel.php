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
use core\db\models\m\ben_article_base;
use core\db\models\m\ben_article_content;
use core\db\models\m\ben_attachment;
use core\db\models\m\ben_tag_article;
use core\db\models\m\ben_tags;
use Overtrue\Pinyin\Pinyin;

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
        $db->find(true);
        return $db;
    }

    /**
     * 方法名称:addAttachment
     * 说明: 写入附件
     * @param $data
     * @return int|mixed
     * @throws \Exception
     */
    static function addAttachment($data)
    {
        $data['created']=$data['updated']=date('Y-m-d H:i:s');
        $db=new ben_attachment("m");
        return $db->create($data);
    }

    /**
     * 方法名称:editAttachementStatus
     * 说明: 更新附件状态
     * @param $aid
     * @return mixed
     * @throws \Exception
     */
    static function editAttachementStatus($attach_uri)
    {
        $db=new ben_attachment("m");
        $db->status=1;
        $db->whereAdd("attach_uri=?",array($attach_uri));
        return $db->update();
    }

    /**
     * 方法名称:addTags
     * 说明: 添加tag
     * @param $data
     * @throws \Exception
     */
    static function addTags($data)
    {
        $res=array();
        if(!empty($data))
        {
            foreach ($data as $k=>$v)
            {
                $v=trim($v);
                $db=new ben_tags("m");
                $db->whereAdd("tag_name=?",array($v));
                if($db->count()==0)
                {
                    $tmp_data=array();
                    $tmp_data['tag_name']=$v;
                    $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
                    $tmp_data['tag_pinyin']=$pinyin->abbr($tmp_data['tag_name']);
                    $tmp_data['created']=$tmp_data['updated']=date('Y-m-d H:i:s');
                    $tmp_data['status']=1;
                    $db=new ben_tags("m");
                    $db->create($tmp_data);
                    $res[]=$db->last_insert_id();
                }
                else
                {
                    $db=new ben_tags("m");
                    $db->whereAdd("tag_name=?",array($v));
                    $db->find(true);
                    $res[]=$db->id;
                }
            }
        }
        return $res;
    }

    /**
     * 方法名称:addArticleTags
     * 说明: 添加文章tag关联
     * @param $aid
     * @param $data
     * @throws \Exception
     */
    static function addArticleTags($aid,$data)
    {
        if(!empty($data))
        {
            foreach ($data as $k=>$v)
            {
                $db=new ben_tag_article("m");
                $db->whereAdd("tid = ?",array($v));
                $db->whereAdd("aid = ?",array($aid));
                if(!$db->count())
                {
                    $tmp_data['aid']=$aid;
                    $tmp_data['tid']=$v;
                    $tmp_data['created']=$tmp_data['updated']=date('Y-m-d H:i:s');
                    $db=new ben_tag_article("m");
                    $db->create($tmp_data);
                }
            }
        }
    }

    /**
     * 方法名称:articleEdit
     * 说明: 文章修改
     * @param $data
     * @param $search
     * @return bool
     */
    static function articleEdit($data,$search)
    {
        $content['content']=$data['content'];
        unset($data['content']);
        //修改文章基础信息
        parent::commEdit("ben_article_base",$data,$search);
        //修改文章内容信息
        parent::commEdit("ben_article_content",$content,array('aid'=>$search['id']));
        return true;
    }

    /**
     * 方法名称:articleAdd
     * 说明: 新增文章
     * @param $data
     * @return mixed
     */
    static function articleAdd($data)
    {
        $content['content']=$data['content'];
        unset($data['content']);
        $content['aid']=parent::commAdd("ben_article_base",$data);
        parent::commAdd("ben_article_content",$content);
        return $content['aid'];
    }

    /**
     * 方法名称:articleDelete
     * 说明: 删除文章
     * @param $ids
     * @throws \Exception
     */
    static function articleDelete($ids)
    {
        $uuids=array();
        if(!is_array($ids))
        {
            $uuids=array($ids);
        }
        else
        {
            $uuids=$ids;
        }
        $succ=0;
        $err=0;
        foreach ($uuids as $k=>$v)
        {
            //逐条处理
            $article=self::commGetDetailById("ben_article_base",$v);
            //删除基础表
            $db=new ben_article_base("m");
            $db->whereAdd("id=?",array($v));
            if($db->delete())
            {
                //删除内容表
                $db_c=new ben_article_content("m");
                $db_c->whereAdd("aid=?",array($v));
                $db_c->delete();
                //删除tag表
                $db_t=new ben_tags("m");
                $db_t->whereAdd("id in (select tid from ben_tag_article where aid=?)",array($v));
                $db_t->whereAdd("id not in (select tid from ben_tag_article where aid!=?)",array($v));
                $db_t->delete();
                //删除关联tags表
                $db_a=new ben_tag_article("m");
                $db_a->whereAdd("aid=?",array($v));
                $db_a->delete();
                //删除附件表
                if($article->head_pic!='')
                {
                    $db_at=new ben_attachment("m");
                    $db_at->whereAdd("attach_uri=?",array($article->head_pic));
                    if ($db_at->delete())
                    {
                        @unlink(__SITEROOT.'/public'.$article->head_pic);
                    }
                }
                $succ++;
            }
            else
            {
                $err++;
            }
        }
        $data['code']='200';
        $data['msg']='删除记录完成，其中成功【'.$succ.'】条，失败【'.$err.'】条！';
        return $data;
    }

    /**
     * 方法名称:getArticleDetail
     * 说明: 获取文章详细
     * @param $uuid
     * @return bool
     * @throws \Exception
     */
    static function getArticleDetail($uuid)
    {
        $data=parent::commGetDetailById("ben_article_base",$uuid);
        $sort=parent::commGetDetailById('ben_sort',$data->sort_id);
        $content=self::getContentByAid($uuid);
        $data->sort_name=$sort->sort_name;
        $data->content=htmlspecialchars_decode($content->content);
        return $data;
    }
}