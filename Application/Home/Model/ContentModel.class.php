<?php

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;
use Think\Cache\Driver\Redis;
/**
 * 内容模型
 */
class ContentModel extends Model{
    
    /**
     * 添加内容素材
     * @param int $content_id
     * @param string $content_json
     */
    public function addMaterial($content_id, $content_json) {
        
        $ContentMaterial = M("Content_material");
        $data['content_id'] = $content_id;
        $data['create_time'] = NOW_TIME;
        $data['content_json'] = $content_json;
        return $ContentMaterial->add($data);
    }
    public function commentlist($work_id,$page,$rows){
        $Comment = M('comment');
            $list = $Comment->alias('c')
                            ->page($page, $rows)
                            ->field('m.uid,m.nickname,m.avatar,c.id,c.to_uid,c.content,c.create_time,c.review')
                            ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                            ->where(' c.work_id = '.$work_id)
                            ->order('c.id desc')
                            ->select();
                return $list;
    }
    public function commentlist1($work_id){
        $Comment = M('comment');
            $list1 = $Comment->alias('c')
                            ->page(1, 3)
                            ->field('m.uid,m.nickname,m.avatar,c.id,c.to_uid,c.content,c.create_time,c.review')
                            ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                            ->where('  c.work_id = '.$work_id)
                            ->order('c.review desc')
                            ->select();
                return $list1;
    }
    //动态评论列表 最新  热门 lisk 2017年6月22日16:43:11
    public function commentlist2($work_id,$type,$uid){
        if($type == 1){
            $map = 'c.create_time desc';
        }else{
            $map = 'c.review desc';
            $data['c.review'] = array('EGT','10');
        }
            $data['c.work_id'] = $work_id;
            $data['c.is_delete'] = 0;
        // if(!empty($org)){
        //     $data['c.work_id'] = 0;
        // }
            $data['is_delete'] = 0;
            // var_dump($data);exit;
            $coeee = M('review_info');
            $Comment = M('comment');
            $list = $Comment->alias('c')
                            ->field('m.uid,m.nickname,m.avatar,c.id,c.content,c.create_time,c.review,c.parent_id')
                            ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                            ->where($data)
                            ->order($map)
                            ->select();
            $list['sum'] = count($list);

            // if($type == 2){
            if($flag == 1){
                $count_id = array();
                foreach ($list as $key => $value) {
                    $figure = $value['id'];
                    $review_count = M('review')->where('is_delete = 0 and parent_id='.$figure)->select();
                    $count = count($review_count);
                    if($count > 10){
                        $map_lifo['a.review_id'] = array('EGT',10);
                        $map_lifo['a.is_delete'] = 0;
                        $map_lifo['a.id'] = $figure;
                        $lista = M('review')->alias('a')
                                ->join('__MEMBER__ b ON b.uid=a.uid')
                                ->join('__MEMBER_ORG__ o on o.uid=a.uid')
                                ->field('a.id,a.uid,a.create_time,a.content,a.review_id,o.nickname,b.avatar,a.parent_id')
                                ->where($map_lifo)
                                ->group('a.id')
                                ->order($order)
                                ->select();
                        $count_id[] = $value;
                    }else{
                        continue;
                    }
                }
                $list = $count_id;
            }
            // }
            foreach ($list as $key => $value) {
                if($value['uid'] == $uid){
                    $list[$key]['is_uid'] = 1;
                }else{
                    $list[$key]['is_uid'] = 0;
                }
                // var_dump($list);exit;
                if(!empty($value['parent_id'])){
                    $id = $value['parent_id'];
                    // $ids = $value['id'];
                    $content = $Comment->alias('c')
                                ->field('m.uid,m.nickname,c.id,c.content,c.create_time,c.review,c.parent_id')
                                ->join('__MEMBER__ m on m.uid = c.uid')
                                ->where('is_delete=0 and id='.$id)
                                ->group('c.id')
                                ->find();
                    $content['create_time'] = date('m-d H:i',$content['create_time']);
                    $content['like'] = intval($content['review']);
                    $content['content'] = rawurldecode($content['content']);

                    if($coeee->where($value['id'].'=comment_id and uid='.$uid)->find()){
                        $value['is_like'] = 1;
                    }else{
                        $value['is_like'] = 0;
                    }
                    if($value['uid'] == $uid){
                        $value['is_uid'] = 1;
                    }else{
                        $value['is_uid'] = 0;
                    }
                    if($content['uid'] == $uid){
                        $content['is_uid'] = 1;
                    }else{
                        $content['is_uid'] = 0;
                    }
                    $content['is_new'] = 1;
                    $list[$key]['is_new'] = 1;
                    unset($content['review']);
                    $value['like'] = intval($value['review']);
                    unset($value['review']);
                    $value['content'] = rawurldecode($value['content']);
                    $value['create_time'] = date('m-d H:i',$value['create_time']);
                    $list[$key] = $content;
                    $list[$key]['new_reply'] = $value;
                }else{
                    $list[$key]['is_new'] = 0;
                    $list[$key]['new_reply'] = (object) null;;
                }
                $list[$key]['create_time'] = date('m-d H:i',$value['create_time']);
            }
            $count['comments'] = $list['sum'];
            M('content')->where('id='.$work_id)->save($count);
            return $list;
    }
    /**
     * 获取内容素材列表
     * @param int $uid 登录用户id  lsk 2017年5月18日14:44:25  修改
     * @param array $list 内容数组  $sort为空为机构热门 1为最新分栏
     */
    public function getMaterialList($uid, $list,$arr_info='',$sort) {

        $Api = new UserApi;
        $Content = M('Content_material');
        foreach ($list as $key=>&$row) {
            $row['create_timestamp'] = $row['create_time'];
            if(empty($sort)){
                $row['create_time'] = $row['create_time'];
            }

            //$row['create_time'] = date('Y-m-d H:i', $row['create_time']);
            if($arr_info !== 'org'){
                $row['is_type']="0";
            }
            if($arr_info == 'orge'){
                $row['is_type']="1";
            }
            $result = $Content->field('content_json,update_time')
            ->where(array('content_id'=>$row['id']))->find();
            if($sort == 2){
                $list[$key]['pic'] = array();
            }
            if(!empty($result['content_json'])) {
                $row['update_timestamp'] = $result['update_time'];
                $arr = json_decode($result['content_json'], TRUE);
                $counter = 0;
                if(is_array($arr)) {
                    foreach ($arr as $json_key=>$json_row) {
                        if(empty($json_row['cover_url'])) {
                            unset($json_row);
                            continue;
                        }
                        $row['pic'][$counter]['cover_url'] = $json_row['cover_url'];
                        $row['pic'][$counter]['type'] = strtoupper($json_row['type']);
                        if($row['pic'][$counter]['type'] == 'LIVE') {
                            $row['pic'][$counter]['status'] = $json_row['status'];
                        }
                        if(!empty($json_row['room_id'])) {
                            $row['pic'][$counter]['room_id'] = $json_row['room_id'];
                        }
                        $row['pic'][$counter]['value'] = $json_row['value'];
                        $counter++;

                    }
                }


            }//历史数据展示
            else {
                if($row['is_type'] ==1){

                }else{
                    $result = $Content->field('type,value,cover_url')
                    ->where(array('content_id'=>$row['id'], 'cover_url'=>array('neq', '')))
                    ->limit(3)->select();
                    foreach ($result as $key=>$content) {
                        if($arr_info !== 'org'){

                            $row['pic'][$key]['cover_url'] = $content['cover_url'];
                            $row['pic'][$key]['type'] = $content['type'];
                            // $row['pic'][$key]['type'] = 'VIDEO';
                            $row['pic'][$key]['value'] = $content['value'];
                        }
                    }
                }
            }
            // if($arr_info !== 'org'){
            //     if($arr_info == 'orge'){

            //     }else{
            //         if($uid) {
            //             $is_like = $Api->isLike($uid, $row['id']);
            //             $list[$key]['is_like'] = ($is_like=='false')?0:1;
            //         }
            //     }

            // }else{
            //     if($uid) {
            //         $is_like = $Api->isLike($uid, $row['id']);
            //         $list[$key]['is_like'] = ($is_like=='false')?0:1;
            //     }
            // }
        }
        // var_dump($list);exit;
        $list =  $Api->setDefaultAvatar($list);
        if($sort == 2){
            foreach ($list as $key => $value) {
                unset($list[$key]['create_timestamp']);
                unset($list[$key]['update_timestamp']);
                $list[$key]['create_time'] = date('m-d H:i',$value['create_time']);
            }
        }
        return $list;
    }
    
    //获取详情页素材
    public function getDetailMaterial($detail) {
        $map['content_id'] = $detail['id'];
        $cm = M('Content_material');
        $result = $cm->field('content_json')
        ->where($map)->find();
        
        if(!empty($result['content_json'])) {
            $material_arr = json_decode($result['content_json'], true);
            foreach ($material_arr as $key=>$row) {
                $detail['pic'][$key]['cover_url'] = $row['cover_url'];
                $detail['pic'][$key]['type'] = strtoupper($row['type']);
                if($detail['pic'][$key]['type'] == 'LIVE') {
                    $detail['pic'][$key]['status'] = $row['status'];
                }
                $detail['pic'][$key]['value'] = $row['value'];
            }
        }
        else {
            $result = $cm->field('type,value,cover_url')
            ->where($map)
            ->select();
            
            foreach ($result as $key=>$content) {
                $detail['pic'][$key]['cover_url'] = $content['cover_url'];
                $detail['pic'][$key]['type'] = $content['type'];
                $detail['pic'][$key]['value'] = $content['value'];
            }
        }
        //是否已经参与
        $detail['is_done_task'] = 0;
        if(!empty($detail['deadline'])) {
            if(strtotime(date("Y-m-d", $detail['deadline']))+86400 <= NOW_TIME) {
                $detail['is_done_task'] = 1;
            }
            $detail['deadline'] = date('Y-m-d', $detail['deadline']);
        }
        
        $detail['create_time'] = date('Y-m-d H:i', $detail['create_time']);
        
        return $detail;
    }
    
    //更新素材
    public function updateMaterial($content_id, $content_json) {
        $Content = M('Content_material');
        $data['content_json'] = $content_json;
        if($Content->where(array('content_id'=>$content_id))->find()){}
            else{
                $data['content_id'] = $content_id;
                $data['create_time'] = time();
                if($Content->add($data)){
                    return TRUE;
                }
            }
        $ret = $Content->where(array('content_id'=>$content_id))->save($data);
        if(!$ret) {
            return FALSE;
        }
        return TRUE;
    }
    public function orgnization_video_channel($org_id,$uid,$state_id,$page,$rows){
        $map['c.org_id'] = $org_id;
        $map['c.tags_id'] = $state_id;
        $map['c.is_delete'] = 0;
        $order = 'c.create_time desc';
        $orgnization_video = M('orgnization_video');
            $org_video_info = $orgnization_video->alias('c')
                            ->field('c.id,c.uid,c.title,c.cover_url,c.video_url,c.create_time,c.comments,c.description,c.section_id,c.chapter_id,c.org_id,c.likes,c.examination_id,m.avatar,l.nickname as org_nickname')
                            ->join('__MEMBER_ORG__ l on l.org_id =c.org_id  and l.uid =c.uid') //线上用
                             //->join('__MEMBER_ORG__ l on l.uid =c.uid') //测试用
                            ->join('__MEMBER__ m on m.uid = c.uid')
                            ->where($map)
                            // ->limit(($page - 1) * $rows, $rows)
                            ->order($order)
                            ->group('c.id')
                            ->select(); 
        $review = M('review');
        foreach ($org_video_info as $key => $value) {
                    $org_video_info[$key]['create_time'] = date('m-d H:i',$value['create_time']);
                    $map_ids['uid'] = $uid;
                    $map_ids['orgvideo_id'] = $value['id'];
                    $li = $review->where($map_ids)->find();
                        if(!empty($li)){
                            $org_video_info[$key]['is_like'] = 1;
                        }else{
                            $org_video_info[$key]['is_like'] = 0;
                        }
                    $org_video_info[$key]['is_type'] = 1;
                    $org_video_pic['cover_url'] = $value['cover_url'];
                    $org_video_pic['value'] = $value['video_url'];
                    $org_video_pic['type'] = "VIDEO" ;
                    $org_video_info[$key]['pic']= array($org_video_pic);
                    unset($org_video_info[$key]['cover_url']);
                    unset($org_video_info[$key]['video_url']);
        }
        return $org_video_info;
    }
    //lisk  获取orgnization_video 从新拼接  2017年5月24日10:04:18
   public function orgnization_video($org_id,$uid,$state_id,$page,$rows){
        if($state_id == 1){
            $map = 'c.likes desc';
        }else{
            $map = 'c.create_time desc';
        }
        //排序 

            $orgnization_video = M('orgnization_video');
            $orgnization = M('orgnization');
            $org_video_info = D('orgnization_video_org_video_info')->alias('c')->where('c.org_id='.$org_id)->order($map)->select(); 
            $count_chapter = M('chapter')->where('is_delete=0 and org_id='.$org_id)->count();
            if(!empty($count_chapter)){
                // $orgnization_video_info = D('orgnization_video_orgnization_video_info')->alias('c')->where('c.org_id='.$org_id)->order($map)->select();
                $orgnization_video_info = $orgnization->alias('o')
                                ->field('c.id,c.uid,c.title,c.cover_url,c.video_url,c.create_time,c.comments,c.description,c.section_id,c.chapter_id,c.org_id,c.likes,c.examination_id,m.nickname,m.avatar,l.nickname as org_nickname,r.open_group')
                                //->join('__MEMBER_ORG__ l on l.org_id =c.org_id  and l.uid =c.uid') //线上用
                                ->join('__MEMBER_ORG__ l on l.uid =o.uid') //测试用
                                ->join('__MEMBER__ m on m.uid = o.uid')
                                ->join('__ORGNIZATION_VIDEO__ c on c.org_id = o.id')
                                ->join('__CHAPTER__ r on r.org_id = o.id')
                                ->where('c.is_delete=0 and c.id='.$org_id)
                                // ->where("c.is_delete=0 and l.org_id =c.org_id and c.org_id=".$org_id)
                                // ->limit(($page - 1) * $rows, $rows)
                                ->order($map)
                                ->group('c.id')
                                ->select(); 
            }
            $org_video_info_id = array();
            if(!empty($org_video_info)){
                $org_video_info_id = $org_video_info;
            }
            if(!empty($orgnization_video_info)){
                $org_video_info_id = $orgnization_video_info;
            }
// var_dump($org_video_info_id);exit;
            $org_video_info = $org_video_info_id;
            $org_star = M('org_star');
            $review = M('review');
            $chapter_video = M('chapter_video');
            $info = M('member_group')->field('group_id')->where('status=1 and uid='.$uid)->select();
            $videolist = new OrgModel();
            if($this->isOrgOwner($uid,$org_id)){
                $is_member = 1;
            }elseif($this->isOrgAdmin($uid,$org_id,ORG)){
                $is_member = 1;
            }
                foreach ($org_video_info as $key => $value) {

                    $group_ids = json_decode($value['open_group'],'TRUE ');
                    //是否为知识点
                    if(!empty($value['section_id'])){
                        if($is_member == 1){
                            $org_video_info[$key]['is_member'] = 1;
                        }else{
                            if($this->isClassmap($group_ids[0],$uid)){       //班级成员
                                    $org_video_info[$key]['is_member'] = 1;
                              }else{
                                   $open_group = M('chapter')->field('open_group')->where('id='.$value['chapter_id'])->find();
                                   $group_ids = json_decode($open_group['open_group'],'TRUE');
                                   $info_v =  array_map('current',$info);
                                   $intersect_id = array_intersect ($group_ids,$info_v);
                                    if(count($intersect_id)>0){
                                        $org_video_info[$key]['is_member'] = 1;
                                    }else{
                                        unset($org_video_info[$key]);
                                        continue;
                                    }
                            }
                        }
                    }

                    if(!empty($value['examination_id'])){
                         $examination_title = M('examination')->field('title')->where('id='.$value['examination_id'])->find();
                        $org_video_info[$key]['title'] = '#'.$examination_title['title'].'#';
                    }
                    $section_ids = $value['section_id'];
                    if(!empty($section_ids)){
                        $chapter_video_ids = $chapter_video->field('sort,title')->where('id='.$value['section_id'])->find();


                        $org_video_info[$key]['title'] = '#'.'第'.numToWord($chapter_video_ids['sort'] ).'节'.'-'.$chapter_video_ids['title'].'#';
                        $org_video_info[$key]['login_uid'] = $uid;
                        $org_video_info[$key]['is_type'] = 6;
                        $chapter_id = $value['chapter_id'];//大章节ID
                    }
                    if(empty($value['examination_id'])){
                        $org_video_info[$key]['section_id'] = $value['section_id'];
                    }else{
                        $org_video_info[$key]['section_id'] = $value['examination_id'];
                    }
                    $map_id['org_id'] = $value['uid'];
                    $map_id['uid'] = $org_id;
                        if($org_star->where($map_id)->find()){
                            $org_video_info[$key]['is_star'] = 1;
                        }else{
                            $org_video_info[$key]['is_star'] = 0;
                        }

                    $map_ids['uid'] = $uid;
                    $map_ids['orgvideo_id'] = $value['id'];
                     $li = $review->where($map_ids)->find();
                        if(!empty($li)){
                            $org_video_info[$key]['is_like'] = 1;
                        }else{
                            $org_video_info[$key]['is_like'] = 0;
                        }
                        
                    $cover_url_info['cover_url'] = $value['cover_url'];
                    $cover_url_info['value'] = $value['video_url'];
                    $cover_url_info['type'] = "VIDEO" ;
                    if(!empty($value['section_id'])){
                        $org_video_info[$key]['is_type']= 7;
                        // $org_video_info[$key]['section_id'] = $value['id'];
                    }else{

                        if(!empty($value['examination_id'])){
                            $examination_ids = M('examination')->field('uid')->where('id='.$value['examination_id'])->find();
                            if($examination_ids['uid'] == $uid){
                                $org_video_info[$key]['is_teacher'] = 1;
                            }else{
                                $org_video_info[$key]['is_teacher'] = 0;
                            }
                            $org_video_info[$key]['is_type']= 8;
                        }else{

                        $org_video_info[$key]['is_type']= 1;
                        }
                    }
                    $org_video_info[$key]['pic']= array($cover_url_info);
                    unset($org_video_info[$key]['cover_url']);
                    unset($org_video_info[$key]['video_url']);
                }
                    foreach ($org_video_info as $key => $value) {

                        if($value['is_type'] == 7){

                            $chapter_video_id =implode(',',M('chapter_video')->field('sort')->where('id='.$value['section_id'])->find());
                            $map_lock['sort'] = $chapter_video_id;
                            // $map_lock['id'] = $value['section_id'];
                            $map_lock['chapter_id'] = $value['chapter_id'];
                            $map_lock['org_id'] = $org_id;
                            $map_lock['title'] = array('NEQ','');
                            $videolist_type = $videolist->orgvideolist($map_lock,$uid,$value['section_id']);
                            if($videolist_type == true){}
                            else{
                                    array_splice($org_video_info,$key,1);
                                    continue;
                            }
                        }
                        elseif($value['is_type'] == 6){

                            $map_lock['sort'] = $value['sort'];
                            $map_lock['chapter_id'] = $value['chapter_id'];
                            $map_lock['org_id'] = $org_id;
                            $videolist_type = $videolist->orgvideolist($map_lock,$uid,$value['id']);
                            if($videolist_type == true){}
                            else{
                                    array_splice($org_video_info,$key,1);
                                    continue;
                            }
                        }

                    }

           return $org_video_info;
    }
    //lisk  获取orgnization_video 从新拼接  2017年5月24日10:04:18
    public function orgnization_video_IN($org_id,$uid,$state_id){
        if($state_id == 1){
            $map = 'c.likes desc';
        }else{
            $map = 'c.create_time desc';
        }
        $examination = M('examination')->where('org_id='.$org_id)->group('id desc')->find();
        $ind['c.is_delete'] = 0;
        $ind['c.org_id'] = $org_id;
        $ind['c.examination_id'] = $examination['id'];
        //排序 
            $orgnization_video = M('orgnization_video');
            $org_video_info = $orgnization_video->alias('c')
                            // ->field('c.id,c.uid,c.title,c.cover_url,c.video_url,c.create_time,c.likes,c.comments,c.description,c.section_id,c.chapter_id,c.org_id,c.likes,m.nickname,m.avatar,l.nickname as org_nickname,r.open_group')                            
                            ->field('c.id,c.uid,c.title,c.cover_url,c.video_url,c.create_time,c.likes,c.comments,c.description,c.section_id,c.chapter_id,c.org_id,c.likes,l.nickname as org_nickname,m.avatar')
                            //->join('__MEMBER_ORG__ l on l.uid =c.uid and l.org_id =c.org_id') //线上用
                             ->join('__MEMBER_ORG__ l on l.uid =c.uid') //测试用
                            ->join('__MEMBER__ m on m.uid = c.uid')
                            // ->join('__CHAPTER__ r on r.org_id = c.org_id')
                            ->where($ind)
                            ->order($map)
                            ->group('c.id')
                            ->select(); 
            $org_star = M('org_star');
            $review = M('review');
            $chapter_video = M('chapter_video');
                foreach ($org_video_info as $key => $value) {
                    $map_id['org_id'] = $value['uid'];
                    $map_id['uid'] = $org_id;
                        if($org_star->where($map_id)->find()){
                            $org_video_info[$key]['is_star'] = 1;
                        }else{
                            $org_video_info[$key]['is_star'] = 0;
                        }

                    $map_ids['uid'] = $uid;
                    $map_ids['orgvideo_id'] = $value['id'];
                     $li = $review->where($map_ids)->find();
                        if(!empty($li)){
                            $org_video_info[$key]['is_like'] = 1;
                        }else{
                            $org_video_info[$key]['is_like'] = 0;
                        }
                        
                    $cover_url_info['cover_url'] = $value['cover_url'];
                    $cover_url_info['value'] = $value['video_url'];
                    $cover_url_info['type'] = "VIDEO" ;
                    $org_video_info[$key]['pic']= array($cover_url_info);
                    unset($org_video_info[$key]['cover_url']);
                    unset($org_video_info[$key]['video_url']);
                }

           return $org_video_info;
    }
    public function orgnization_video_teacher($org_id,$uid,$state_id,$page,$rows){
        if($state_id == 1){
            $map = 'c.likes desc';
        }else{
            $map = 'c.create_time desc';
        }
            // $map_chapter['c.is_delete'] = 0;
            $map_chapter['c.org_id'] = $org_id;
            $map_chapter['c.title'] = array('NEQ','');
        //排序 
            // $orgnization_video = M('chapter_video');
            $org_video_info = D('orgnization_video_teacher')->alias('c')->where($map_chapter)->order($map)->group('c.id')->select();
            // $org_video_info = $orgnization_video->alias('c')
            //                 ->field('c.id,c.uid,c.title,c.cover_url,c.video_url,c.create_time,c.description,c.sort,c.chapter_id,c.org_id,c.likes,m.avatar,l.nickname as org_nickname,r.open_group')
            //                 ->join('__MEMBER_ORG__ l on l.org_id =c.org_id and l.uid = c.uid') //线上
            //                 //->join('__MEMBER_ORG__ l on l.org_id =c.org_id')//测试
            //                 ->join('__MEMBER__ m on m.uid = c.uid', 'left')
            //                 ->join('__CHAPTER__ r on r.id = c.chapter_id')
            //                 ->where($map_chapter)
            //                 // ->page($page,$rows)
            //                 // ->limit(($page - 1) * $rows, $rows)
            //                 ->order($map)
            //                 ->group('c.id')
            //                 ->select();
            // echo $orgnization_video->getlastsql();exit;
            // var_dump($org_video_info);exit;
            $org_star = M('org_star');
            $review = M('review');
            if($this->isOrgOwner($uid,$org_id)){
                $is_member = 1;
            }elseif($this->isOrgAdmin($uid,$org_id,ORG)){
                $is_member = 1;
            }

            $chapter_video = M('chapter_video');
            $chapter =  M('chapter');
                foreach ($org_video_info as $key => $value) {
                    $group_ids = json_decode($value['open_group'],'TRUE ');
                    if($is_member =1){
                        $org_video_info[$key]['is_member'] = 1;
                    }else{
                          if($this->isClassmap($group_ids[0],$uid)){       //
                                $org_video_info[$key]['is_member'] = 1;
                          }else{
                                   $open_group = $chapter->field('open_group')->where('id='.$value['chapter_id'])->find();
                                   $group_ids = json_decode($open_group['open_group'],'TRUE');
                                   $info_v =  array_map('current',$info);
                                   $intersect_id = array_intersect ($group_ids,$info_v);
                                    if(count($intersect_id)>0){
                                        $org_video_info[$key]['is_member'] = 1;
                                    }else{
                                        unset($org_video_info[$key]);

                                        continue;
                                    }
                          }
                    }
$chapter_group = $chapter->field('open_group')->where('id='.$value['chapter_id'])->find();
                    $map_group['uid'] = $uid;
                    $map_group['id'] = $value['id'];
                    $map_group['title'] = array('NEQ','');
                    $map_group['org_id'] = $value['org_id'];
                    $chapter_group = json_decode($chapter_group['open_group'],TRUE);
                    // var_dump($chapter_group);exit;
                    // $map_group['status'] = 1;
                    if(empty($chapter_group)){
                        if($this->orgvideolist($map_group,$uid)){
                        }else{
                                unset($org_video_info[$key]);
                                continue;
                        }
                    }

                    // $org_video_info[$key]['is_lock'] = 0;

                    //是否为知识点
                    if(!empty($value['sort'])){
                        $org_video_info[$key]['title'] = '#'.'第'.numToWord($value['sort']   ).'节'.'-'.$value['title'].'#';
                        $org_video_info[$key]['login_uid'] = $uid;
                        $org_video_info[$key]['is_knowledge'] = 1;
                        $chapter_id = $value['chapter_id'];//大章节ID
                    }
                    $map_id['org_id'] = $value['uid'];
                    $map_id['uid'] = $org_id;
                        if($org_star->where($map_id)->find()){
                            $org_video_info[$key]['is_star'] = 1;
                        }else{
                            $org_video_info[$key]['is_star'] = 0;
                        }

                    $map_ids['uid'] = $uid;
                    $map_ids['orgvideo_id'] = $value['id'];
                     $li = $review->where($map_ids)->find();
                        if(!empty($li)){
                            $org_video_info[$key]['is_like'] = 1;
                        }else{
                            $org_video_info[$key]['is_like'] = 0;
                        }
                        
                    $cover_url_info['cover_url'] = $value['cover_url'];
                    $cover_url_info['value'] = $value['video_url'];
                    $cover_url_info['type'] = "VIDEO" ;
                    $org_video_info[$key]['is_type']= 6;
                    $org_video_info[$key]['pic']= array($cover_url_info);
                    unset($org_video_info[$key]['cover_url']);
                    unset($org_video_info[$key]['video_url']);
                }
                $videolist = new OrgModel();

                    foreach ($org_video_info as $key => $value) {
                        if($value['is_type'] == 7){

                            $chapter_video_id =implode(',',$chapter_video->field('sort')->where('id='.$value['section_id'])->find());
                            $map_lock['sort'] = $chapter_video_id;
                            $map_lock['chapter_id'] = $value['chapter_id'];
                            $map_lock['org_id'] = $org_id;
                            $map_lock['title'] = array('NEQ','');
                            $videolist_type = $videolist->orgvideolist($map_lock,$uid,$value['section_id']);
                            if($videolist_type == true){}
                            else{
                                    array_splice($org_video_info,$key,1);
                                    continue;
                            }
                        }
                        // elseif($value['is_type'] == 6){

                        //     $map_lock['sort'] = $value['sort'];
                        //     $map_lock['chapter_id'] = $value['chapter_id'];
                        //     $map_lock['org_id'] = $org_id;
                        //     $videolist_type = $videolist->orgvideolist($map_lock,$uid,$value['id']);
                        //     if($videolist_type == true){}
                        //     else{
                        //             array_splice($org_video_info,$key,1);
                        //             continue;
                        //     }
                        // }
                    }
           return $org_video_info;
    }
    //加锁
    public function orgvideolist($map_chapter,$uid){
        $chapter_video_id = M('chapter_video');
        $list = $chapter_video_id->field('id,chapter_id,title,description,cover_url,video_url')
                                ->group('id')
                                ->where($map_chapter)
                                ->order('create_time asc')
                                ->select();
// var_dump($map_chapter);exit;
        $content_logic = new \Home\Logic\ContentLogic();
        $is_admin = $content_logic->org_identity($uid,$map_chapter['org_id']);
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
                    return TRUE;
                }
                if($is_admin == 1 || $is_admin == 2){
                    return TRUE;
                }elseif($is_admin == 0){
                    return FALSE;
                    
                }else{
                    if(!empty($arr_group)){
                        if(in_array($v['chapter_id'], $new_chapter)){
                            return TRUE;
                        }
                        $chapter = M('chapter')->field('open_group')->where('id='.$v['chapter_id'])->find();
                        $open_group = json_decode($chapter['open_group'],true);
                        $array_intersect = array_intersect($arr_group,$open_group);
                        if(!empty($array_intersect)){
                            $new_chapter[] = $v['chapter_id'];
                            return TRUE;
                        }
                    }
                    $a_map['section_id'] = $v['id'];
                    $a_map['uid'] = $uid;
                    $count = M('orgnization_video')->where($a_map)->count();
                    if(intval($count)){
                        return TRUE;
                    }else{
                        return FALSE;
                    }
                }
            }
        }
    }
    //大章节 $chapter_id=大章节ID
    public function chapter($chapter_id,$uid,$org_id){
        $select = M('chapter');
        //屏蔽班级
        $chapter = M('chapter')->field('open_group')->where('id='.$chapter_id)->find();
        $open_group = json_decode($chapter['open_group'],true);

        $open_group_id =implode(',',$open_group);
        if(!empty($open_group_id)){
            $map['group_id']  = array('in',$open_group_id);
            $member_group = M('member_group');
            $member_group_id = $member_group->field('uid')->where($map)->select();
            foreach ($member_group_id as $key => $value) {
                if($value['uid'] == $uid){
                    return 1;
                }
            }
        }
        return 0;

    }   
    //是否是机构成员
    public function org_member($uid,$org_id){
            $Group = M('group');
            $map['org_id'] = $org_id;
            $group_rs = $Group->alias('c')
                                   ->field('c.id')
                                   // ->join('__ORGNIZATION__ a ON a.id=c.org_id and c.is_delete=0')
                                   ->where($map)
                                   ->select();
            foreach ($group_rs as $row) {
                    $group_ids[] = $row['id'];
                }
            $group_ids = implode(',', $group_ids);
            if(empty($group_ids)) {
                return TRUE;
            }
            $member_list = $this->getMemberByGroupIds($group_ids,$org_id,$uid);
            if($false == $member_list){
                return FALSE;
            }
                return TRUE;
    }
    /**
     * 根据班级id获取用户列表
     * @param $group_ids 批量班级id，如:1,2,3,4
     */
    private function getMemberByGroupIds($group_ids,$org_id,$uid) {
        
        $newlist = array();
        $num = 0;
        $member_group_info = M('member_group');
        $member_group = $member_group_info
                ->where(array('group_id'=>array('in', $group_ids), 'status'=>1))->select();
                
        $list = M('member_group')->alias('mg')
                    ->field('mg.uid,m.nickname,m.avatar')
                    ->join('__MEMBER__ m on mg.uid = m.uid')
                    ->order('mg.id desc')
                    ->where(array('mg.group_id'=>array('in', $group_ids), 'mg.status'=>1))
                    ->select();
        $member_org = M('member_org')->field('uid')->where('org_id='.$org_id)->select();
        if(!empty($list)){
            $member_org = array_merge($list,$member_org);
        }

        foreach ($member_org as $key=>$row) {
            $newlist[$num++] = $row['uid'];
        }
        $result = array_values(array_unique ($newlist));
        $uids = implode(',', $result);

        $list = $this->organizationmember($uids,$org_id,$page,$rows);
        foreach ($list as  $value) {
            if($value['uid'] == $uid) {
                return TRUE;
            }
            return FALSE;
        }
    }
    public function organizationmember($uids,$org_id,$page,$rows){
        $member_org = M('member_org');
            $map['mo.uid'] = array('in',$uids);
            $map['mo.org_id'] = array('in',$org_id);
            $select = $member_org->alias('mo')
                        ->field('mo.id,mo.uid,mo.nickname,m.avatar')
                        ->join('__MEMBER__ m on mo.uid = m.uid')
                        ->group('mo.uid')
                        ->limit(($page - 1) * $rows, $rows)
                        ->where('mo.uid in('.$uids.') and mo.org_id in('.$org_id.')')
                        ->select();
            return $select;
    }
    /**
     * 
     * @param int $page 1  lisk 2017年5月24日10:04:11
     * @param int $rows 20页
     */
    public function  Hotout($map, $page, $rows,$state_id,$uid)
        {
            $Api = new UserApi;
            if($state_id=''){
                $order = 'c.likes desc';
            }else{
                $order = 'c.create_time desc';
            }
            $map['tag_id'] = array('EQ','');
            unset($map['c.task_id']);
            // $map['task_id'] = array('EQ','');
            $Content = M('Content');
            // $list = D('orgcontentlist_hotout')->alias('c')->group('c.id and c.create_time')->where($map)->order($order)->select();
            $list = $Content->alias('c')
                            // ->page($page, $rows)
                            ->field('c.id,c.uid,c.title,c.description,c.comments,c.likes,c.create_time,m.nickname,m.avatar,l.nickname as org_nickname')
                            ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                            ->join('__MEMBER_ORG__ l on l.uid = c.uid and l.org_id =c.org_id')
                            // ->join('__GROUP__ g on g.uid = c.uid')
                            ->group('c.id and c.create_time')
                            // ->limit(($page - 1) * $rows, $rows)
                            ->where($map)
                            ->order($order)
                            ->select();
                            // echo $Content->getlastsql();exit;
            $org_star = M('org_star');
            foreach ($list as $key => $value) {
                $map_id['org_id'] = $map['c.org_id'];
                $map_id['uid'] = $value['uid'];
                    if($org_star->where($map_id)->find()){
                        $list[$key]['is_star'] = 1;
                    }else{
                        $list[$key]['is_star'] = 0;
                    }
                $list[$key]['is_type'] = 0;
                    if($uid) {
                        $is_like = $Api->isLike($uid, $value['id']);
                        $list[$key]['is_like'] = ($is_like == 'false')?1:0;
                    }
            }
            return $list;
        }
    // 考题
    public function examination($org_id,$uid,$type,$page,$rows){
        $map['e.org_id'] = $org_id;
        $map['e.is_delete'] = 0;
        if($type == 1){
            $map_ids = 'e.likes desc';
        }elseif($type == 2){
            $map_ids = 'e.create_time desc';
        }
        // $list = D('orgcontentlist_examination')->alias('e')->where($map)->order($map_ids)->select();
        $select = M('examination');
        $list = $select->alias('e')
                ->field('e.id,e.uid,e.title,e.description,e.org_id,e.create_time,e.template,m.avatar,l.nickname,e.likes,e.content_json')
                ->join('__MEMBER__ m on m.uid = e.uid')
                ->join('__MEMBER_ORG__ l on l.uid = e.uid  and l.org_id =e.org_id')
                ->where($map)
                // ->limit(($page - 1) * $rows, $rows)
//                ->page($page,$rows)
                ->order($map_ids)
                ->select();
//                 echo $select->getlastsql();exit;
        foreach ($list as $key => $value) {
            $pic = json_decode ($value['content_json'],true);
            $list[$key]['pic'] = $pic;
            $list[$key]['is_type'] = 11;
            $list[$key]['template_id'] = $list[$key]['template'];
            $list[$key]['section_id'] = $value['id'];
            unset($list[$key]['content_json']);
            unset($list[$key]['template']);
            $is_find = M('review')->where($uid.'=uid and examination_id='.$value['id'])->find();
            if(empty($is_find)){
                $list[$key]['is_like'] = 0;
            }else{
                $list[$key]['is_like'] = 1;
            }
        }
        return $list;
    } 
    public function channelid($map,$state_id,$uid,$page,$rows){
        $map['c.is_find'] = 0;
        $map['c.task_ids'] = $state_id;
        $order = 'c.create_time desc';
        $Content = M('Content');
            $list = $Content->alias('c')
                            ->field('c.id,c.uid,c.org_id,c.group_id,c.title,c.tag_id,c.task_id,c.description,c.comments,c.likes,c.create_time,m.avatar,l.nickname as org_nickname')
                            ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                            ->join('__MEMBER_ORG__ l on l.uid = c.uid  and l.org_id =c.org_id')
                            ->group('c.id')
                            ->limit(($page - 1) * $rows, $rows)
                            ->where($map)
                            ->order($order)
                            ->select();
        $Api = new UserApi;
        foreach ($list as $key => $value) {
                if($uid) {
                    $is_like = $Api->isLike($uid, $value['id']);
                    $list[$key]['is_like'] = (!empty($is_like))?1:0;
                }
                $list[$key]['is_type'] = 0;
        }
        return $list;
    }
    // 作业列表处理 lisk 2017年6月17日14:46:05
    public function joblist($map,$uid,$org,$mapp,$page,$rows){
        $org_id = $map['c.org_id'];
        $map['c.is_find'] = 0;
        // $map['c.is_read'] = 1;
        // $map['c.tag_id'] = array('EQ',0);
        // $map['c.task_id'] = array('GT',0);
        // $map['c.group_id'] = array('GT',0);
        if($org == 'org1'){
            $order = 'c.likes desc';
        }else{
            $order = 'c.create_time desc';
        }
            unset($map['c.task_id']);
            $Content = M('content');
            $dou = D('joblist');
            $lista = $dou->alias('c')->group($order)->where($map)->select();
            $listr = $dou->alias('c')->group($order)->where($mapp)->select();
            $list = array();
            if(!empty($listr)){
                $list = array_merge($list,$listr);
            }
            if(!empty($lista)){
                $list = array_merge($list,$lista);
            }
        $org_star = M('org_star');
        //是否是这个班级成员
        $member_group = M('member_group');
        $group = M('group');
        $orgnization = M('orgnization');
        $admin = M('admin');

        if($this->isOrgOwner($uid,$org_id)){
            $is_member = 1;
        }elseif($this->isOrgAdmin($uid,$org_id,'ORG')){
            $is_member = 1;
        }

        $Api = new UserApi;
            foreach ($list as $key => $value) {
                $map_id['org_id'] = $value['org_id'];
                $map_id['uid'] = $value['uid'];
                $map_group['uid'] = $uid;
                $map_group['group_id'] = $value['group_id'];
                $map_group['status'] = 1;
                    if($is_member == 1){
                        $list[$key]['is_member'] = 1;
                    }else{
                        if($this->isClass($map_group)){
                            $list[$key]['is_member'] = 1;
                        }elseif($this->isGROUP($uid,$value['group_id'])){
                            $list[$key]['is_member'] = 1;
                        }else{
                            $list[$key]['is_member'] = 0;
                            // unset($list[$key]);
                            // continue;
                        }
                    }
                $task = M('task')->alias('c')
                        ->field('a.name')
                        ->join('__TAGS__ a on a.id=c.tag_id')
                        ->where('c.id='.$value['task_id'])
                        ->find();
                $tags_name =$task['name'];
                if($value['group_id'] != 0){
                        $list[$key]['title'] = '#'.$tags_name.'作业'.'#';
                        if($this->isOrgAdmin($value['uid'],$org_id,'ORG')){
                            $list[$key]['is_teacher'] = 1;                     
                       }elseif($this->isGROUP($value['uid'],$value['group_id'])){
                           $list[$key]['is_teacher'] = 1;
                       }else{
                           $list[$key]['is_teacher'] = 0;
                       }
                }
                if($value['task_id'] == 0 && $value['group_id'] == 0){
                    $list[$key]['is_type'] = 0;
                }else{
                    $list[$key]['is_type'] = 5;
                }
                
                    if($uid) {
                        // var_dump($value);exit;
                        $is_like = $Api->isLike($uid, $value['id']);
                        $list[$key]['is_like'] = ($is_like=='false')?1:0;
                    }
            
                // if($uid) {
                //     $is_like = $Api->isLike($uid, $value['id']);
                //     $list[$key]['is_like'] = $is_like?1:0;
                // }   
                
            }
            return $list;
    }
    //是否机构管理员
    public function isOrgAdmin($uid, $org_id,$type = 'ORG' ) {
        $Admin = M('admin');
        $map['uid'] = $uid;
        $map['related_id'] = $org_id;
        $map['type'] = $type;
        $info = $Admin->field('id')->where($map)->find();
        if(!empty($info['id']) || $this->isOrgOwner($uid, $org_id)) {
            return TRUE;
        }
        return FALSE;
    }
    
    //是否机构创建者
    public function isOrgOwner($uid, $org_id) {
        $Org = M('orgnization');
        $map['uid'] = $uid;
        $map['id'] = $org_id;
        $info = $Org->field('id')->where($map)->find();
        if(!empty($info['id'])) {
            return TRUE;
        }
        return FALSE;
    }
    //是否为班级管理员
    public function isGROUP($uid, $group_id) { 
        $Admin = M('admin');
        $map['uid'] = $uid;

        $map['related_id'] = array('in',$group_id);

        $map['type'] = GROUP;
        $info = $Admin->field('id')->where($map)->find();
        if(!empty($info['id'])) {
            return TRUE;
        }
        return FALSE;
    }
    // 是否为班级成员
    public function isClass($map_group) { 
        $Admin = M('member_group');
        if($Admin->field('group_id')->where($map_group)->find()){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    // 是否为班级成员
    public function isClassmap($map_group,$uid) { 
        $Admin = M('member_group');
        $map['group_id'] = array('EQ',$map_group);
        $map['uid'] = $uid;
        $map['status'] = 1;
        if($Admin->field('group_id')->where($map)->find()){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function Content_info($map,$page,$rows){
        $map['c.group_id'] = 0;
        $Content = M('Content');
     $list = $Content->alias('c')
                        ->page($page, $rows)
                        ->field('c.id,c.uid,c.title,c.description,c.comments,c.likes,c.create_time,m.avatar,d.nickname as org_nickname')
                        ->join('__MEMBER__ m on m.uid = c.uid')
                        ->join('__MEMBER_ORG__ d on d.uid = c.uid and d.org_id =c.org_id ')
                        // ->join('__GROUP__ g on g.uid = c.uid')
                        ->group('c.id')
                        ->where($map)
                        ->order('c.create_time desc')
                        ->select();
        $org_star = M('org_star');
        foreach ($list as $key => $value) {
            $map_id['org_id'] = $map['c.org_id'];
            $map_id['uid'] = $value['uid'];
            if($org_star->where($map_id)->find()){
                $list[$key]['is_star'] = 1;
            }else{
                $list[$key]['is_star'] = 0;
            }
        }
        return $list;
    }
    public function viewContent($map){
                $detail = M('Content')->alias('c')
                            ->field('c.id,c.uid,c.title,c.description,c.comments,c.likes,c.create_time,o.nickname,m.avatar')
                            ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                            ->join('__MEMBER_ORG__ o on o.uid = c.uid')
                            ->where($map)
                            ->find();
                return $detail;
    }
    public function livestreaming($map){
                $detail = M('live')->alias('c')
                        ->field("c.id,c.uid,c.title,'' as description,c.publish ,c.group_id,c.cover_url,c.comments,c.likes,c.start_time as create_time,m.nickname,m.avatar")
                        ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                        ->where($map)
                        ->find();
                return $detail;
    }
    public function recordedbroadcast($map){
                $detail = M('live_recorded')->alias('c')
                        ->field("c.id,c.uid,c.live_id,c.title,'' as description,c.play ,c.group_id,c.cover_url,c.comments,c.likes,c.add_time as create_time,c.start_time as create_timestamp,c.end_time as update_timestamp,o.nickname,m.avatar")
                        ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                        ->join('__MEMBER_ORG__ o on o.uid = c.uid')
                        ->where($map)
                        ->find();
                return $detail;
    }
    //动态列表
    public function Comment($map,$type,$uid){
        if($type ==1 ){
            $data = 'c.create_time desc';
        }else{
            $data = 'c.review desc';
            $map['c.review'] = array('EGT',10);
        }   
            $map['is_delete'] = 0;
            $Comment = M('comment');
            $list = $Comment->alias('c')
                            ->field('m.uid,o.nickname,m.avatar,c.id,c.content,c.create_time,c.review,c.parent_id')
                            ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                            ->join('__MEMBER_ORG__ o on o.uid = c.uid')
                            ->where($map)
                            ->group('c.id')
                            ->order($data)
                            ->select();
            $list['sum'] = count($list);

            if($type == 2){
                if($list['sum'] <10){
                    return;
                }
            }
            $coeee = M('review_info');
            foreach ($list as $key => $value) {
                if($value['uid'] == $uid){
                    $list[$key]['is_uid'] = 1;
                }else{
                    $list[$key]['is_uid'] = 0;
                }
                if(!empty($value['parent_id'])){
                    $id = $value['parent_id'];
                    $content = $Comment->alias('c')
                                ->field('m.uid,m.nickname,m.avatar,c.id,c.content,c.create_time,c.review')
                                ->join('__MEMBER__ m on m.uid = c.uid')
                                // ->join('__MEMBER_ORG__ o on o.uid = c.uid and org_id')
                                ->where('is_delete=0 and id='.$id)
                                ->group('c.id')
                                ->find();
                    $content['create_time'] = date('m-d H:i',$content['create_time']);
                    $value['like'] = intval($value['review']);
                    $content['review_id'] = 0;
                    $content['content'] = rawurldecode($content['content']);
                    $value['content'] = rawurldecode($value['content']);
                    unset($content['review']);
                    if($coeee->where($value['id'].'=comment_id and uid='.$uid)->find()){
                        $value['is_like'] = 1;
                    }else{
                        $value['is_like'] = 0;
                    }
                    if($content['uid'] == $uid){
                        $content['is_uid'] = 1;
                    }else{
                        $content['is_uid'] = 0;
                    }
                    if($value['uid'] == $uid){
                        $value['is_uid'] = 1;
                    }else{
                        $value['is_uid'] = 0;
                    }
                    $content['is_new'] = 1;
                    $content['parent_id'] = 0;
                    unset($value['review']);
                    unset($content['review_id']);
                    $list[$key] = $content;
                    $value['create_time'] = date('m-d H:i',$value['create_time']);
                    $list[$key]['new_reply'] = $value;
                }else{
                    $list[$key]['is_new'] = 0;
                    $list[$key]['new_reply'] = (object) null;
                }
                $list[$key]['create_time'] = date('m-d H:i',$value['create_time']);
            }
            $count['comments'] = $list['sum'];
            M('content')->where('id='.$map['c.work_id'])->save($count);

            return $list;
    }

    //用户发布做而已  lisk 2017年6月26日09:56:33
    public function memberTaskList($group_id,$task_id) {
        $Content = M('task')->alias('t');
        $org_id = M('group')->field('org_id')->where('id='.$group_id)->find();
        $org_id = implode(',',$org_id);
        $map['o.org_id'] = $org_id;
        $map['group_id'] = $group_id;
        $map['is_admin'] = 0;
        $map['c.status'] = 1;
        $map['t.id'] = $task_id;
        //排序方式
        $order = (I('order', '', 'trim') == 'likes') ? 'c.likes desc' : 'c.id desc';
        
        $list = $Content->field('c.id,c.title,c.create_time,c.likes,c.comments,m.nickname,m.avatar')
                        ->where($map)
                        ->join('__CONTENT__ c on t.id = c.task_id', 'left')
                        ->join('__MEMBER__ m on m.uid = c.uid', 'left')
                        ->join('__MEMBER_ORG__ o on o.uid=m.uid')
                        ->group('c.id')
                        ->order($order)
                        ->select();
        if(count($list) !== 0) {
            return TRUE;
        }   
            return FALSE;
    }
    //机构列表缓存
    public function conten_redis($list,$uid,$org_id,$state_id){
        $ContentModel = new \Home\Model\ContentModel();
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
            $count_s = count($list);
            $count_ceil = ceil($count_s/$rows);
            $res = array();
            $m = -1;
            for ($i=0; $i <= $count_s; $i++) { 
                if ($i%$rows == 0) {
                  if($i>0){
                    $redis->set('orgContentList'.'_'.$uid.'_'.$org_id.'_'.$state_id.'_'.($m+1),json_encode($res[$m]));
                  }
                  $m+=1;
                  $res[$m] = array();
                }
                $res[$m][] = $list[$i];
            }
            return;
    }
}

