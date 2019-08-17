<?php
/**
 *
 * 文件说明:
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019-05-14
 * Time: 16:51
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace library;
class code
{
    private $nums;//字符个数
    private $im;
    private $other_nums;//干扰像素的个数
    public $code;//用于验证的字符串
    function __construct($num=4,$other_nums=100)
    {
        $this->nums=$num;
        $this->other_nums=$other_nums;
    }
    private function creatpic()
    {
        //创建画布
        $this->im=imagecreatetruecolor($this->nums*20,25);
        $white=imagecolorallocate($this->im,255,255,255);//白色
        imagefill($this->im,0,0,$white);//填充白背景
    }
    private function getrand()
    {
        //生成随机数
        $string="2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,J,K,L,M,N,P,Q,R,S,T,U,V,W,X,Y,Z";
        $t=array();
        $t=explode(',',$string);
        $s='';
        $s=$t[rand(0,31)];
        return $s;
    }
    private function getrandcolor()
    {
        //生成随机颜色
        $randcolor=imagecolorallocate($this->im,rand(0,255),rand(0,255),rand(0,255));
        return $randcolor;
    }
    private function addstring()
    {
        $font=__SITEROOT.'/library/arial.ttf';
        for ($j=0;$j<$this->nums;$j++)
        {
            $string=$this->getrand();//用于写到图片上
            $this->code.=strtolower($string);//生成待验证的字符串
            imagettftext($this->im, 16 , rand(5,10) , $j*20, 20 ,$this->getrandcolor(),$font,$string);
        }
    }
    private function drawother()
    {
        //生成干扰像素
        for ($i=0;$i<$this->other_nums;$i++)
        {
            imagesetpixel($this->im,rand(0,$this->nums*20),rand(0,25),$this->getrandcolor());
        }
    }
    function draw($type)
    {
        $this->creatpic();
        $this->addstring();
        $this->drawother();
        switch ($type)
        {
            case 'gif':
                {
                    header("Content-type: image/gif");
                    return imagegif($this->im);
                    break;
                }
            case 'jpg':
                {
                    header("Content-type: image/jpeg");
                    return imagejpeg($this->im);
                    break;
                }
            case 'png':
                {
                    header("Content-type: image/png");
                    return imagepng($this->im);
                    break;
                }
            default:
                {
                    return imagegif($this->im);
                    break;
                }
        }

    }
    function __destruct()
    {
        if (isset($this->im))
        {
            imagedestroy($this->im);
        }
    }
}