<?php
namespace Home\Logic;
use Exception;
use User\Api\UserApi;
/**
 * Class ScheduleLogic
 * @name 点名课卡的逻辑处理类*
 * @author sufyan add
 */
class CallRollLogic
{
    private $post_data = null;
    public function __construct($post=null){
        $this->post_data = $post;
    }
    //课卡管理 
    public function lesson_manage($uid,$org_id,$is_admin){
        $is_teacher = data_isset($this->post_data['is_teacher'],'intval',1);
        $page = data_isset($this->post_data['page'],'intval',1);
        if($page>1){
            throw new Exception('没有更多了',1056);
        }
        $contentlogic = new ContentLogic();
        $group_id_list = $contentlogic->get_group_id_list($is_admin, $uid, $org_id);
        $map['l.is_teacher'] = $is_teacher;
        $map['l.org_id'] = $org_id;
        $schedulelogic = new ScheduleLogic();
        $admin_list = $schedulelogic->get_admin($group_id_list, $org_id);//所有管理员
        $admin_uid = array();//管理员列表
        $admin_arr = array();//管理员uid
        $num = 0;
        foreach ($admin_list as $key=>$val){
            $admin_arr[] = $val['uid'];
        }
        if($is_teacher == 0){ //学员列表
            $group_list = $this->group_list($group_id_list,array(),$org_id);
        }
        
        if($is_admin == 1 || $is_admin == 2){            
            if($is_teacher == 1){
                $group_list = $admin_list;//所有管理员
            }
            log_new($group_list,'admin');
        }elseif($is_admin == 3){//班级管理员，只能显示他自己的
            if($is_teacher == 1){
                $group_list = M('member_org')->field('nickname,uid')->where('org_id='.$org_id.' and uid='.$uid)->select();
            } 
        }
        if(empty($group_list)){
            throw new Exception('没有更多了',1056);
        }
        $operation = M('operation')->field('uid')->select();
        $operate_uid = array();
        foreach ($operation as $k=>$v){
            $operate_uid[] = $v['uid'];
        }
        $i = 0;
        $newlist = array();
        foreach ($group_list as $k=>$v){   
            if(in_array($v['uid'], $operate_uid)){
                continue;
            }
            if($is_teacher == 0){
                if(in_array($v['uid'], $admin_arr)){
                    continue;
                }
            }
            $newlist[$i] = $group_list[$k];
            $map['l.uid'] = $v['uid'];
            $lesson = M('lesson')->alias('l')->field('l.total_lessons,l.uid,l.is_teacher')->where($map)->find();
            if(empty($lesson)){
                $newlist[$i]['total_lessons'] = 0;                
            }else{
                $newlist[$i]['total_lessons'] = $lesson['total_lessons'];
            }
            $newlist[$i]['is_teacher'] = $is_teacher;
            $i++;
        }
        $group_list = arraySequence($newlist, 'total_lessons');    
        return $group_list;
    }
    public function callroll_operate($uid,$org_id,$is_entrypt,$is_admin){
        $group_id = data_isset($this->post_data['group_id'],'intval');
        $schedule_id = data_isset($this->post_data['schedule_id'],'intval',0);    
        if(empty($org_id) || empty($group_id)){
            throw new Exception('参数不能为空',1302);
        }
        if($is_entrypt == 0){
            $admin_list = I('admin_list','','');
            $user_list = I('user_list','','');
        }else{
            $user_list = data_isset($this->post_data['user_list'],'trim');//JSON数据学员[{"uid":1,"is_come":1,"is_leave":0},{"uid":2,"is_come":0,"is_leave":0}]
            $admin_list = data_isset($this->post_data['admin_list'],'trim');//JSON数据管理员
        }
        $user_list = str_replace('true', 1,$user_list);
        $user_list = str_replace('false', 0,$user_list);
        $user_arr = json_decode($user_list,true);
        $admin_arr = json_decode($admin_list,true);
        if(!is_array($user_arr) || !is_array($admin_arr) ){
            throw new Exception('JSON数据格式不正确',1313);
        }
        if(empty($user_arr) || empty($admin_arr)){
            throw new Exception('JSON数据不能为空',1302);
        }
        $teachers = json_decode($admin_list,true);
        $teachers_ids = implode(',', $teachers);
        $admin = M('member_org')->field('nickname')->where('org_id='.$org_id.' and uid in('.$teachers_ids.')')->group('uid,org_id')->select();
        $admin_list_name = array();
        foreach ($admin as $k=>$v){
            if($k>2)
                break;
            $admin_list_name[] = $v['nickname'];
        }
        $teachers_name = implode(',', $admin_list_name);
        $come_num = 0;
        $user_uid = array();        
        $map['org_id'] = $org_id;
        foreach($user_arr as $key=>$val){            
            if($val['is_come'] == 1){//计算到的人数
                $come_num++;
                $user_uid[] = $val['uid'];
            }
        }
        $total = count($user_arr);
        $timestamp = data_isset($this->post_data['timestamp'],'intval');//日历的时间
        $lessons = data_isset($this->post_data['lessons'],'trim','0.0');//课时    
        $group = M('group')->field('group_name')->where('id='.$group_id)->find();
        $call_roll_arr = array('org_id'=>$org_id,'group_id'=>$group_id,'group_name'=>$group['group_name'],'schedule_id'=>$schedule_id,'uid'=>$uid,'ratio'=>$come_num.'/'.$total,
            'students'=>$user_list,'teachers'=>$admin_list,'teachers_name'=>$teachers_name,'lessons'=>$lessons,'need_call_time'=>$timestamp,'create_time'=>time());
        $call_roll = M('call_roll');
        $call_roll->startTrans();
        $flag = $call_roll->add($call_roll_arr);
        $lesson_record = array();
        if($flag){
            $arr = array('org_id'=>$org_id,'group_id'=>$group_id,'uid'=>$uid,'group_name'=>$group['group_name'],'schedule_id'=>$schedule_id,'call_roll_id'=>$call_roll->getLastInsID());
            $user_r = $this->add_lesson_record($arr,$lessons,$user_uid,0);//0学员
//            log_new($user_r,'uiser');
            $admin_r = $this->add_lesson_record($arr,$lessons,$admin_arr,1);//老师
            $result = array_merge($user_r['lesson_record'],$admin_r['lesson_record']);//print_r($result);exit; print_r($result['lesson_record']);exit;
            $result_l = array_merge($user_r['lesson'],$admin_r['lesson']);
            $flag_r = M('lesson_record')->addAll($result);// 添加记录成功dbh_lesson_record表
            if($flag_r){                
                foreach ($result_l as $key=>$val){//添加总数dbh_lesson表
                    $map['uid'] = $val['uid'];
                    $map['is_teacher'] = $val['is_teacher'];
                    $lesson_one = M('lesson')->field('id')->where($map)->find();
                    $data_one = $val;
                    if(empty($lesson_one)){                        
                        $data_one['create_time'] = time();
                        $flag_l = M('lesson')->add($data_one);
                    }else{
                        $data_sec['update_time'] = time();
                        $data_sec['total_lessons'] = $val['total_lessons'];
                        $flag_l = M('lesson')->where('id='.$lesson_one['id'])->save($data_sec);
                    }
                    if(!$flag_l){
                        $call_roll->rollback();
                        throw new Exception('提交失败',0);
                    }
//                    $ispush = M('ucenter_member')->field('uid,is_push')->where('uid='.$val['uid'].' and is_push!=""')->find();
//                    if()
                }
                $arr = array('type'=>9,'users'=>$users,'is_admin'=>4,'title'=>$title,'body'=>$body,'org_id'=>$val['org_id'],'group_id'=>$val['group_id']);
                $data_user = json_encode($arr);
                curl_data($this->push_url, $data_user);
                $call_roll->commit();
            }else{
                $call_roll->rollback();
                throw new Exception('提交失败',0);
            }            
        }else{
            $call_roll->rollback();
            throw new Exception('提交失败',0);
        }        
    }
    //整理上课记录
    private function add_lesson_record($arr,$lessons,$user_uid,$is_teacher){
        $lesson_record = array();
        $lesson = array();
        $lessons = $is_teacher?$lessons:-$lessons;
        $map['org_id'] = $arr['org_id'];
        foreach($user_uid as $key=>$val){  //统计用户的课时数
            $new_c = array();
            $map['uid'] = $val;            
            $map['is_teacher'] = $is_teacher;            
            $lessons_one = M('lesson')->field('total_lessons')->where($map)->find();
            if(empty($lessons_one)){
                $total_lessons = $lessons;
            }else{
                $total_lessons = $lessons_one['total_lessons'] + $lessons;
            }
            $arr_one = array('org_id'=>$arr['org_id'],'group_id'=>$arr['group_id'],'group_name'=>$arr['group_name'],'uid'=>$val,'operate_type'=>3,
                'is_teacher'=>$is_teacher,'lessons'=>$lessons,'total_lessons'=>$total_lessons,'call_roll_id'=>$arr['call_roll_id'],'schedule_id'=>$arr['schedule_id'],'create_time'=>time(),'operate_uid'=>$arr['uid']);
            
            $lesson_record[] = $arr_one;
            $lesson[] = array('uid'=>$val,'org_id'=>$arr['org_id'],'is_teacher'=>$is_teacher,'total_lessons'=>$total_lessons);
        }
        $result = array('lesson_record'=>$lesson_record,'lesson'=>$lesson);
        return $result;
    }
    //点名时待选择的管理员和临时插班生列表
    public function user_list($uid,$org_id,$is_admin){
        $group_id = data_isset($this->post_data['group_id'],'intval');
        $user_type = data_isset($this->post_data['user_type'],'intval',0);//0管理员列表，1临时插班学员列表,2非排课点名时的管理员列表
        if(empty($org_id) || empty($group_id)){
            throw new Exception('参数不能为空',1302);
        }
        $schedulelogic = new ScheduleLogic();
        $admin_list = $schedulelogic->get_admin($group_id, $org_id);//所有管理员
        $admin_group_list = $schedulelogic->get_admin($group_id, $org_id,1);//班级管理员
        $admin_uid = array();//管理员列表
        $admin_arr = array();//管理员uid
        $num = 0;
        $operation = M('operation')->field('uid')->select();
        $operate_uid = array();
        foreach ($operation as $k=>$v){
            $operate_uid[] = $v['uid'];
        }
        foreach ($admin_list as $key=>$val){
            $admin_arr[] = $val['uid'];
            if(in_array($val['uid'], $admin_group_list)){
                continue;
            }
            if(in_array($val['uid'], $operate_uid)){//去客服
                continue;
            }
            $admin_uid[$num++] = $val;
        }
        if($user_type == 0){            
            $users_list = $admin_uid;
        }elseif($user_type == 1){        
            $group_list = $this->group_list($group_id,array(),$org_id);
            $group_uid = array();
            if(!empty($group_list)){
                foreach ($group_list as $key=>$val){
                    $group_uid[] = $val['uid'];
                }
            }
            $contentlogic = new ContentLogic();
            $group_id_list = $contentlogic->get_group_id_list($is_admin, $uid, $org_id);//当前用户在机构下的所有班
            $org_list = $this->group_list($group_id_list,array(),$org_id);//机构下班级所有学员
            $users_list = array();
            if(!empty($org_list)){
                $num = 0;
                foreach ($org_list as $key=>$val){
                    if(in_array($val['uid'], $admin_arr)){//去掉管理员
                        continue;
                    }
                    if(in_array($val['uid'], $operate_uid)){//去客服
                        continue;
                    }
                    if(in_array($val['uid'], $group_uid)){//去掉本班学员
                        continue;
                    }
                    $users_list[$num++] = $val;
                }        
            }
        }elseif($user_type == 2){
            $users_list = $admin_list;
        }
        if(empty($users_list)){
            throw new Exception('没有更多了',1056);
        }        
        return $users_list;
    }
    
    //学员课时信息列表
    public function user_lesson($uid,$org_id,$is_entrypt){
        $group_id = data_isset($this->post_data['group_id'],'intval');
        $user_type = data_isset($this->post_data['user_type'],'intval',0);//0班级学员列表，1临时插班学员列表，        
        $timestamp = data_isset($this->post_data['timestamp'],'intval');
        $end_timestamp = strtotime(date('Y-m-d 23:59',$timestamp));
        if(empty($org_id) || empty($group_id) || empty($timestamp)){
            throw new Exception('参数不能为空',1302);
        }
        $schedulelogic = new ScheduleLogic();
        $admin_list = $schedulelogic->get_admin($group_id, $org_id);//所有管理员
        $admin_uid = array();
        foreach ($admin_list as $key=>$val){
            $admin_uid[] = $val['uid'];
        }
        if($user_type == 0){
            $group_list = $this->group_list($group_id,array(),$org_id);
        }elseif($user_type == 1){   
            if($is_entrypt == 0){
                $user_list = I('user_list','','');
            }else{
                $user_list = data_isset($this->post_data['user_list'],'trim');//JSON数据
            }
//            log_new($this->post_data,'userlist');
//            log_new($user_list,'userlist');
            $user_uid = json_decode($user_list,true);
            if(!is_array($user_uid)){
                throw new Exception('插班学员数据格式不正确',1313);
            }            
            $group_list = $this->group_list($group_id,$user_uid,$org_id);
        }
        if(empty($group_list)){
            throw new Exception('没有更多了',1056);
        }
        $num = 0;
        $group_member = array();
        foreach ($group_list as $key=>$val){
            if(in_array($val['uid'], $admin_uid)){//去除管理员
                continue;
            }
            $group_member[$num]['uid'] = $val['uid'];
            $group_member[$num]['nickname'] = $val['nickname'];
            $group_member[$num]['lessons'] = '0.0';//课时数
            $group_member[$num]['is_leave'] = 0;//是否请假
            $group_member[$num]['is_come'] = 0;//
            $leave_count = M('leave')->where('end_time >= '.$timestamp. ' and start_time <='.$end_timestamp.' and org_id='.$org_id.' and uid='.$val['uid'])->count();
            if($leave_count>0){
                $group_member[$num]['is_leave'] = 1;
            }
            $lesson = M('lesson')->field('total_lessons')->where('is_teacher = 0 and org_id='.$org_id.' and uid='.$val['uid'])->find();
            if(!empty($lesson)){
                $group_member[$num]['lessons'] = $lesson['total_lessons'];
            }
            $num++;
        }
        if(empty($group_member)){
            throw new Exception('没有更多了',1056);
        }
        $group_arr = array();
        $group_arr['user_list'] = $group_member;
        $group_arr['admin_list'] = array();
        if($user_type == 0){
            $schedulelogic = new ScheduleLogic();
            $admin_group_list = $schedulelogic->get_admin($group_id, $org_id,1);//班级管理员
            $group_arr['admin_list'] = $admin_group_list;
        }
        return $group_arr;        
    }
    //班级学员
    public function group_list($group_id,$user_uid=array(),$org_id){
        $map_g['mg.status'] = 1;        
        if(!empty($user_uid)){
            $user_str = implode(',', $user_uid);
            $where = 'mg.uid in('.$user_str.')';
        }else{
            $where = 'mg.group_id in('.$group_id.')';
        }
        $map_g['mo.org_id'] = $org_id; 
        $group_list = M('member_group')->alias('mg')
                ->field('mg.uid,mo.nickname,m.avatar')
                ->join('__MEMBER__ m on mg.uid = m.uid')
                ->join('__MEMBER_ORG__ mo on mo.uid = mg.uid')
                ->where($map_g)
                ->where($where)
                ->group('mg.uid')
                ->select();
        $api = new UserApi();
        if(!empty($group_list))
            $group_list = $api->setDefaultAvatar($group_list);
        return $group_list;
    }
    //课时操作
    public function lesson_operate($login_uid,$org_id){        
        $uid = data_isset($this->post_data['uid'],'intval');//待修改的用户
        $is_teacher = data_isset($this->post_data['is_teacher'],'intval',0);//0学员1老师
        $operate_type = data_isset($this->post_data['operate_type'],'intval',1);//1修改（学员，老师都可被修改），2扣课（老师结算，学员扣课）
        $lessons = data_isset($this->post_data['lessons'],'trim','0.0');//1修改时传修改后的课时数
        if(empty($org_id) || empty($uid)){
            throw new Exception('参数不能为空',1302);
        }
        $arr_info = array('a_0_1'=>'学员课时设置','a_0_2'=>'学员上课扣课','a_1_1'=>'老师课时设置','a_1_2'=>'老师结算'); 
        $info = $arr_info['a_'.$is_teacher.'_'.$operate_type];
        $lesson_model = M('lesson');
        $map['uid'] = $uid;
        $map['org_id'] = $org_id;
        $map['is_teacher'] = $is_teacher;
        $pre_lesson = $lesson_model->field('total_lessons,id')->where($map)->find();//如果有记录取现总课时
        if(!empty($pre_lesson)){
            $total_lesson = $pre_lesson['total_lessons'];//课时总数
            if($operate_type == 1){//修改
                $lessons_operate = $lessons - $pre_lesson['total_lessons'];//更改多少课时
            }
        }else{
            $total_lesson = 0;//课时总数 变量
            if($operate_type == 1){//修改
                $lessons_operate = $lessons;//课时的改变量                
            }            
        }
        if($operate_type == 2){//扣课时 课时为负数
            $lessons_operate = -$lessons;
        }
        if($lessons_operate == 0){
            return '暂无改变';
        }
        $total_lesson = $total_lesson + $lessons_operate;
        $lesson_record = array('uid'=>$uid,'operate_uid'=>$login_uid,'lessons'=>$lessons_operate,'total_lessons'=>$total_lesson,
            'operate_type'=>$operate_type,'org_id'=>$org_id,'is_teacher'=>$is_teacher,'create_time'=>time());
//        log_new($lesson_record,'lesson_record_add');
        $lesson_model->startTrans();
        $flag = M('lesson_record')->add($lesson_record);//添加操作记录
        if($flag){
            if(!empty($pre_lesson)){//更改记录
                $lesson_flag = $lesson_model->where('id='.$pre_lesson['id'])->save(array('total_lessons'=>$total_lesson,'update_time'=>time()));
            }else{//第一次添加数据
                $lessson_arr = array('uid'=>$uid,'org_id'=>$org_id,'total_lessons'=>$total_lesson,'is_teacher'=>$is_teacher,'create_time'=>time());
                $lesson_flag = $lesson_model->add($lessson_arr);
            }
            if($lesson_flag){
                $lesson_model->commit();  
                return $info.'成功';
            }else{
                $lesson_model->rollback();
                throw new Exception($info.'失败',0);
            }
        }else{
            $lesson_model->rollback();
            throw new Exception($info.'失败',0);
        }
    }
    //点名的日历列表，是否有课
    public function calendar_schedule($start,$end,$schedule){
        $weekarray=array("日","一","二","三","四","五","六");
        $dt_start = strtotime($start);
        $dt_end = strtotime($end);
        $data = array();
        if(empty($schedule)){
            $is_have_lesson = 0;
        }else{
            $is_have_lesson = 1;
            $week_arr = array();
            foreach ($schedule as $k=>$v){
                $week_arr[] = $v['sort'];
            }
        }
        while ($dt_start<=$dt_end){
            $week = date('w',$dt_start);
            $arr['week'] = $weekarray[$week];
            $arr['timestamp'] = $dt_start;
            $arr['date'] = date('Y-m-d',$dt_start);        
            $arr['day'] = date('j',$dt_start);//不带0 
            $arr['is_have_lesson'] = 0;
            if($is_have_lesson == 1){
                if(in_array($week, $week_arr)){
                    $arr['is_have_lesson'] = 1;
                }
            }
            $data[] = $arr;
            $dt_start = strtotime('+1 day',$dt_start);
        }
        return $data;
    }    
}
