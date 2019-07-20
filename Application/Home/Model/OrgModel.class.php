<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Model;

/**
 * 机构模块
 */
class OrgModel {
    //学生参与视频列表
  public function videosort($map,$order,$page,$rows){
        $select = M('orgnization_video');
        $list = $select->alias('o')
                ->field('o.id,o.title,o.cover_url,o.video_url,l.avatar,m.nickname')
                ->join('__MEMBER_ORG__ m on m.uid = o.uid')
                ->join('__MEMBER__ l on l.uid = o.uid')
                ->limit(($page - 1) * $rows, $rows)
                ->where($map)
                ->group('o.id')
                ->order($order)
                ->select();
        return $list;
  }
// 机构知识点小节视频列表
  public function orgvideolist($map,$uid){
        $map['is_delete'] = 0;
        $chapter_video_id = M('chapter_video');
        $list = $chapter_video_id->field('id,chapter_id,title,description,cover_url,video_url')
                                ->group('id')
                                ->where($map)
                                ->order('create_time asc')
                                ->select();
        $content_logic = new \Home\Logic\ContentLogic();
        $is_admin = $content_logic->org_identity($uid,$map['org_id']);
        if($is_admin == 3){//查班级管理员 管理的班级不加锁                
            $group_id = M('admin')->field('related_id')->where('uid='.$uid.' and type="GROUP"')->select();
            foreach ($group_id as $key=>$val){
                $arr_group[] = $val['related_id'];
            }
        }
        if(empty($list)){
            $list = array();
        }else{
        foreach ($list as $k=>$v){
            if($k == 0){//第一节不加锁
                $list[$k]['is_lock'] = 0;
                continue;
            }
            
            if($is_admin == 1 || $is_admin == 2){
                $list[$k]['is_lock'] = 0;
            }elseif($is_admin == 0){
                $list[$k]['is_lock'] = 1;
                
            }else{
                if(!empty($arr_group)){
                    if(in_array($v['chapter_id'], $new_chapter)){
                        $list[$k]['is_lock'] = 0;
                        continue;
                    }
                    $chapter = M('chapter')->field('open_group')->where('id='.$v['chapter_id'])->find();
                    $open_group = json_decode($chapter['open_group'],true);
                    $array_intersect = array_intersect($arr_group,$open_group);
                    if(!empty($array_intersect)){
                        $new_chapter[] = $v['chapter_id'];
                        $list[$k]['is_lock'] = 0;
                        continue;
                    }
                }
                $a_map['section_id'] = $list[$k-1]['id'];
                $a_map['uid'] = $uid;
                $count = M('orgnization_video')->where($a_map)->count();
                if(intval($count)){
                    $list[$k]['is_lock'] = 0;
                }else{
                    $list[$k]['is_lock'] = 1;
                }
            }
        }
        }
        $list_info['count'] = count($list);
        $list_info['pic'] = $list;
        $org_map['chapter_id'] = $map['chapter_id'];
        $org_map['section_id'] = $map['sort'];
        $org_map['org_id'] = $map['org_id'];
        $org_map['uid'] = $uid;
        $select = M('orgnization_video')->where($org_map)->find();
        return $list_info;
  }
//机构视频列表
  public function orglist($org_id){
        $member_org = M('member_org')->field('uid')->where('org_id='.$org_id)->select();

        foreach ($member_org as $key => $value) {
            $member_org[$key] = $value['uid'];
        }

        $uids = implode(',',$member_org);
        $map['a.org_id'] = $org_id;
        $map['b.uid'] = array('in',$uids);
        $select = M('orgnization_video');
        $list = $select ->alias('a')
                        ->field('a.id,a.uid,a.title,a.cover_url,a.video_url,a.create_time,a.likes,a.comments,b.nickname as org_nickname,c.nickname,c.avatar')
                        ->join('__MEMBER_ORG__ b ON b.uid=a.uid and b.org_id = a.org_id','left')
                        ->join('__MEMBER__ c ON c.uid=a.uid')
                        ->where($map)
                        ->group('a.id')
                        ->order('create_time desc') 
                        ->limit(($page - 1) * $rows, $rows)
                        ->select();
        return $list;

  }
    /**
     *@author lisk 2017年5月17日09:55:33
     *@param  检查是否关注过某用户
     *@param  int $uid 谁关注
     *@param  int $star_uid 关注谁
     *@return JSON
     */
    private function checkFollow($uid, $star_uid) {
        $Follow = M('follow');
        $map['who_follow'] = $uid;
        $map['follow_who'] = $star_uid;
        
        $ret = $Follow->where($map)->field('id')->find();
        if(!$ret['id']) {
            return false;
        }
        return true;
    }
}
