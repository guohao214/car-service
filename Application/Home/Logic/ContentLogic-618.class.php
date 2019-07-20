<?php
namespace Home\Logic;
use Exception;
use User\Api\UserApi;
/**
 * Class UserLogic
 * @name 用户登录的逻辑处理类*
 * @author sufyan add
 */
class ContentLogic
{
    private $post_data = null;
    private $push_url = '';
    public function __construct($post=null){
        $this->post_data = $post;
        $this->push_url = 'http://192.168.1.139:8080/gtui/demo.php';
    }
    public function org_member($page,$rows,$org_id){
        return $member_list;
    }
    //机构发公告、活动、置顶 推送
    public function send_org_inform($uid,$org_id,$content_id){
        $users = M('member_org')->alias('mo')->field('um.push_id')
                ->join('__UCENTER_MEMBER__ as um on um.id = mo.uid and um.status=1 and um.push_id!=""')
                ->where('mo.org_id='.$org_id.' and mo.uid !='.$uid)
                ->group('mo.uid')
                ->select();
        if(!empty($users)){
            $arr = array('type'=>5,'users'=>$users,'content_id'=>$content_id);
            $data = json_encode($arr);
            curl_data($this->push_url, $data);
        }
    }
    //前一天，提前半小时推送
    public function send_class_inform($type){ 
        $w_0 = date('w');
        if($w_0 == 0){
            $w_0 = 7;
        }
        //$weeks = array('c','周一','周二','周三','周四','周五','周六','周日');
       // $today_w = $weeks[$w_0];//今天周几
        //$tomorrow_w = $w==6 ? $weeks[0] : $weeks[$w+1];//明天周几
        date_default_timezone_set("PRC");           
        if($type == 'day'){
            if($w_0 == 7) $w_0 = 0;
            $condition = 'sch.sort = '.($w_0 + 1);
            $title = '上课通知';
            $body = '亲，记得明天要上培训课咯~';
            $group = 'sch.org_id,sch.group_id';
        }elseif($type == 'hourgo'){//上课 
            $search_time = date("H:i",time() + 60 * 60);     
            $title = '上课通知';
            $body = '亲，还有1小时您的孩子就要上课咯~';
            //$condition = 'sch.week = "'.$today_w.'" and sch.start_time <= "'.$search_time.'" and sch.end_time >="'.$search_time.'"' ;			
            $condition = 'sch.sort = '.$w_0.' and sch.start_time ="'.$search_time.'"' ;
            $group = 'sch.id';
        }elseif($type == 'hour'){//下课 
            $search_time = date("H:i",time() + 30 * 60);     
            $title = '下课通知';
            $body = '亲，还有30分钟您的孩子就要下课啦，快去接TA吧~~';
            //$condition = 'sch.week = "'.$today_w.'" and sch.start_time <= "'.$search_time.'" and sch.end_time >="'.$search_time.'"' ;			
            $condition = 'sch.sort = '.$w_0.' and sch.end_time ="'.$search_time.'"' ;
            $group = 'sch.id';
        }elseif($type == 'minute'){//下课 
            $search_time = date("H:i",time() + 10 * 60);     
            $title = '下课通知';
            $body = '亲，还有10分钟您的孩子就要下课啦，快去接TA吧~~';
            //$condition = 'sch.week = "'.$today_w.'" and sch.start_time <= "'.$search_time.'" and sch.end_time >="'.$search_time.'"' ;			
            $condition = 'sch.sort = '.$w_0.' and sch.end_time ="'.$search_time.'"' ;
            $group = 'sch.id';
        }
        //log_new($condition, 'sendinform');
        $schedule = M('schedule')->alias('sch')->field('sch.id,sch.org_id,sch.group_id')
                //->join('__MEMBER_GROUP__ as gr on sch.group_id = gr.group_id')
                //->join('__UCENTER_MEMBER__ as um on um.uid = gr.uid')
                //->join('__PUSH_HISTORY__ as hist on gr.uid = hist.uid and hist.create_time !="'.date('Y-m-d').'"')
                ->where($condition)->group($group)->select(); 
        //log_new(M('schedule')->getlastsql(),'hour');
        if(!empty($schedule)){
            $new_users = array();
            $group_id = 0;
            $schedulelogic = new \Home\Logic\ScheduleLogic();
            foreach ($schedule as $key=>$val){
                $admin_users = $schedulelogic->get_admin($val['group_id'], $val['org_id'],1);   //班级管理员             
                $admin_users2 = $schedulelogic->get_admin($val['group_id'], $val['org_id'],2);//机构的
                $admin_user_id = array();
                if(!empty($admin_users2)){
                    foreach ($admin_users2 as $kk=>$vv){
                        $admin_user_id[] = $vv['uid'];
                    }
                }
                $admin_str = implode(',', $admin_user_id);
                $group_user_id = array();
                if(!empty($admin_users)){
                    $i = 0;
                    foreach ($admin_users as $kk=>$va){
                        if(!in_array($va['uid'], $admin_user_id)){//班级管理员，不是机构的
                            $group_user_id[] = $va['uid'];                            
                        }                        
                    }                                   
                }
                $group_str = implode(',', $group_user_id);    
                log_new($group_user_id,'hour_admin');
                if(!empty($group_user_id)){//管理员推送
                    if($type == 'day'){
                        $admin_map['id'] = array('in',$group_str);
                        $admin_map['push_id'] = array('neq',"");
                        $admin_map['status'] = 1;
                        $group_admins = M('ucenter_member')->field('push_id')->where($admin_map)->select();
                        $arr_admin = array('type'=>4,'users'=>$group_admins,'is_admin'=>3,'title'=>$title,'body'=>$body,'org_id'=>$val['org_id'],'group_id'=>$val['group_id']);
                        $data = json_encode($arr_admin);
                        
                        curl_data($this->push_url, $data);                    
                    }
                }
                //家长推送
                $map['g.group_id'] = $val['group_id'];
                $map['g.uid'] = array('not in',$admin_str);
                $users = M('member_group')->alias('g')->field('g.uid,um.push_id')
                    ->join('__UCENTER_MEMBER__ as um on um.id = g.uid and um.status=1 and um.push_id!=""')
                   // ->join('__SCHEDULE_REMIND__ as sr on sr.uid = gr.uid and sr.is_close = 0 and sr.schedule_id ='.$val['id'])
                    ->where($map)->select();
                
                if(!empty($users)){
                    foreach ($users as $k=>$v){
                        $r = M('schedule_remind')->where('is_close = 1 and schedule_id='.$val['id'].' and uid='.$v['uid'])->find();
                        if(!empty($r)){
                            array_splice($users, $k,1);
                        }
                    }
                    log_new($users,'hour_user');
                    $arr = array('type'=>4,'users'=>$users,'is_admin'=>4,'title'=>$title,'body'=>$body,'org_id'=>$val['org_id'],'group_id'=>$val['group_id']);
                    $data_user = json_encode($arr);
                    curl_data($this->push_url, $data_user);
                }   
                
            }
        }
    }
    //发送课表推送 给班级所有成员
    public function send_schedule_inform($uid,$group_id,$org_id,$type=0){        
        //$group_users = $this->get_group_push_ids($uid,$group_id, $org_id,1);  
        $schedulelogic = new \Home\Logic\ScheduleLogic();
        $admin_users = $schedulelogic->get_admin($group_id, $org_id,1);//只发班级管理员
        $use_ids = array();
        if(!empty($admin_users)){
            foreach ($admin_users as $key=>$val){
                $use_ids[] = $val['uid'];
            }
        }
        $use_ids[] = $uid;
        $users = M('member_group')->alias('a')
                ->field('um.push_id,a.uid')
                ->join('__UCENTER_MEMBER__ as um on um.id = a.uid and um.id!='.$uid.' and um.push_id!="" and um.status=1')
                ->where('a.group_id='.$group_id)
                ->select();
       //$admin = array();
       $user = array();
	   $i = 0;
        if(!empty($users)){
            foreach ($users as $key=>$val){
                if(!in_array($val['uid'], $use_ids)){//班成成员
                    $user[$i]['push_id'] = $val['push_id'];
                    $i++;                
                }
            }
        }
        $user_str = implode(',', $use_ids);
        //管理员
        $admin = M('ucenter_member')->alias('um')
                ->field('um.push_id')
                ->where('um.id!='.$uid.' and um.id in('.$user_str.') and um.push_id!="" and um.status=1')
                ->select();
        log_new($admin,'ucenter_admin');
        $send_user = M('member')->alias('a')
                ->field('mo.nickname,a.nickname as member_nickname')
                ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                ->where('a.uid='.$uid)->find();
        if(empty($send_user) || empty($send_user['nickname'])){
                $teacher_name = $send_user['member_nickname']?$send_user['member_nickname']:'';
            }else{
                $teacher_name = $send_user['nickname'];
            }
        if(!empty($user)){ //班成成员推送
            $arr = array('type'=>3,'send_user'=>$teacher_name,'users'=>$user,'is_admin'=>4,'org_id'=>$org_id,'group_id'=>$group_id,'type_o'=>$type);
            $data = json_encode($arr);
            curl_data($this->push_url, $data);
        }
        if(!empty($admin)){// 管理员推送
            $arr = array('type'=>3,'send_user'=>$teacher_name,'users'=>$admin,'is_admin'=>3,'org_id'=>$org_id,'group_id'=>$group_id,'type_o'=>$type);
            $data = json_encode($arr);log_new($arr,'ucenter_admin2');
            curl_data($this->push_url, $data);
        }
    }
    //老师发评语的用户发送评语推送
    public function send_comment_inform($uid,$to_uid,$org_id,$content_id,$task_id){        
        $users = M('ucenter_member')->alias('um')
                ->field('um.push_id')                
                ->where('um.id='.$to_uid.' and um.push_id!="" and um.status=1')
                ->select();
        if(!empty($users)){
            $send_user = M('member')->alias('a')
                ->field('mo.nickname,a.nickname as member_nickname')
                ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                ->where('a.uid='.$uid)->find();
            if(empty($send_user) || empty($send_user['nickname'])){
                $teacher_name = $send_user['member_nickname']?$send_user['member_nickname']:'';
            }else{
                $teacher_name = $send_user['nickname'];
            }
            $arr = array('type'=>2,'send_user'=>$teacher_name,'users'=>$users,'content_id'=>$content_id,'task_id'=>$task_id);
            $data = json_encode($arr);
            curl_data($this->push_url, $data);
        }        
    }
    //给班级成员发送作业推送
    public function send_task_inform($uid,$group_id,$org_id,$content_id){
        $group_users = $this->get_group_push_ids($uid,$group_id, $org_id);        
        if(!empty($group_users)){
            $send_user = M('member')->alias('a')
                ->field('mo.nickname,a.nickname as member_nickname')
                ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                ->where('a.uid='.$uid)->find();
            
            if(empty($send_user) || empty($send_user['nickname'])){
                $teacher_name = $send_user['member_nickname']?$send_user['member_nickname']:'';
            }else{
                $teacher_name = $send_user['nickname'];
            }
            $arr = array('type'=>1,'send_user'=>$teacher_name,'users'=>$group_users,'content_id'=>$content_id,'org_id'=>$org_id,'group_id'=>$group_id);
            $data = json_encode($arr);
            curl_data($this->push_url, $data);
        }
    }
    //获取班级成员信息，推送。除去管理员
    private function get_group_push_ids($uid,$group_id,$org_id,$type=0){
        $schedulelogic = new \Home\Logic\ScheduleLogic();
        $admin_users = $schedulelogic->get_admin($group_id, $org_id);
        $use_ids = array();
        if(!empty($admin_users)){
            foreach ($admin_users as $key=>$val){
                $use_ids[] = $val['uid'];
            }
        }
        $use_ids[] = $uid;
        $users = M('member_group')->alias('a')
                ->field('um.push_id,a.uid')
                ->join('__UCENTER_MEMBER__ as um on um.id = a.uid and um.id!='.$uid.' and um.push_id!="" and um.status=1')
                ->where('a.group_id='.$group_id)
                ->select();
        if($type == 1){
            $data = $users;
        }else{
            $data = array();
            if(!empty($users)){
                foreach ($users as $key=>$val){
                    if(!in_array($val['uid'], $use_ids)){
                        $data[$key]['push_id'] = $val['push_id'];
                    }
                }
            }
        }
        return $data;
    }
    public function leave_count_admin($uid,$org_id){
        $count = M('leave_inform')->alias('li')
                ->field('li.id')
                ->join('__LEAVE__ as l on li.leave_id = l.id and l.org_id='.$org_id)
                ->where('li.uid='.$uid.' and li.is_read = 0')->count();
        return intval($count);
    }
    public function leave_count_user($uid,$org_id){
        $count = M('leave')->alias('l')
                ->field('l.id')
                ->where('l.uid='.$uid.' and l.is_read = 0 and l.status = 1 and l.org_id='.$org_id)->count();
        return intval($count);
    }
    public function get_live_list($page,$rows,$org_id,$uid,$state_id){
        //log_new($org_id,'livememeberorg');
        $map['c.status'] = 10;
        $map['c.source_flag'] = 2;
        $map['c.org_id'] = $org_id;
        if($state_id == 2){
            $order_live = 'c.create_time desc';
        }elseif($state_id == 1){
            $order_live = 'c.likes desc';
        }
        if(empty($state_id)){

            $list_live = M('live')->alias('c')
                    ->page($page, $rows)
                    ->field('c.id,c.uid,c.title,c.cover_url,c.group_id,c.create_time,c.play,c.publish,c.comments,c.likes,c.create_time ,c.end_time,m.nickname,m.avatar,d.nickname as org_nickname')
                    ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                    ->join('__MEMBER_ORG__ d on d.uid = c.uid ')
                    ->group('c.id')
                    ->where($map)
                    ->order('c.create_time desc')
                    ->select(); 
        }else{
           $list_live = M('live')->alias('c')
                        ->page($page, $rows)
                        ->field('c.id,c.uid,c.title,c.cover_url,c.create_time,c.group_id,c.play,c.publish,c.comments,c.likes,c.create_time ,c.end_time ,m.nickname,m.avatar,d.nickname as org_nickname')
                        ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                        ->join('__MEMBER_ORG__ d on d.uid = c.uid ')
                        ->group('c.id')
                        ->where($map)
                        ->order('c.likes desc')
                        ->select(); 
        }
        $map_r['c.source_flag'] = 2;
        $map_r['c.org_id'] = $org_id;
        $map_r['d.org_id'] = $org_id;
        if($state_id == 2){
            $order = 'c.add_time desc';
        }elseif($state_id == 1){
            $order = 'c.likes desc';
        }else{
            $order = 'c.add_time desc';
        }
        if(empty($state_id)){   
            $list_live_record = M('live_recorded')->alias('c')
                    ->page($page, $rows)
                    ->field('c.id,c.uid,c.title,c.add_time as create_time,c.start_time,c.cover_url,c.end_time,c.group_id,c.group_id,c.play,c.comments,c.likes,m.avatar,d.nickname ')
                    ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                    ->join('__MEMBER_ORG__ d on d.uid = c.uid ')
                    ->group('c.id')
                    ->where($map_r)
                    ->order('create_time desc')
                    ->select();
        }else{
            $list_live_record = M('live_recorded')->alias('c')
                        ->page($page, $rows)
                        ->field('c.id,c.uid,c.title,c.add_time as create_time,c.cover_url,c.start_time,c.end_time,c.group_id,c.group_id,c.play,c.comments,c.likes,m.avatar,d.nickname')
                        ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                        ->join('__MEMBER_ORG__ d on d.uid = c.uid ')
                        ->group('c.id')
                        ->where($map_r)
                        ->order($order)
                        ->select();
        }

        $list = array();
       //log_new(M('live_recorded')->getlastsql(),'livememeber2');
        $num = 0;
        $likes = M('likes');
        $live_recorded = M('live_recorded');

        if(!empty($list_live)){
            foreach ($list_live as $row){
                if($state_id ==2){
                    $new_row = array('id'=>$row['id'],
                            'uid'=>$row['uid'],'title'=>$row['title'],'description'=>'','comments'=>$row['comments'],'likes'=>$row['likes'],
                            'create_time'=>$row['create_time'],'nickname'=>$row['nickname'],'group_name'=>'',
                            'avatar'=>$row['avatar'],'is_like'=>0,'org_nickname'=>'','is_attention'=>1,'is_type'=>2,'update_timestamp'=>$row['end_time'],'create_timestamp'=>$row['start_time'],'pic'=>array(
                                array('cover_url' => $row['cover_url'],
                                'type' => 'LIVE',
                                // 'flag' =>1,
                                'room_id'=>$row['group_id'],
                                'status'=> 1,
                                'value'=> $row['publish'])
                            ));
                }else{

                    $new_row = array('id'=>$row['id'],
                        'uid'=>$row['uid'],'title'=>$row['title'],'description'=>'','comments'=>$row['comments'],'likes'=>$row['likes'],
                        'create_time'=>$row['create_time'],'nickname'=>$row['nickname'],'group_name'=>'',
                        'avatar'=>$row['avatar'],'is_like'=>0,'org_nickname'=>'','is_attention'=>1,'is_type'=>2,'update_timestamp'=>$row['end_time'],'create_timestamp'=>$row['start_time'],'pic'=>array(
                            array('cover_url' => $row['cover_url'],
                            'type' => 'LIVE',
                            // 'flag' =>1,
                            'room_id'=>$row['group_id'],
                            'status'=> 1,
                            'value'=> $row['publish'])
                        ));
                }
                //直播ID
                $data['uid'] = $uid;
                $data['work_id'] = $row['id'];
                $data['is_live'] = 1;
                if($likes ->where($data)->find()){
                    $new_row['is_like'] = 1;
                }else{
                    $new_row['is_like'] = 0;
                }
                $list[$num++] = $new_row;
            }
        }

        if(!empty($list_live_record)){
             if($state_id ==2){
                $num = 0;
                foreach ($list_live_record as $row){
                    $new_row = array('id'=>$row['id'],
                        'uid'=>$row['uid'],'title'=>$row['title'],'description'=>'','comments'=>$row['comments'],'likes'=>$row['likes'],
                        'create_time'=>$row['create_time'],'nickname'=>$row['nickname'],'group_name'=>'',
                        'avatar'=>$row['avatar'],'is_like'=>0,'org_nickname'=>'','is_attention'=>1,'is_type'=>3,'update_timestamp'=>$row['end_time'],'create_timestamp'=>$row['start_time'],'pic'=>array(
                           array( 'cover_url' => $row['cover_url'],
                            'type' => 'LIVE',
                            // 'flag' =>2,
                            'room_id'=>$row['group_id'],
                            'status'=> 2,
                            'value'=> $row['play']),
                            )
                        );
                    //录播ID
                    $data['uid'] = $uid;
                    $data['work_id'] = $row['id'];
                    $data['is_live'] = 2;
                    if($likes ->where($data)->find()){
                        $new_row['is_like'] = 1;
                    }else{
                        $new_row['is_like'] = 0;
                    }
                    $list[$num++] = $new_row;
                }
             }else{
                foreach ($list_live_record as $row){
                    $new_row = array('id'=>$row['id'],
                        'uid'=>$row['uid'],'title'=>$row['title'],'description'=>'','comments'=>$row['comments'],'likes'=>$row['likes'],
                        'create_time'=>get_short_time($row['create_time']),'nickname'=>$row['nickname'],'group_name'=>'',
                        'avatar'=>$row['avatar'],'is_like'=>0,'org_nickname'=>'','is_attention'=>1,'is_type'=>3,'update_timestamp'=>$row['end_time'],'create_timestamp'=>$row['start_time'],'pic'=>array(
                           array( 'cover_url' => $row['cover_url'],
                            'type' => 'LIVE',
                            // 'flag' =>2,
                            'room_id'=>$row['group_id'],
                            'status'=> 2,
                            'value'=> $row['play']),
                            )
                        );
                    //录播ID
                    $data['uid'] = $uid;
                    $data['work_id'] = $row['id'];
                    $data['is_live'] = 2;
                    if($likes ->where($data)->find()){
                        $new_row['is_like'] = 1;
                    }else{
                        $new_row['is_like'] = 0;
                    }
                    $list[$num++] = $new_row;
                }
             }
        }
        return $list;
    }
    public function get_group_id_list($is_admin,$uid, $org_id,$type = 0){

        if($is_admin == 0){
//            //$map['mg.status'] = 1;
//            //$map['g.is_delete'] = 0;
//            //$map['g.org_id'] = $org_id;
//            $map['mg.uid'] = $uid;
//            //$map['g.uid'] = array('NEQ', $uid);
//            //学生不是创建班级的人
//            $member_group = M('member_group')->alias('mg');
//            $list = $member_group->field('mg.group_id')
//                        //->join('__GROUP__ g on g.id = mg.group_id', 'left')
//                        ->where($map)->group('mg.group_id')->order('mg.group_id desc')
//                        ->select();//找所有的班级
            $list = array();
        }elseif($is_admin == 1 || $is_admin == 2){
            $map['g.is_delete'] = 0;
            $map['g.org_id'] = $org_id;
            //join机构，确保机构存在
            $list = M('group')->alias('g')->field('g.id as group_id')->where($map)->select();//机构创建者下的所有班
        }elseif($is_admin == 3){
            $map['a.type'] = 'GROUP';
            $map['a.uid'] = $uid;
            // var_dump($map);exit;
            if($type == 1){

                $list = M('group')->alias('g')->field('g.id as group_id')->where('is_delete=0 and org_id='.$org_id)->select();//机构创建者下的所有班
            }else{
                $list = M('admin')->alias('a')->field('a.related_id as group_id')->where($map)->select();//班级管理员班
            }
        }elseif($is_admin == 4){
            $map['mg.status'] = 1;
            $map['g.is_delete'] = 0;
            $map['g.org_id'] = $org_id;
            $map['mg.uid'] = $uid;
            $map['g.uid'] = array('NEQ', $uid);
            //学生不是创建班级的人
            $member_group = M('member_group')->alias('mg');
            $list = $member_group->field('mg.group_id')
                        ->join('__GROUP__ g on g.id = mg.group_id', 'left')
                        ->where($map)->group('mg.group_id')->order('mg.group_id desc')
                        ->select();//找所有的班级
        }
        if(empty($list)){
            $group_id[] = -1;
        }else{
            foreach ($list as $k=>$v){
                $group_id[] = $v['group_id'];
            }
        }
        $group_str = implode(',', $group_id);
        return $group_str;
    }
    public function task_id_str($group_id,$uid){
        $task_str = ' c.is_admin=1 and c.status=1 and c.is_find=0 and c.uid='.$uid.' and group_id in ('.$group_id.')';
        $task_cnt = M('content')->alias('c')
                    ->field('c.task_id')
                    ->join('__TASK__ t on t.id = c.task_id')
                    ->where($task_str)
                    ->group('c.task_id')
                    ->select();//管理员发的班级作业: 
        if(!empty($task_cnt)){
            foreach ($task_cnt as $k=>$v){
                $task_id[] = $v['task_id'];
            }
        }else{
            $task_id[] = -1;
        }
        return $task_id;
    }
    public function task_count_admin($is_admin,$uid,$org_id){
        if(empty($org_id)){
            throw new Exception('机构ID不能为空',1210);
        }
        $group_id = $this->get_group_id_list($is_admin, $uid, $org_id);
        $create_time = time();
        $task_str = $this->task_id_str($group_id,$uid);
        
        if(!empty($task_str)){
            $task_str = implode(',', $task_str);
            $joined_str = "c.task_id in (".$task_str.") and c.is_admin=0 and c.status=1 and c.is_read =0 and c.is_find=0 ";
            $joined_task = M('content')->alias('c')->where($joined_str)->count();//上交过的作业 
        }else{
            $joined_task = 0;
        }
        $task['need_mark'] = intval($joined_task);
        return $task;
    }
    
    public function task_count_user($uid,$org_id){
        if(empty($org_id)){
            throw new Exception('机构ID不能为空',1210);
        }
        $map['mg.status'] = 1;
        $map['g.is_delete'] = 0;
        $map['g.org_id'] = $org_id;
        $map['mg.uid'] = $uid;
        $map['g.uid'] = array('NEQ', $uid);
        $member_group = M('member_group')->alias('mg');
        $list = $member_group->field('mg.group_id')
                    ->join('__GROUP__ g on g.id = mg.group_id', 'left')
                    ->where($map)->group('mg.group_id')->order('g.id desc')
                    ->select();//找所有的班级
        if(empty($list)){
            $task_count = 0;
            $task_view = 0;
        }else{
            foreach ($list as $k=>$v){
                $group_id[] = $v['group_id'];
            }
            $group_str = implode(',', $group_id);
            $create_time = time();
            $task_str = 'c.group_id in ('.$group_str.') and c.is_admin=1 and c.status=1 and c.is_find=0';
            
            $task_cnt = M('content')->alias('c')
                                ->field('c.task_id')
                                ->join('__TASK__ t on t.id = c.task_id and t.deadline >= '.$create_time)
                                ->where($task_str)->group('c.task_id')
                                ->select();//管理员发的班级作业:没有到期的作业条数 
								
            if(!empty($task_cnt)){
                foreach ($task_cnt as $k=>$v){
                    $task_id[] = $v['task_id'];
                }
                $task_str = implode(',', $task_id);				
                $joined_str = "c.group_id in (".$group_str.") and c.task_id in (".$task_str.") and c.is_admin=0 and c.status=1 and c.is_find=0 and c.uid=".$uid;
                $joined_task = M('content')->alias('c')->where($joined_str)->group('c.task_id')->select();//上交过的作业 
		log_new(M('content')->getlastsql(),'task');
                $task_count = count($task_cnt) - count($joined_task);//待交的作业
		log_new(count($task_cnt).'d'.count($joined_task),'task222');
                $joined = "task_id in (".$task_str.") and is_delete=0 and is_read=0 and to_uid=".$uid;
                $task_view = M('comment')->where($joined)->count();//老师评语放在comment
            }
        }
        $task['need_submit'] = intval($task_count);//待交的作业
        $task['need_mark'] = intval($task_view);
        return $task;
    }
    public function org_identity($uid, $org_id){

        $group_id = $this->get_group_id_list(3, $uid, $org_id,1);
        $org = new \Home\Controller\OrgnizationController();
        if($org->isOrgOwner($uid, $org_id)){
            $is_admin = 1;
        }elseif($org->isOrgAdmin($uid, $org_id)){
            $is_admin = 2;
        }elseif($org->isGROUP($uid, $org_id,$group_id)) {
            $is_admin = 3;
        }elseif($org->isclass($uid,$group_id)){
            $is_admin = 4;
        }else{
            $is_admin = 0;
        }
        return $is_admin;
    }
    public function check_pubtask(){
        $title = data_isset($this->post_data['title'],'trim');
        if(empty($title)){
            $tag = data_isset($this->post_data['tag'],'trim');
            if(!empty($tag)){
                $title = $tag;//新版机构发作业 不用传标题
            }
        }else{
            $title_len = mb_strlen($title, 'utf-8');
            if($title_len>30 || $title_len<4) {
                throw new Exception('标题字数在4-30个字',1034);
            }
        }
//        log_new($this->post_data,'putask');
//        if(empty($title)){
//            throw new Exception('标题不能为空',1034);
//        }
        
        $description = data_isset($this->post_data['description'],'trim');
        //任务说明不能为空
//        if(empty($description)) {
//            throw new Exception('任务说明不能为空',1051);
//        }
        $group_id = data_isset($this->post_data['group_id'],'intval');
        if(empty($group_id)) {
            // $this->renderFailed('未指定要发到的班级');
            throw new Exception('未指定要发到的班级',1051);
                    // $result = array('status'=>,'info'=>'未指定要发到的班级');
                    // parent::put_post($result);
        }
        $data = array('title'=>$title,'description'=>$description,'group_id'=>$group_id);
        return $data;
    }
    public function hav_content($content_json){
        $is_hav_content = 0;
        if(!empty($content_json)) {
            if(ini_get('magic_quotes_gpc')) {
                $content_json = stripslashes($content_json);
            }
            if(!is_valid_json($content_json)) {
                throw new Exception('json格式不对',1036);
            }
            //json数组为空判断
            $content_arr = json_decode($content_json, TRUE);
			log_new($content_arr,'su');
            if(count($content_arr) > 0) {
                $is_hav_content = 1;
            }
        }
        return $is_hav_content;
    }
    public function create_task($isGroupidExists){//任务标签        
        $create_time = NOW_TIME;
        $tag_id = data_isset($this->post_data['tag_id'],'intval',0);
        if(empty($tag_id)) {
            log_new($tag_id, 'taskk1');
            //是否传自定义标签 20170518
            $tag = data_isset($this->post_data['tag'],'trim');
            if(!empty($tag)){
                $tag_data = array('type'=>'TASK','name'=>$tag,'create_time'=>$create_time,'org_id'=>$isGroupidExists['org_id']);
                $tag_flag = M('tags')->add($tag_data);
                if(!$tag_flag){
                    throw new Exception('添加失败',0);
                }else{
                    $tag_id = M('tags')->getLastInsID();
                }
            }
        }
        $deadline = data_isset($this->post_data['deadline'],'intval');
        log_new($tag_id, 'taskk2');
        if(empty($deadline)) {
            $deadline = $create_time + 86400*5; //截至时间，默认5天过期
        }
        $task_data = array('tag_id'=>$tag_id,'create_time'=>$create_time,'deadline'=>$deadline);
        log_new($task_data, 'taskk3');
        $task_id = M('task')->add($task_data);
        if(empty($task_id)) {
            throw new Exception('添加失败',0);
        }
        $data = array('tag_id'=>$tag_id,'task_id'=>$task_id,'create_time'=>$create_time);
        return $data;
    }    
     /**
     * 根据班级id获取用户列表
     * @param $group_ids 批量班级id，如:1,2,3,4
     */
    private function getMemberByGroupIds($group_ids, $page, $rows, $org_id=0) {
        //$Group = M('member_group');
        //$map['mg.group_id'] = array("in", $group_ids);
        $newlist = array();
        $num = 0;
        //log_new($group_ids,'dd');
        $list = M('member_group')->alias('mg')
                    ->field('mg.uid,m.nickname,m.avatar')
                    ->join('__MEMBER__ m on mg.uid = m.uid')
                    ->order('mg.id desc')
                    ->page($page, $rows)->where('mg.group_id in ('.$group_ids.') and mg.status=1')->select();
        foreach ($list as $key=>$row) {
            $newlist[$num++] = $row['uid'];
        }
        if($org_id > 0){
            $map['mo.org_id'] = $org_id;
            $list = M('member_org')->alias('mo')
                        ->page($page, $rows)
                        ->field('mo.uid,m.nickname,m.avatar')
                        ->join('__MEMBER__ m on mo.uid = m.uid')
                        ->page($page, $rows)
                        ->where($map)->select();
            foreach ($list as $key=>$row) {
                $newlist[$num++] = $row['uid'];
            }
        }
        
        $result = array_values(array_unique ($newlist));
        $uids = implode(',', $result);
        $list = $this->organizationmember($uids);
        return $list;
    }
    
    public function organizationmember($uids){
        $member = M('member');
        $select=$member
            ->field('uid,nickname,avatar')
            ->where('uid in ('.$uids.')')
            ->select();
            return $select;
    }
}
