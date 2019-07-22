<?php
/**
 * @todo 上线时间短来不及，重复函数，需要统一整合调用
 */

namespace Home\Controller;
use User\Api\UserApi;

use Exception;
class MemberController extends HomeController {
    //添加
    public function appAddMember(){
        $data = $this->post_origin_data; 
        if(M('member')->add($data)){
            echo json_encode(array('status'=>1,'msg'=>'恭喜您，已提交成功！'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'抱歉，提交失败请重试！'));
        }
    }
    
    //获取列表
    public function appMemberList(){
        header("Access-Control-Allow-Origin:*");
        $num = $this->post_origin_data['num'] ? $this->post_origin_data['num']  : 100000;
        $where['disabled'] = 1;
        $member = M('member')->where($where)->page(0, $num)->order('group_id desc')->select();               
        echo json_encode($member);
        exit;
    }
    
    //获取列表
    public function appMemberScanList(){
        header("Access-Control-Allow-Origin:*");
        $num = $this->post_origin_data['num'] ? $this->post_origin_data['num']  : 100000;
        $where['disabled'] = 1;
        $where['uid'] = $this->post_origin_data['uid'];
        $member_scan = M('member_scan')->where($where)->page(0, $num)->order('id desc')->select();   
        $member_scan_new = [];
        $goods_id = [];
        if($member_scan){
            foreach ($member_scan as $key => $value) {
                if(!in_array($value['goods_id'], $goods_id)){
                    $goods_id[] = $value['goods_id'];
                    $member_scan_new[] = $value;
                }
            }
        }            
        echo json_encode($member_scan_new);
        exit;
    }

    //获取列表  
    public function webMemberList(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $page = $data['page'];
        $length = $data['length'];

 
        $total = M('member')->order("id desc")->count();
        
        $start = $page*$length;
        $sql = "
            SELECT 
                m.id, m.name, m.mobile, m.city, b.name as brand_name
            FROM 
                member m
            LEFT join
                brand b
                ON m.brand_id = b.big_brand_id
            LIMIT 
                $start, $length

        ";
        //$list = $sql;
        $list = M("member")->query($sql);
        
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

    //删除用户
    public function webDeleteMember(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $id = $data['id'];

        $where['id'] = $id;
        $cnt = M('member')->where($where)->save(['status'=>2]);
        $res = [
            "code" => 0,
            "data" => $data
        ];
        echo json_encode($res);
        exit;
    }




}   
