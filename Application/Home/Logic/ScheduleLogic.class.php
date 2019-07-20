<?php
namespace Home\Logic;
use Exception;
use User\Api\UserApi;
/**
 * Class ScheduleLogic
 * @name 课程表的逻辑处理类*
 * @author sufyan add
 */
class ScheduleLogic
{
    private $post_data = null;
    public function __construct($post=null){
        $this->post_data = $post;
    }
    public function group_admin($uid,$org_id){
        $logic = new \Home\Logic\ContentLogic();
        $is_admin = $logic->org_identity($uid,$org_id);
        $group_id = $logic->get_group_id_list($is_admin, $uid, $org_id);
        $data = $this->get_admin($group_id, $org_id);
        return $data;
    }
    //添加默认通知人
    public function add_leaveinform($arr,$admin_group,$uid,$org_id){
        $data['operate_uid'] = $uid;
        $data['create_time'] = time();
        $data['org_id'] = $org_id;   
        $modify = array('is_delete'=>0,'update_time'=>time(),'operate_uid'=>$uid);
        if(!empty($arr)){//机构管理设置
            $data['group_id'] = 0;
            foreach ($arr as $key=>$val){
                $data['uid'] = $val;
                $inform = M('leave_default')->field('id,is_delete')->where('uid='.$val.' and org_id='.$org_id)->find();
                if(empty($inform)){
                    $flag = M('leave_default')->add($data);
                }else{
                    
                    if($inform['is_delete'] == 1)
                        $flag = M('leave_default')->where('id='.$inform['id'])->save($modify);
                }
            }
        }
        if($admin_group == 1){//设置班级管理员
            $data['group_id'] = 1;
            $data['uid'] = 0;
            $inform = M('leave_default')->field('id,is_delete')->where('uid=0 and group_id=1 and org_id='.$org_id)->find();
            if(empty($inform)){
                $flag = M('leave_default')->add($data);
            }else{
                if($inform['is_delete'] == 1)
                $flag = M('leave_default')->where('id='.$inform['id'])->save($modify);
            }
        }
        if($flag == 0){
            throw new Exception('添加失败',0);
        }
    }

    //检查用户是否是机构管理员或创建者
    public function check_leavedefault_auth($uid){
        $org_id = data_isset($this->post_data['org_id'],'intval',0);
        $org = $this->org_isexist($uid, $org_id);//机构是否存在且只有机构创建者或管理员才可以操作
        if($org['uid'] != $uid){
            $admin = M('admin')->where('uid='.$uid.' and type="ORG" and related_id ='.$org_id)->find();
            if(empty($admin)){
                    throw new Exception('只有机构创建者或机构管理员才有权限',1301);
            }
        }
        return $org_id;
    }
    //管理员列表
    public function get_admin($group_id='',$org_id,$type=0){
        $map['a.type'] = 'ORG';
        $map['a.related_id'] = $org_id;
        if($type == 0){
            $org_arr = M('admin')->alias('a')
                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                    ->join('__MEMBER__ as m on m.uid = a.uid')
                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                    ->where($map)->group('a.uid')->select();
            $org_ower = M('orgnization')->alias('a')
                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                    ->join('__MEMBER__ as m on m.uid = a.uid')
                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid')
                    ->where('a.id='.$org_id.' AND mo.org_id = a.id')->group('a.uid')->select();
            
//            $group_ower = M('group')->alias('a')
//                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
//                    ->join('__MEMBER__ as m on m.uid = a.uid')
//                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
//                    ->where('a.id in('.$group_id.')')->group('a.uid')->select();
            $group_arr = M('admin')->alias('a')
                ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                ->join('__MEMBER__ as m on m.uid = a.uid')
                ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                ->where('a.type="GROUP" and a.related_id in('.$group_id.')')
                ->group('a.uid')->select();
        }elseif($type == 2){
            $org_arr = M('admin')->alias('a')
                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                    ->join('__MEMBER__ as m on m.uid = a.uid')
                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                    ->where($map)->group('a.uid')->select();
            $org_ower = M('orgnization')->alias('a')
                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                    ->join('__MEMBER__ as m on m.uid = a.uid')
                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid')
                    ->where('a.id='.$org_id.' AND mo.org_id = a.id')->group('a.uid')->select();
//            $group_ower = M('group')->alias('a')
//                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
//                    ->join('__MEMBER__ as m on m.uid = a.uid')
//                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
//                    ->where('a.id in('.$group_id.')')->group('a.uid')->select();
        }elseif($type == 1){
            $group_arr = M('admin')->alias('a')
                ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                ->join('__MEMBER__ as m on m.uid = a.uid')
                ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                ->where('a.type="GROUP" and a.related_id in('.$group_id.')')
                ->group('a.uid')->select();
        }elseif($type == 4){
            $org_arr = M('admin')->alias('a')
                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                    ->join('__MEMBER__ as m on m.uid = a.uid')
                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid and mo.org_id ='.$org_id)
                    ->where($map)->group('a.uid')->select();
            $org_ower = M('orgnization')->alias('a')
                    ->field('m.uid,m.avatar,mo.nickname,m.nickname as member_nickname')
                    ->join('__MEMBER__ as m on m.uid = a.uid')
                    ->join('__MEMBER_ORG__ as mo on mo.uid = a.uid ')
                    ->where('a.id='.$org_id.' AND mo.org_id = a.id')->group('a.uid')->select();
        }
        
        
        $list = array();
        if(!empty($org_arr)){
            $list = $org_arr;
        }
        if(!empty($org_ower)){
            $list = array_merge($list,$org_ower);
        }
        if(!empty($group_arr)){
            $list = array_merge($list,$group_arr);
        }
        if(!empty($group_ower)){
            $list = array_merge($list,$group_ower);
        }
        $newlist = array();
        $data = array();
        $i = 0;
        foreach ($list as $key=>$val){            
            if(!in_array($val['uid'], $newlist)){
                if(empty($val['nickname'])){
                    $data[$i]['nickname'] = $val['member_nickname'];
                }else{
                    $data[$i]['nickname'] = $val['nickname'];
                }
                $data[$i]['uid'] = $val['uid'];
                $data[$i]['avatar'] = $val['avatar'];
                $i++;
                $newlist[] = $val['uid'];
            }
        }
        return $data;
    }
    public function check_params($uid){
        $org_id = data_isset($this->post_data['org_id'],'intval',0);
        $org = $this->org_isexist($uid, $org_id);//机构是否存在且只有机构创建者或管理员才可以操作课程表
        if($org['uid'] != $uid){
            $admin = M('admin')->where('uid='.$uid.' and type="ORG" and related_id ='.$org_id)->find();
            if(empty($admin)){
                throw new Exception('只有机构创建者或机构管理员才有权限',1301);
            }
        }
        $group_id = data_isset($this->post_data['group_id'],'intval',0);
        $this->group_isexist($org_id,$group_id);//班级是否存在     
        $schedule_id = data_isset($this->post_data['schedule_id'],'intval',0);
        if($schedule_id > 0){
            $schedule = M('schedule')->where('id='.$schedule_id)->find();
            if(empty($schedule)){
                throw new Exception('所编辑的课程表不存在',1302);
            }
        }
        $start_time = data_isset($this->post_data['start_time'],'trim');
        $end_time = data_isset($this->post_data['end_time'],'trim');
        $week = data_isset($this->post_data['week'],'trim','');
        $teacher_name = data_isset($this->post_data['teacher_name'],'trim','');
        $teacher_tel = data_isset($this->post_data['teacher_tel'],'trim','');
        $classroom = data_isset($this->post_data['classroom'],'trim','');
        if(empty($start_time)){
            throw new Exception('开始时间不能为空',1303);
        }
        if(empty($end_time)){
            throw new Exception('结束时间不能为空',1304);
        }
        if(empty($week)){
            throw new Exception('星期不能为空',1305);
        }
        if(empty($teacher_name)){
            throw new Exception('老师名称不能为空',1306);
        }
        if(empty($teacher_tel)){
            throw new Exception('老师电话不能为空',1307);
        }
        if(empty($classroom)){
            throw new Exception('教室不能为空',1308);
        }
        $arr_week = array('周一'=>1,'周二'=>2,'周三'=>3,'周四'=>4,'周五'=>5,'周六'=>6,'周日'=>7);
        $data = array(
            'uid' => $uid,
            'org_id' => $org_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'week' => $week,
            'sort' => $arr_week[$week],
            'teacher_name' => $teacher_name,
            'teacher_tel' => $teacher_tel,
            'classroom' => $classroom,
            'group_id' => $group_id
        );
        $result = array('data'=>$data,'schedule_id'=>$schedule_id);
        return $result;
    }
    public function check_user_auth($uid){
        $org_id = data_isset($this->post_data['org_id'],'intval',0);
        $this->org_isexist($uid, $org_id);//机构是否存在且只有机构创建者或管理员才可以操作课程表
        $group_id = data_isset($this->post_data['group_id'],'intval',0);
        if(empty($group_id)){            
            $contentlogic = new ContentLogic();
            $group_id = $contentlogic->get_group_id_list(4, $uid, $org_id);
        }
        //$this->group_isexist($org_id,$group_id);//班级是否存在     
        return $group_id;
    }
    public function check_user_remind($uid){
        $schedule_id = data_isset($this->post_data['schedule_id'],'intval',0);
        $schedule = $this->schedule_isexist($schedule_id);
        $is_close = data_isset($this->post_data['is_close'],'intval',0);
        $group = M('member_group')->field('uid')->where('uid='.$uid.' and status = 1 and group_id='.$schedule['group_id'])->find();
        if(empty($group)){
            $admin = M('admin')->where('uid='.$uid.' and type="GROUP" and related_id ='.$schedule['group_id'])->find();
            if(empty($admin)){
                $group_create = M('group')->where('id='.$schedule['group_id'].' and is_delete =0 and uid='.$uid)->find();
                if(empty($group_create))
                    throw new Exception('只有班级成员或班级管理员才能设置开关',1310);
            }
        }
        $data = array('schedule_id'=>$schedule_id,'is_close'=>$is_close,'uid'=>$uid);
        return $data;
    }

    public function get_schedulelist($uid,$group_id){
        $data = M('schedule')->field('id as schedule_id,classroom,start_time,end_time,teacher_name,teacher_tel,week,uid,org_id,group_id')
                ->where('group_id in('.$group_id.')')->order('sort asc,start_time asc')->select();
        if(empty($data)){
            return array();
        }
        foreach ($data as $k=>$v){
            $data[$k]['is_close'] = 0;
            $schedule = M('schedule_remind')->field('is_close,id')->where('uid='.$uid.' and schedule_id='.intval($v['schedule_id']))->find();
            if(!empty($schedule)){
                $data[$k]['is_close'] = $schedule['is_close'];
            }
        }        
        return $data;
    }
    public function check_del_auth($uid){
        $schedule_id = data_isset($this->post_data['schedule_id'],'intval',0);
        $schedule = $this->schedule_isexist($schedule_id);
        $org = $this->org_isexist($uid, $schedule['org_id']);//机构是否存在且只有机构创建者或管理员才可以操作课程表
        if($org['uid'] != $uid){
            $admin = M('admin')->where('uid='.$uid.' and type="ORG" and related_id ='.$schedule['org_id'])->find();
            if(empty($admin)){
                throw new Exception('只有机构创建者或机构管理员才有权限',1301);
            }
        }
        return $schedule_id;
    }
    public function check_leave_params($uid){ 
        $org_id = data_isset($this->post_data['org_id'],'intval',0);
        if($org_id < 0 || $org_id == 0){
            throw new Exception('机构为空',1062);
        }
        $org_logic = new \Home\Logic\ContentLogic();
        $is_admin = $org_logic->org_identity($uid,$org_id);
        if($is_admin!=4){
            throw new Exception('班级成员才能请假',1312);
        }
        $type = data_isset($this->post_data['type'],'intval',1);
        $start_time = data_isset($this->post_data['start_time'],'intval');
        $end_time = data_isset($this->post_data['end_time'],'intval');
        $class_hour = data_isset($this->post_data['class_hour'],'trim');
        $reason = data_isset($this->post_data['reason'],'trim','');
        $inform_uid = data_isset($this->post_data['inform_uid'],'trim');
        if($start_time == 0 || $start_time < 0){
            throw new Exception('开始时间不能为空',1303);
        }
        if($end_time == 0 || $end_time < 0){
            throw new Exception('结束时间不能为空',1304);
        }
        if(empty($class_hour)){
            throw new Exception('课时不能为空',1311);
        }
        $data = array('uid'=>$uid,'org_id'=>$org_id,'type'=>$type,
            'start_time'=>$start_time,'end_time'=>$end_time,'class_hour'=>$class_hour,'reason'=>$reason,'create_time'=> time());
        return $data;
    }
    public function get_leave_list($uid){
        $org_id = data_isset($this->post_data['org_id'],'intval',0);
        if($org_id < 0 || $org_id == 0){
            throw new Exception('机构为空',1302);
        }
        $is_admin = data_isset($this->post_data['is_admin'],'intval',4);
        if($is_admin == 4){
            $list = $this->student_leave($uid,$org_id);
        }elseif($is_admin == 2 || $is_admin == 3 || $is_admin == 1){
            $list = $this->admin_leave($uid,$org_id);
        }
        $type_arr = array(1=>'事假',2=>'病假',3=>'其它');
        $now_time = date('Y.m.d',time());
        if(!empty($list)){
            foreach ($list as $key=>$val){
                if(empty($val['avatar'])){
                    $list[$key]['avatar'] = 'http://vod.doushow.com/dbh_avatar_default.png?v=abc';
                }
                if($is_admin == 4)
                    M('leave')->where('id='.$val['id'].' and uid='.$uid)->save(array('is_read'=>1));//查看之后设置已读
                        else
                    M('leave_inform')->where('leave_id='.$val['id'].' and uid='.$uid)->save(array('is_read'=>1));//查看之后设置已读
                foreach ($val as $k=>$v){
                    if($k == 'start_time' || $k == 'end_time'){
                        $list[$key][$k] = date('Y-m-d',$v);
                        if($k == 'end_time'){
                            $time = strtotime(date('Y-m-d'));
                            if($v < $time){
                               $list[$key]['is_overdue'] = 1;//过期
                            }else{
                                $list[$key]['is_overdue'] = 0; //未过期
                            }
                        }
                    }
                    if($k == 'create_time'){
                        $create_time = date('Y.m.d',$v);
                        if($now_time == $create_time)
                            $list[$key][$k] = date('H:i',$v);
                        else
                            $list[$key][$k] = $create_time;
                    }
                    if($k == 'type'){
                        $list[$key][$k] = $type_arr[intval($v)];
                    }
                }
            }
        }
        return $list;
    }
    private function admin_leave($uid,$org_id){
        $list = M('leave')->alias('l')
                ->field('l.id,m.uid,m.avatar,m.nickname,l.start_time,l.end_time,l.status,l.class_hour,l.status,l.type,l.reason,l.create_time')
                ->join('__MEMBER__ as m on m.uid = l.uid')
                ->join('__LEAVE_INFORM__ as li on li.leave_id = l.id')
                ->where('li.uid='.$uid.' and l.org_id='.$org_id)->order('l.create_time desc')->select();
        return $list;
    }
    private function student_leave($uid,$org_id){
        $list = M('leave')->alias('l')
                ->field('l.id,m.uid,m.avatar,m.nickname,l.start_time,l.end_time,l.status,l.class_hour,l.status,l.type,l.reason,l.create_time')
                ->join('__MEMBER__ as m on m.uid = l.uid')
                ->where('l.uid='.$uid.' and l.org_id='.$org_id)->order('l.create_time desc')->select();
        return $list;
    }
    public function schedule_isexist($schedule_id){
        if($schedule_id == 0 || $schedule_id<0){
            throw new Exception('课程表不存在',1302);
        }
        $schedule = M('schedule')->where('id='.$schedule_id)->find();
        if(empty($schedule)){
            throw new Exception('课程表不存在',1302);
        }
        return $schedule;
    }
    private function group_isexist($org_id,$group_id){
        if($group_id < 0 || $group_id == 0){
            throw new Exception('班级为空',1300);
        }else{
            $group = M('group')->field('id')->where('org_id='.$org_id.' and id='.$group_id)->find();
            if(empty($group)){
                throw new Exception('此机构下的此班级不存在',1309);
            }
        }
    }
    public function org_isexist($uid,$org_id){
        if($org_id < 0 || $org_id == 0){
            throw new Exception('机构为空',1062);
        }else{
            $org = M('orgnization')->field('uid')->where('id='.$org_id)->find();
            if(empty($org)){
                throw new Exception('机构不存在',1206);
            }
        }
        return $org;
    }
}
