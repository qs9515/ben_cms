<?php
/**
 *
 * 文件说明: 文件上传
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/8/14
 * Time: 16:28
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace library;
class upload
{
    private $allowTypes = array('gif', 'jpg', 'png', 'bmp');
    private $uploadPath = null;
    private $maxSize = 20000000;
    private $msgCode = null;
    public $filename = '';

    public function __construct($options = array())
    {
        //取类内的所有变量
        $vars = get_class_vars(get_class($this));
        //设置类内变量
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $vars)) {
                $this->$key = $value;
            }
        }
    }

    public function fileUpload($myfile)
    {
        $name = $myfile['name'];
        $tmpName = $myfile['tmp_name'];
        $error = $myfile['error'];
        $size = $myfile['size'];

        //检查上传文件的大小 or 类型 and 上传的目录
        if ($error > 0) {
            $this->msgCode = $error;
            return false;
        } elseif (!$this->checkType($name)) {
            return false;
        } elseif (!$this->checkSize($size)) {
            return false;
        } elseif (!$this->checkUploadFolder()) {
            return false;
        }

        $newFile = $this->uploadPath . '/' . $this->randFileName($name);

        //复制文件到上传目录
        if (!is_uploaded_file($tmpName)) {
            $this->msgCode = -3;
            return false;
        } elseif (@move_uploaded_file($tmpName, $newFile)) {
            $this->msgCode = 0;
            //var_dump($newFile);
            return $newFile;
        } else {
            $this->msgCode = -3;
            return false;
        }
    }

    /**
     * 检查上传文件的大小有没有超过限制
     *
     * @return boolean
     * @var int $size
     */
    private function checkSize($size)
    {
        if ($size > $this->maxSize) {
            $this->msgCode = -2;
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查上传文件的类型
     *
     * @return boolean
     * @var string $fileName
     */
    private function checkType($fileName)
    {
        $arr = explode(".", $fileName);
        $type = end($arr);
        if (in_array(strtolower($type), $this->allowTypes)) {
            return true;
        } else {
            $this->msgCode = -1;
            return false;
        }
    }

    /**
     * 检查上传的目录是否存在,如不存在尝试创建
     *
     * @return boolean
     */
    private function checkUploadFolder()
    {
        if (null === $this->uploadPath) {
            $this->msgCode = -5;
            return false;
        }

        $this->uploadPath = rtrim($this->uploadPath, '/');
        $this->uploadPath = rtrim($this->uploadPath, '\\');

        if (!file_exists($this->uploadPath)) {
            if (@mkdir($this->uploadPath, 0755,true)) {
                return true;
            } else {
                $this->msgCode = -4;
                return false;
            }
        } elseif (!is_writable($this->uploadPath)) {
            $this->msgCode = -3;
            return false;
        } else {
            return true;
        }
    }

    /**
     * 生成随机文件名
     *
     * @return string
     * @var string $fileName
     */
    private function randFileName($fileName)
    {
        $salt = md5(time());

        list($name, $type) = explode(".", $fileName);

        $newFile = crypt(md5($name), $salt);

        $this->filename = $newFile . '.' . $type;

        return $newFile . '.' . $type;
    }

    /*
    * 取上传的结果和信息
    *
    * @return array
    */
    public function getStatus()
    {
        $messages = array(
            4 => "没有文件被上传",
            3 => "文件只被部分上传",
            2 => "上传文件超过了HTML表单中MAX_FILE_SIZE选项指定的值",
            1 => "上传文件超过了php.ini 中upload_max_filesize选项的值",
            0 => "上传成功",
            -1 => "未允许的类型，只能传图片",
            -2 => "文件过大，上传文件不能超过{$this->maxSize}个字节",
            -3 => "上传失败",
            -4 => "建立存放上传文件目录失败，请重新指定上传目录",
            -5 => "必须指定上传文件的路径"
        );
        return array('error' => $this->msgCode, 'message' => $messages[$this->msgCode]);
    }
}