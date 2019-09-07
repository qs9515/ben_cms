<?php
/**
 *
 * 文件说明: 非对称加密类
 *
 * Created by PhpStorm.
 * User: qs9515
 * Date: 2019/9/7
 * Time: 13:38
 *
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
namespace library;

use core\conf;

class rsa
{
    /**
     * 方法名称:exportOpenSSLFile
     * 说明: 生成证书
     * @param string $path
     * @return bool
     */
    public static function exportOpenSSLFile($config,$path=''){
        //windows下config中，必须包含openssl.cnf的实际路径
        $res = openssl_pkey_new($config);
        if ( $res == false ){
            return false;
        }
        //注意,windows下，必须和openssl_pkey_new一样，使用config参数，否则私钥会生成失败
        openssl_pkey_export($res, $private_key, null, $config);
        $public_key = openssl_pkey_get_details($res);
        $public_key = $public_key["key"];
        //存储私钥
        file_put_contents($path."/cert_public.key", $public_key);
        //存储公钥
        file_put_contents($path."/cert_private.pem", $private_key);
        openssl_free_key($res);
    }

    /**
     * 方法名称:getPublicKey
     * 说明: 获取公钥
     * @param $config
     * @param string $path
     * @return false|string
     */
    public static function getPublicKey($config,$path='')
    {
        $private_key_file=$path.'/cert_private.pem';
        $public_key_file=$path.'/cert_public.key';
        $rsa_expire_time=conf::get('system.expire_time');
        if (file_exists($private_key_file) && (time()-filemtime($private_key_file)<$rsa_expire_time))
        {
            //密匙存在，未过期，直接使用
        }
        else
        {
            //重新生成密匙
            rsa::exportOpenSSLFile($config,$path);
        }
        if (!file_exists($public_key_file))
        {
            //重新生成密匙
            rsa::exportOpenSSLFile($config,$path);
        }
        return file_get_contents($public_key_file);
    }

    /**
     * 方法名称:authCode
     * 说明: 加密解密
     * @param $string
     * @param string $operation E加密，D解密
     * @param string $path
     * @return string
     */
    public static function authCode($string, $operation = 'E',$path='') {
        $ssl_public     = file_get_contents($path."/cert_public.key");
        $ssl_private    = file_get_contents($path."/cert_private.pem");
        $pi_key         = openssl_pkey_get_private($ssl_private);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $pu_key         = openssl_pkey_get_public($ssl_public);//这个函数可用来判断公钥是否是可用的
        if( false == ($pi_key || $pu_key) ){
            return '';
        }
        $data = "";
        if( $operation == 'D')
        {
            openssl_private_decrypt(base64_decode($string),$data,$pi_key);//私钥解密
        }
        else{
            openssl_public_encrypt($string, $data, $pu_key);//公钥加密
            $data = base64_encode($data);
        }
        return $data;
    }
}