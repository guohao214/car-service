<?php
namespace Home\Logic;
use Exception;
use User\Api\UserApi;
/**
 * Class ScheduleLogic
 * @name 榜单的逻辑处理类*
 * @author sufyan add
 */
class BillboardLogic
{
    private $post_data = null;
    private $is_delete = 0;
    public function __construct($post=null){
        $this->post_data = $post;
        $this->is_delete = $this->post_data['is_delete'];
    }
    private function array_insert_before($array, $billboard_one, $move_sort) {
//        if (array_key_exists($id, $array)) {
            $new = array();
            $move_from_id = $billboard_one['id'];//当前移动对象ID
//            log_new($array,'array1');
            $move_from_sort = 0;
            $sort = 1;
            foreach($array as $key=>&$val){
                $val['sort'] = $sort;
                $flag = M('billboard_users')->where('id='.$val['id'])->save(array('sort'=>$val['sort']));
                $sort++;
                
                if($val['id'] == $move_from_id){
                    $move_from_sort = $val['sort'];
                }
            }
//            log_new($array,'array2');
            //比如 $move_from_sort是10， $move_sort是1
            if($move_from_sort > $move_sort){//如果排序靠前排，就依次将当前的前一个移后，
                //从开始位置（比如10）减1到结束位置（比如1）
                for($idx=$move_from_sort-1; $idx>$move_sort-1; $idx--){                    
                    if($idx > 0){
                        $array[$idx]['sort'] = $array[$idx-1]['sort'];
                        $flag = M('billboard_users')->where('id='.$array[$idx]['id'])->save(array('sort'=>$array[$idx]['sort']));
                    }                    
                }
            }elseif($move_from_sort < $move_sort){//如果排序靠后排，就依次将当前的后一个移前，
                $num = $index - 1 ;
                
                for($idx=$move_from_sort-1; $idx<$move_sort-1; $idx++){
                    if($idx > 0){
                        $array[$idx]['sort'] = $array[$idx+1]['sort'];
                        $flag = M('billboard_users')->where('id='.$array[$idx]['id'])->save(array('sort'=>$array[$idx]['sort']));
                    }                    
                }
            }
            $flag = M('billboard_users')->where('id='.$billboard_one['id'])->save(array('sort'=>$move_sort));
//            log_new($array,'array3');
            return $flag;
//        }
//        return FALSE;
    }

    public function check_billboard_set($modify_type){
        $billboard = $this->check_billboard_id();
        $data = array();
        if($modify_type == 0){
            $type = 'title';
            $content = $this->check_title();
            $operate_info = '榜单名称修改';
        }elseif($modify_type == 1){
            $type = 'score';
            $content = data_isset($this->post_data['score'],'intval',100);
            $operate_info = '最低上榜积分修改';
        }elseif($modify_type == 2){
            $type = 'max_num';
            $content = data_isset($this->post_data['max_num'],'intval',50);
            $operate_info = '最多显示人数修改';
        }elseif($modify_type == 5){
            $type = 'is_delete';
            $user_id = data_isset($this->post_data['user_id'],'intval');
            if(empty($user_id)){
                throw new Exception('成员ID不能为空',1320);
            }
            $content = data_isset($this->post_data['is_delete'],'intval',1);            
            $operate_info = '删除榜单成员';
        }elseif($modify_type == 3){
            //log_new($this->post_data,'userid');
            $user_id = data_isset($this->post_data['user_id'],'intval');//需要改的用户
            $sort = data_isset($this->post_data['sort'],'intval',0);//改后的排序
            $avatar = data_isset($this->post_data['avatar'],'trim');  //修改头像
            if(empty($user_id)){
                throw new Exception('成员ID不能为空',1320);
            }            
            $b_map['uid'] = $user_id;
            $b_map['billboard_id'] = $billboard['id'];
            $billboard_one = M('billboard_users')->field('id,sort,avatar')->where($b_map)->find();//此成员的排序号和头像
            $modify_id = $billboard_one['id'];
            if(empty($billboard_one)){
                throw new Exception('成员ID不能为空',1320);
            }
            if($sort == 0){//如果为0 就排序到最后一位
                $billboard_users = M('billboard_users')->field('sort')->where('billboard_id='.$billboard['id'].' and is_delete = 0 and uid!='.$user_id)->order('sort desc')->find();
                $new_sort = intval($billboard_users['sort']) + 1;
                $new['sort'] = $new_sort;
                $flag = M('billboard_users')->where('id='.$billboard['id'])->save($new);
            }else{
                $billboard_users = M('billboard_users')->field('id,sort,uid')->where('is_delete = 0 and billboard_id='.$billboard['id'])->order('sort asc')->select();
                $o_billboard_users = $billboard_users;//取得所有的成员排序，重组
                $flag = $this->array_insert_before($o_billboard_users,$billboard_one,$sort);
            }
            if(!empty($avatar)){
                if($billboard_one['avatar'] != $avatar){
                    $data['avatar'] = $avatar;
                    $flag = M('billboard_users')->where('id='.$modify_id)->save($data);
                }     
            }
            $operate_info = '成员信息修改';
        }elseif($modify_type == 4){//设置上榜成员
            $user_id = str_replace('&quot;', '"', data_isset($this->post_data['user_id'],'trim'));            
            $user_id = json_decode($user_id,true); 
            if(empty($user_id)){
                throw new Exception('成员ID不能为空',1320);
            }
            $arr = array();
            foreach ($user_id as $k=>$v){
                $arr[] = $v;
            }
            $arr_str = implode(',', $arr);
            $billboard_users_new = M('billboard_users')->field('sort')->where('billboard_id='.$billboard['id'].' and is_delete = 0')->order('sort desc')->find();
            $sort = intval($billboard_users_new['sort']) + 1;
            
            $err = array();
            foreach ($user_id as $k=>$v){
                $billboard_users = M('billboard_users')->field(TRUE)->where('billboard_id='.$billboard['id'].' and uid ='.$v)->find();               
                if(!empty($billboard_users)){
                    if($billboard_users['is_delete'] == 0){
                        continue;
                    }else{
                        $billboard_users['is_delete'] = 0;
                        $billboard_users['sort'] = $sort;//删除后再重新设置 其排名重新排
                        $billboard_users['update_time'] = time();
                        $b_map['id'] = $billboard_users['id'];
                        $flag = M('billboard_users')->where($b_map)->save($billboard_users);                        
                    }
                }else{
                    $data['billboard_id'] = $billboard['id']; 
                    $data['uid'] = $v;
                    $data['sort'] = $sort;
                    $member = M('member')->field('avatar')->where('uid='.$v)->find();
                    $data['avatar'] = !empty($member['avatar'])?$member['avatar']: C('USER_INFO_DEFAULT.avatar');
                    $data['create_time'] = time();
                    $data['org_id'] = $billboard['org_id'];
                    $flag = M('billboard_users')->add($data);
                }                
                $sort++;
                $err[] = $flag;
            }
            if(in_array(0, $err)){
                $flag = 1;
                $operate_info = '榜单部分成员添加';
            }else{
                $flag = 1;
                $operate_info = '榜单成员添加';
            }
        }
        if($modify_type == 3){            
        }elseif($modify_type == 5){
            $billboard_user = M('billboard_users')->field('id,is_delete')->where('uid='.$user_id.' and billboard_id='.$billboard['id'])->find();
            if(empty($billboard_user)){
                throw new Exception('成员不存在',1320);
            }else{
                if($billboard_user['is_delete'] == $content){
                    throw new Exception('数据未改变',1);
                }else{
                    $data[$type] = $content;
                    $data['update_time'] = time();
                    $flag = M('billboard_users')->where('id='.$billboard_user['id'])->save($data);
                }
            }
            
        }elseif($modify_type == 4){    
        }else{
            if($billboard[$type] == $content){
                throw new Exception('数据未改变',1);
            }else{
                $data[$type] = $content;
                $data['update_time'] = time();
                $flag = M('billboard')->where('id='.$billboard['id'])->save($data);
            }
        }
        $operate = array('flag'=>$flag,'info'=>$operate_info);
        return $operate;
    }
    
    public function check_billboard_id(){
        $billboard_id = data_isset($this->post_data['billboard_id'],'intval',0);
        if(empty($billboard_id)){
            throw new Exception('榜单不存在',1318);
        }
        $billboard = M('billboard')->field('id,title,score,org_id,max_num,num')->where('id='.$billboard_id.' and is_delete=0')->find();
        if(empty($billboard)){
            throw new Exception('榜单不存在',1318);
        }
        return $billboard;
    }

    private function check_title(){
        $title = data_isset($this->post_data['title'],'trim');
        if(empty($title)){
            throw new Exception('榜单名称不能为空',1034);
        }
        $title_len = mb_strlen($title, 'utf-8');
        if($title_len>10 || $title_len<1) {
            throw new Exception('榜单名称字数在1-10个字',1034);
        }
        return $title;
    }
    private function check_cover_url(){
        $cover_url = data_isset($this->post_data['cover_url'],'trim');
        if(empty($cover_url)){
            throw new Exception('封面不能为空',1316);
        }
        return $cover_url;
    }

    //检测添加 删除时的参数
    public function check_billboard($uid){
        $org_id = data_isset($this->post_data['org_id'],'intval');
        $schedule_logic = new ScheduleLogic();
        $org = $schedule_logic->org_isexist($uid,$org_id);
        if($org['uid'] != $uid){//如果不是机构创建者，就查找是不是机构管理员
            $admin = M('admin')->where('uid='.$uid.' and type="ORG" and related_id ='.$org_id)->find();
            if(empty($admin)){
                throw new Exception('只有机构创建者或机构管理员才有权限',1301);
            }
        }
        $billboard_id = data_isset($this->post_data['billboard_id'],'intval',0);
        if($billboard_id > 0){
            $chapter = M('billboard')->where('id='.$billboard_id)->find();
            if(empty($chapter)){
                throw new Exception('榜单不存在',1318);
            }
            $this->is_delete = data_isset($this->post_data['is_delete'],'intval',0);
        }
        $data = array();
        if($this->is_delete != 1){
            $title = $this->check_title();
            $score = data_isset($this->post_data['score'],'intval',100);
            $max_num = data_isset($this->post_data['max_num'],'intval',50);            
            $data = array(
                'uid'=>$uid,       
                'org_id'=>$org_id,
                'score'=>$score,
                'title'=>$title,
                'max_num'=>$max_num,
            );
        }
        return array('data'=>$data,'billboard_id'=>$billboard_id);
    }
    private function set_billboard_user($org_id,$billboard_id){
        $user_id = str_replace('&quot;', '"', data_isset($this->post_data['user_id'],'trim'));
        $user_id = json_decode($user_id,true); 
        if(empty($user_id)){
            throw new Exception('成员ID不能为空',1320);
        }
        $arr = array();
        foreach ($user_id as $k=>$v){
            $arr[] = $v;
        }
        $sort = 1;
        $arr_str = implode(',', $arr);
        foreach ($user_id as $k=>$v){
            $data['billboard_id'] = $billboard_id; 
            $data['uid'] = $v;
            $data['sort'] = $sort;
            $member = M('member')->field('avatar')->where('uid='.$v)->find();
            $data['avatar'] = !empty($member['avatar'])?$member['avatar']: C('USER_INFO_DEFAULT.avatar');
            $data['create_time'] = time();
            $data['org_id'] = $org_id;
            $flag = M('billboard_users')->add($data);
            if($flag)
                $sort++;
        }
    }
    //添加，编辑
    public function operate($params){
        if($this->is_delete == 1){//删除和编辑
            $params['data']['update_time'] = time();
            $map['id'] = $params['billboard_id'];
            if($this->is_delete == 1){//删除
                $params['data']['is_delete'] = 1;
            }
            $flag = M('billboard')->where($map)->save($params['data']); 
            $operate = array('flag'=>$flag,'info'=>'删除');
        }else{//添加
            $params['data']['create_time'] = time();
            $flag = M('billboard')->add($params['data']);
            $operate = array('flag'=>$flag,'info'=>'添加');
            if($flag){
                $params['billboard_id'] = M('billboard')->getLastInsID();
                $billboard = M('billboard')->field('sort')->where('org_id='.$params['data']['org_id'])->order('sort desc')->find();
                if(empty($billboard)){
                    $params_b['sort'] = 1;
                    //$params_b['prev'] = 0;
                }else{
                    $params_b['sort'] = $billboard['sort'] + 1;
                   //$params_b['prev'] = $billboard['sort'];
                }
                $map['id'] = $params['billboard_id'];
                M('billboard')->where($map)->save($params_b); //添加排序
                $operate = array('flag'=>$flag,'info'=>'添加','billboard_id'=>$params['billboard_id']);
                $this->set_billboard_user($params['data']['org_id'],$params['billboard_id']);
            }
        }        
        return $operate;
    }
    
}
