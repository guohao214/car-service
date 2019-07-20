<?php
/**
 * @todo 上线时间短来不及，重复函数，需要统一整合调用
 * Class GroupController
 * @name 
 */

namespace Home\Controller;
use User\Api\UserApi;


use Exception;
class BrandController extends HomeController {
    
   

    public function createQrcode(){
        require_once APP_PATH . '/Home/Common/phpqrcode.php';
        /*
            参数说明：
            
            d: 二维码对应的网址
            p: 二维码尺寸，可选范围1-10(具体大小和容错级别有关)（缺省值：3）
            m: 二维码白色边框尺寸,缺省值: 0px
            e: 容错级别(errorLevel)，可选参数如下(缺省值 L)：
             - L水平    7%的字码可被修正
             - M水平    15%的字码可被修正
             - Q水平    25%的字码可被修正
             - H水平    30%的字码可被修正
        */
        $content = $_POST["url"];
        $errorLevel = isset($_GET["e"]) ? $_GET["e"] : 'L'; 
        $PointSize = 3; 
        $margin = 4;
        preg_match('/http:\/\/([\w\W]*?)\//si', $content, $matches);

        $filepath = APP_PATH . '/../Public/static/qrcode/' . time();
        echo $filepath;

        \QRcode::png($content, $filepath, $errorLevel, $PointSize, $margin, false);

    }
    
    //获取列表 (小程序接口)
    public function appBrandList(){
        header("Access-Control-Allow-Origin:*");
        $where['disabled'] = 1;
        $brand = M('brand')->field('big_brand_id,logo,cn_name,name')->where($where)->select();      
         
        echo json_encode($brand);
        exit;
    }

    //添加
    public function webAddBrand(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;        
        if(M('brand')->add($data)){
            //echo json_encode(array('status'=>1,'msg'=>'已提交'));
            $res = [
                "code" => 0,
                "data" => $data
            ];
        }else{
            //echo json_encode(array('status'=>0,'msg'=>'提交失败，请重试'));
            $res = [
                "code" => 9001,
                "msg" => '提交失败，请重试',
                "data" => $data
            ];
        }
        echo json_encode($res);
        exit;
    }

    //编辑
    public function webEditBrand(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $big_brand_id = $data['big_brand_id'];
        unset($data['big_brand_id']);

        $where['big_brand_id'] = $big_brand_id;
        $cnt = M('brand')->where($where)->save($data);

        $res = [
            "code" => 0,
            "data" => $cnt
        ];
        echo json_encode($res);
        exit;
    }

    //删除
    public function webDeleteBrand(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $big_brand_id = $data['big_brand_id'];

        $where['big_brand_id'] = $big_brand_id;
        $cnt = M('brand')->where($where)->save(['disabled'=>2]);
        $res = [
            "code" => 0,
            "data" => $data
        ];
        echo json_encode($res);
        exit;
    }

    //获取列表 (后台接口)
    public function webBrandList(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $page = $data['page'];
        $length = $data['length'];

        $where['disabled'] = 1;
        $total = M('brand')->where($where)->order("name asc")->count();
        $list = M('brand')->where($where)->order("name asc")->page($page+1, $length)->select();
        
        $res = [
            "code" => 0,
            "data" => [
                "list" => $list,
                "total" => $total
            ]
        ];
        echo json_encode($res);
        exit;
    }




    public function webUploadImage(){
        header("Access-Control-Allow-Origin:*");
        $file = $_FILES['file'];
        

        $uptypes = [  
            'image/jpg',  
            'image/jpeg',  
            'image/png',  
            'image/pjpeg',  
            'image/gif',  
            'image/bmp',  
            'image/x-png'  
        ];

        $max_file_size=200000000;     //上传文件大小限制, 单位BYTE
        
        $fileName = $file['name'];
        $filetype = $file['type'];
        $filesize = $file['size'];

        try{        
            if(!in_array($filetype, $uptypes)){            // 文件类型判断                
                throw new Exception("文件类型不符!" . json_encode($filetype));
            }
            if($filesize > $max_file_size){                // 文件大小判断
                throw new Exception("文件太大!" . $filesize);
            }

            $dir = "image/";
            if (!is_dir($dir)) {                    //创建路径
                mkdir($dir);
            }
            
            $dir = "image/" . date("Ymd") . "/";
            if (!is_dir($dir)) {                    //创建路径
                mkdir($dir);
            }

            $url = $dir . 't' . time() . '_' . $fileName;
            //当文件存在
            if (file_exists($url)) {
            }else{//当文件不存在
                move_uploaded_file($file["tmp_name"], $url);
            }

            $res = [
                "code" => 0,
                "data" => 'http://' . $_SERVER['SERVER_NAME'] . "/" .$url,
            ];
            echo json_encode($res);
            exit;
        }catch(Exception $ex){
            $res = [
                "code" => 0,
                "data" => $ex->getMessage(),
            ];
            echo json_encode($res);
            exit;
        }

        
    }
}   
