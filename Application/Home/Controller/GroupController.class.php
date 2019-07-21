<?php
/**
 * @todo 上线时间短来不及，重复函数，需要统一整合调用
 * Class GroupController
 * @name 群组管理/班级管理
 */

namespace Home\Controller;
use User\Api\UserApi;

use Exception;
class GroupController extends HomeController {
    //添加
    public function add(){
        try{
            $data = $this->post_origin_data;
            file_put_contents('log.txt', json_encode($data),FILE_APPEND);
            $goods_data = $data['basicInfo'];
            $goods_image_data = [];
            $goods_group_data = $data['groupList'];

            //file_put_contents('thumb.txt', json_encode($value['arr'][0]),FILE_APPEND);
            foreach ($data['photoList'] as $key => $value) {
                if($value['name'] == 'thumb'){
                    $goods_data['thumb'] = $value['arr'][0];                    
                }else if($value['name'] == 'group_thumb'){
                    $goods_data['group_thumb'] = $value['arr'][0];                    
                }else{
                    $type = $value['name'];
                    foreach ($value['arr'] as $k => $v) {
                        $obj = ['type'=>$type,'img' => $v,'goods_id'=>0];
                        array_push($goods_image_data, $obj)    ;             
                    } 
                }                   
            }

            $model = M('goods');
            $model->startTrans();
            $goods_id = $model->add($goods_data);            
            if($goods_id){
                foreach ($goods_image_data as $k => $v) {
                    $goods_image_data[$k]['goods_id'] = $goods_id  ;         
                } 
                foreach ($goods_group_data as $k => $v) {
                    $goods_group_data[$k]['goods_id'] = $goods_id  ;         
                } 

                $goods_image_id = M('goods_image')->addAll($goods_image_data);
                $goods_group_id = M('group')->addAll($goods_group_data);

                if($goods_image_id && $goods_group_id){
                    $model->commit();
                    echo json_encode(array('status'=>1,'msg'=>'添加成功'));
                }else{
                    $model->rollback();
                    echo json_encode(array('status'=>0,'msg'=>'添加失败'));
                }
                
            }else{
                $model->rollback();
                echo json_encode(array('status'=>0,'msg'=>'添加失败'));
            }
            
        }catch(Exception $err){
            echo json_encode(array('status'=>101,'msg'=>$err.message));
        }
    }
    //编辑
    public function edit(){
        try{
            $data = $this->post_origin_data;
            
            $goods_data = $data['basicInfo'];
            $goods_image_data = [];
            $goods_group_data = $data['groupList'];
            $goods_id = $data['goods_id'];
            //file_put_contents('thumb.txt', json_encode($value['arr'][0]),FILE_APPEND);
            foreach ($data['photoList'] as $key => $value) {
                if($value['name'] == 'thumb'){
                    $goods_data['thumb'] = $value['arr'][0];                    
                }else if($value['name'] == 'group_thumb'){
                    $goods_data['group_thumb'] = $value['arr'][0];                    
                }else{
                    $type = $value['name'];
                    foreach ($value['arr'] as $k => $v) {
                        $obj = ['type'=>$type,'img' => $v,'goods_id'=>$goods_id];
                        array_push($goods_image_data, $obj)    ;             
                    } 
                }                   
            }

            foreach ($goods_group_data as $key => $value) {
                $goods_group_data[$key]['goods_id'] = $goods_id;
            }

            $model = M('goods');
            $model->startTrans();
            $where = array('goods_id'=>$goods_id);
            $model->where($where)->save($goods_data);
            
            $del = array('disabled'=>2);
            M('goods_image')->where($where)->save($del);
            M('group')->where($where)->save($del);
            $goods_image_flag = true;
            $goods_group_flag = true;
            if($goods_image_data)
                $goods_image_flag = M('goods_image')->addAll($goods_image_data);
            if($goods_group_data)
                $goods_group_flag = M('group')->addAll($goods_group_data);
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
    public function appGroupList(){
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
    
    /*
    * 我的团队 下级用户 列表
    */
    public function appMyGroupUser(){
        header("Access-Control-Allow-Origin:*");
        $uid = $this->post_origin_data['uid'];   
        $sql = 'select a.user_profile,a.name,a.group_id,a.uid,b.status 
            from group_user a
            left join user b on a.uid = b.uid
            where b.parent_id ='.$uid.' and a.disabled = 1 ';

        $group_user = M('group_user')->query($sql);

        if($group_user){
            $where = [
               'parent_id'=>$uid,
            ];

            $commission = M('commission')->where($where)->getField('member_id');

            foreach ($group_user as $key => $value) {
                $group_user[$key]['is_got'] = 0;
                if(in_array($value['uid'], $commission)){
                    $group_user[$key]['is_got'] = 1;
                }
            }
        }
        


        echo json_encode(array('group_user_list'=>$group_user));
        exit;

    }

    public function appGroupUsers(){
        $group_id = $this->post_origin_data['group_id'];
        if(!$group_id){
            echo json_encode(array('group_user_list'=>[],'num'=>0));
            exit;
        }
        $where = [
            'group_id'=>$group_id,
            'disabled'=>1,
        ];
        $page = $this->post_origin_data['page'];
        $size = $this->post_origin_data['size'];
        $start = ($page-1)* $size;

        //正在拼团的总数
        $group_total = M('group')->where($where)->find();
        $group_user_num = M('group_user')->where($where)->count();
        $group_user = M('group_user')->field('user_profile,name,group_id,uid')->page($start,$size)->where($where)->select();     
        echo json_encode(array('group_user_list'=>$group_user,'num'=>$group_total['count'] - $group_user_num));
        exit;
    }
    //获取成员列表
    public function appGroupUserList(){
        $goods_id = $this->post_origin_data['goods_id'];
        $num = $this->post_origin_data['num'] ? $this->post_origin_data['num']  : 100000;
        $where = [
            'goods_id'=>$goods_id,
            'disabled'=>1,
            'is_group'=>1
        ];
        //正在拼团的总数
        $group_user_total = M('group_user')->where(array('goods_id'=>$goods_id,'disabled'=>1))->count();  
        //正在拼团的团长们
        $group_user = M('group_user')->field('user_profile,name,group_id,uid')->where($where)->select();        
        $_group_user = [];
        if($group_user){
            foreach ($group_user as $k => $v) {
                $group = M('group')->where(array('group_id'=>$v['group_id'],'disabled'=>1))->find();
                $group_user_num = M('group_user')->query('select count(*) as cnt FROM group_user where disabled=1 and group_id='.$v['group_id']);
                if($group){
                    $v['start_date'] = $group['start_date'];
                    $v['end_date'] = $group['end_date'];
                    $v['time'] = '09:00:00';
                    $num = $group_user_num ? $group_user_num[0]['cnt'] : 0;
                    $v['count'] = $group['count'] - $num;
                    $v['price'] = $group['price'];
                    $_group_user[] = $v;
                }
            }
        }
        echo json_encode(array('group_user_list'=>$_group_user,'num'=>$group_user_total));
        exit;
    }
    private function group($goods_id){
        $group = M('group')->where(array('goods_id'=>$goods_id,'disabled'=>1))->select();
        $new_group = [];
        if($group){
            foreach ($group as $value) {
                $index = $value['goods_id'].'_'.$value['group_id'];
                $new_group[$index] = $value;
            }
        }
    }
    //用户参的团
    public function appUserGroup(){

    }
    //添加
    public function appAddGroupUser(){
        $data = $this->post_origin_data; 
        $open_id = $data['open_id'];
        
        $info = M('user')->where(['open_id'=>$open_id])->find();
        $is_group = $data['is_group'];

        if($info){
            $_info = M('group_user')->where(['uid'=>$info['uid'],'disabled'=>1])->count();
            if($_info){
                echo json_encode(array('status'=>0,'msg'=>'抱歉，您已参与或发过拼团！'));
                exit; 
            }else{
                $data['uid'] = $info['uid'];
                $data['user_profile'] = $info['avatarurl'];
            }                      
        }else{
            echo json_encode(array('status'=>0,'msg'=>'用户不在存在！'));
            exit; 
        }

        file_put_contents('thumb.txt', '------'.json_encode($data),FILE_APPEND);
        if(M('group_user')->add($data)){
            echo json_encode(array('status'=>1,'msg'=>'拼团成功，销售顾问将在48小时内联系您！'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'抱歉，拼团失败请重试！'));
        }
    }
    
    //获取详情
    public function goods(){
        $goods_id = $this->post_origin_data['goods_id'];
        $where = [
            'goods_id'=>$goods_id,
            'disabled'=>1
        ];
        file_put_contents('thumb.txt', json_encode($where),FILE_APPEND);
        $goods = M('goods')->where($where)->find();
        $group = M('group')->where($where)->select();
        $goods_image = M('goods_image')->where($where)->select();
        $photoList = [];
        $sendData = [];
        if($goods){
            $sendData['basicInfo'] = $goods;            
            array_push($photoList, ['arr'=>[$goods['thumb']],'name'=>'thumb'],['arr'=>[$goods['thumb']],'name'=>'group_thumb']);
            if($goods_image){
                $name = ['upload_list_1','upload_list_2','upload_list_3','upload_list_4'];
                $upload_list_1 = [];
                $upload_list_2 = [];
                $upload_list_3 = [];
                $upload_list_4 = [];
                foreach ($goods_image as $key => $value) {
                    if($value['type'] == 'upload_list_1'){
                        array_push($upload_list_1,$value['img']);
                    }
                    if($value['type'] == 'upload_list_2'){
                        array_push($upload_list_2,$value['img']);
                    }
                    if($value['type'] == 'upload_list_3'){
                        array_push($upload_list_3,$value['img']);
                    }
                    if($value['type'] == 'upload_list_4'){
                        array_push($upload_list_4,$value['img']);
                    }
                    file_put_contents('thumb.txt', json_encode($value),FILE_APPEND);
                }

                array_push($photoList,
                    ['arr'=>$upload_list_1,'name'=>'upload_list_1'],
                    ['arr'=>$upload_list_2,'name'=>'upload_list_2'],
                    ['arr'=>$upload_list_3,'name'=>'upload_list_3'],
                    ['arr'=>$upload_list_4,'name'=>'upload_list_4']);
            }
            $sendData['photoList'] = $photoList;
            $sendData['groupList'] = $group;            
        }
        echo json_encode($sendData);
        exit;
    }

    //拼团用户添加
    public function groupUserAdd(){
        $data = $this->post_origin_data;        
        if(M('group_user')->add($data)){
            echo json_encode(array('status'=>1,'msg'=>'已提交'));
        }else{
            $model->rollback();
            echo json_encode(array('status'=>0,'msg'=>'提交失败，请重试'));
        }
    }
    
    //拼团用户列表
    public function groupUserList(){
        header("Access-Control-Allow-Origin:*");
        $num = $this->post_origin_data['num'] ? $this->post_origin_data['num']  : 100000;
        $where['disabled'] = 1;
        $group_user = M('group_user')->where($where)->page(0, $num)->order('group_id desc')->select();               
        echo json_encode($group_user);
        exit;
    }

    //拼团用户列表
    public function groupUserOne(){
        header("Access-Control-Allow-Origin:*");
        $group_id = $this->post_origin_data['group_id'] ;
        $where['disabled'] = 1;
        $where['group_id'] = $group_id;
        $group_user = M('group_user')->where($where)->page(0, $num)->order('group_id desc')->select();               
        echo json_encode($group_user);
        exit;
    }

    //拼团用户列表
    public function webGroupList(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $page = isset($data['page']) ? $data['page'] : 0;
        $length = isset($data['length']) ? $data['length'] : 10;
        $status = isset($data['status']) ? $data['status'] : 'grouping';
        
        $where = "disabled=1";
        if($status == 'grouping'){
            $where .= " and end_time >= now()";
        }else if($status == 'end'){
            $where .= " and end_time < now()";
        }
        
        $total = M('group')->where($where)->order("group_id desc")->count();
        $list = M('group')->where($where)->order('group_id desc')->page($page+1, $length)->select();

        foreach($list as $index=>$item){
            $goods_id = $item['goods_id'];
            $goods = M('goods')->where(['goods_id'=>$goods_id])->find();
            $groupUserCnt = M('group_user')->where(['disabled'=>1, 'group_id'=>$item['group_id']])->count();
            $list[$index]['goods_name'] = $goods['goods_name'];
            $list[$index]['status'] = '拼团中';
            $list[$index]['remain_cnt'] = $item['count'] - $groupUserCnt;
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

    public function webEditGroup(){
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

        // $res = [
        //     "code" => 0,
        //     'msg'=>'修改成功',
        //     "data" => ""
        // ];
        // echo json_encode($res);
        // exit;

        //M('group')->where(["group_id"=>$group_id])->save($editData);
    }

    public function webDeleteGroup(){
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
