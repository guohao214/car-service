<?php
/**
 * @todo 上线时间短来不及，重复函数，需要统一整合调用
 * Class GroupController
 * @name 群组管理/班级管理
 */

namespace Home\Controller;
use User\Api\UserApi;

use Exception;
class CommissionController extends HomeController {
    //添加
    public function add(){
        try{
            $data = $this->post_origin_data;            
            echo json_encode(array('status'=>1,'msg'=>'添加成功'));
        }catch(Exception $err){
            echo json_encode(array('status'=>101,'msg'=>$err.message));
        }
        exit;
    }

    //编辑
    public function edit(){
        try{
            $data = $this->post_origin_data;
            
            if($goods_image_flag && $goods_group_flag){
                $model->commit();
                echo json_encode(array('status'=>1,'msg'=>'修改成功'));
            }else{
                $model->rollback();
                echo json_encode(array('status'=>0,'msg'=>'修改失败，请重试'));
            }
            
        }catch(Exception $err){
            echo json_encode(array('status'=>101,'msg'=>$err.message));
        }
    }

    //删除
    public function del(){
        $data = $this->post_origin_data;
        $goods_id = $data['goods_id'];
        $where['goods_id'] = $goods_id;
        $map['disabled'] = 2;
        $goods = M('goods')->where($where)->save($map);
        if($goods){
            echo json_encode(array('status'=>1,'msg'=>'删除成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'删除失败'));
        }
        exit;
    }

   
    //获取团列表
    public function appCommissionList(){
        header("Access-Control-Allow-Origin:*");
        $num = $this->post_origin_data['num'] ? $this->post_origin_data['num']  : 100000;
        $where['disabled'] = 1;
        $goods_id = $this->post_origin_data['goods_id'];
        if($goods_id){
            $where['goods_id'] = $goods_id;
        }
        $group = M('group')->where($where)->page(0,$num)->order('group_id desc')->select();

        //echo M('group')->getLastSql();                 
        echo json_encode($group);
        exit;
    }
    
    public function appMyGroup(){
       
    }
    
    
    //拼团用户列表
    public function webCommissionList(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $page = isset($data['page']) ? $data['page'] : 0;
        $length = isset($data['length']) ? $data['length'] : 10;
        $status = isset($data['status']) ? $data['status'] : 'grouping';
        
        $where = "disabled=1";
        //$total = M('commission')->where($where)->order("id desc")->count();
        //$list  = M('commission')->where($where)->order("id desc")->page($page+1, $length)->select();

        $sql = "
            SELECT
                count(1) as total
            FROM(
                SELECT
                    u.uid, u.nickname, u.mobile, count(c.member_id) as member_cnt
                FROM
                    user u            
                LEFT JOIN
                    commission c
                    ON u.uid = c.parent_id
                GROUP BY
                    u.uid  
            ) tmp
            WHERE
                tmp.member_cnt>0
        ";
        $res = M('commission')->query($sql);
        $total = $res[0]['total'];

        $startPos = $page*$length;
        $sql = "
            SELECT
                *
            FROM(
                SELECT
                    u.uid, u.nickname, u.avatarUrl, u.mobile, count(c.member_id) as member_cnt
                FROM
                    user u            
                LEFT JOIN
                    commission c
                    ON u.uid = c.parent_id
                GROUP BY
                    u.uid  
            ) tmp
            WHERE
              tmp.member_cnt>0
            LIMIT
              $startPos, $length
        ";
        $list = M('commission')->query($sql);

        foreach($list as $index=>$item){
            $level = 1;
            $member_cnt = $item['member_cnt'];
            if($member_cnt >= 10){
                $level = 2;
            }else if ($member_cnt >= 100) {
                $level = 3;
            }else if ($member_cnt >= 500) {
                $level = 4;
            }else if ($member_cnt >= 1000) {
                $level = 5;
            }

            $list[$index]['level'] = $level;
        }

        $res = [
            "code" => 0,
            "data" => [
                "list" => $list,
                "total" => $total,
            ]
        ];
        echo json_encode($res);
        exit;
    }

    public function webEditCommission(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $group_id = $data['group_id'];
        $editData = [
            'count'=>$data['count'],
            'price'=>$data['price']
        ];

        M('group')->where(["group_id"=>$group_id])->save($editData);

        $res = [
            "code" => 0,
            "msg"=>"success",
            "data" => ""
        ];
        echo json_encode($res);
        exit;        
    }

    public function webDeleteCommission(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $group_id = $data['group_id'];

        $model = M('group');
        $model->where(["group_id"=>$group_id])->save(['disabled'=>2]);
        $res = [
            "code" => 0,
            'msg'=>'删除成功',
            "data" => $group_id
        ];
        echo json_encode($res);
        exit;
    }


    
}   
