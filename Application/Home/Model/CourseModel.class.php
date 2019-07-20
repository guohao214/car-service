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
 * 评论模块
 */
class CourseModel {
    public function review($id,$type=0,$flag=0,$uid){//$type=2 直播，3 录播 机构视频 专辑10
        $page = 0;
        if($flag == 1){
            // if($type != 4){
                $map['a.review_id'] = array('EGT',10);
            // }
            $order = 'a.review_id desc';//最热
            $rows = 3;
        }elseif($flag == 0){
            $order = 'a.create_time desc';//最新
            $rows = 20;
        }
        if($type == 4 ){

            $type = 0;
        }elseif($type == 10){
            $type = 4;
        }
        $ids = $id;
        $map['a.is_delete'] = 0;
        $arr = array('a.video_id','a.orgcomment_id','a.live_id','a.recorded_id','a.special_id');//动态类型数组
        $map[$arr[intval($type)]] = $id;
        if($type == 1){
            $orgnization_video = M('orgnization_video')->field('org_id')->where('id='.$id)->find();
            $orgnization_video = implode(',',$orgnization_video);
            // $map['o.org_id'] = $orgnization_video;
        }
        $list = M('review')->alias('a')
                ->join('__MEMBER__ b ON b.uid=a.uid')
                ->join('__MEMBER_ORG__ o on o.uid=a.uid')
                ->field('a.id,a.uid,a.create_time,a.content,a.review_id,o.nickname,b.avatar,a.parent_id')
                ->where($map)
                ->group('a.id')
                ->order($order)
                ->select();
        if($flag == 0){
            $list['sum'] = count($list);
        }
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
            $coeee = M('review_info');
            $review = M('review');
            foreach ($list as $key => $value) {

                    $list[$key]['like'] = $value['review_id'];
                if(!empty($value['parent_id'])){

                    $id = $value['parent_id'];
                    $ids = $value['id'];
                    $content = $review->alias('c')
                                ->join('__MEMBER__ b ON b.uid=c.uid')
                                ->join('__MEMBER_ORG__ o on o.uid=c.uid')
                                ->field('c.id,c.uid,c.create_time,c.content,c.review_id,o.nickname,b.avatar,c.parent_id')
                                ->where('c.id='.$id)
                                ->group('c.id')
                                ->find(); 
                    $content['is_new'] = 1;               
                    $content['create_time'] = date('m-d H:i',$content['create_time']);

                    $content['like'] = $content['review_id'];
                    $content['content'] = rawurldecode($content['content']);
                    unset($content['review_id']);
                    // $arr_review = array('comment_id','org_id','live','recorded_id');
                    $arr_review = array('comment_id','comment_id','comment_id','comment_id','comment_id');
                    $map_review[$arr_review[$type]] = $ids;
                    $map_review['uid'] = $uid;
                    if($uid == $value['uid']){
                        $value['is_uid'] = 1;
                    }else{
                        $value['is_uid'] = 0;
                    }
                    if($coeee->where($map_review)->find()){
                        $value['is_like'] = 1;
                    }else{
                        $value['is_like'] = 0;
                    }
                    $list[$key]['is_new'] = 1;
                    $value['like'] = $value['review_id'];
                    $list[$key] = $content;
                    $value['create_time'] = date('m-d H:i',$value['create_time']);
                    $list[$key]['new_reply'] = $value;
                }else{
                    unset($list[$key]['review_id']);
                    $list[$key]['is_new'] = 0;
                    $list[$key]['new_reply'] =  (object) null;;
                }
                $list[$key]['create_time'] = date('m-d H:i',$value['create_time']);

            }
            $count['comments'] = $list['sum'];
            if($type == 3){
                if($flag == 0){
                    $info = M('live_recorded')->where('id='.$id)->save($count);
                }
            }
        return $list;
    }
    public function review_info($id,$type=0,$uid){
        $arr = array('comment_id','org_id','live','recorded_id');
        $map[$arr[$type]] = $id;
        $map['uid'] = $uid;
        $select = M('review_info')->where($map)->find();
        return $select;
    }
}

