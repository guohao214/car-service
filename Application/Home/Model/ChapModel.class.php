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
 * 知识点
 */
class ChapModel {

    public function chapter_video($map,$uid){
            $chapter_video_id = M('chapter_video');
            $chapter_video = $chapter_video_id->field('sort')->where($map)->group('sort')->order('sort desc')->find();
            //添加最新节数
            $map['title'] = array('eq','');
            $count = $chapter_video_id->where($map)->count();            
            $num = intval($count) + 1;
            
            $chapter_video = implode(',',$chapter_video);
            $map['sort'] = $chapter_video+1;
            $map['create_time'] = time();
            $map['uid'] = $uid;
            $ids = $chapter_video_id->add($map);
            if(!empty($ids['id'])){
                 $select = $chapter_video_id->field('id,sort')->where('id='.$ids)->find();
                    $new = array();                    
                    $new['sort'] = '第'.numToWord($num).'节';
                    $new['sort_id'] = $select['sort'];
                    $new['chapter_video_id'] = $select['id'];
                    $new_chapter[] = $new;
                    return $new_chapter;
            }
                    return 0;
    }
}
