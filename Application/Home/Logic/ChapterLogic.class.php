<?php
namespace Home\Logic;
use Exception;
use User\Api\UserApi;
/**
 * Class ScheduleLogic
 * @name 课程表的逻辑处理类*
 * @author sufyan add
 */
class ChapterLogic
{
    private $post_data = null;
    private $is_delete = 0;
    public function __construct($post=null){
        $this->post_data = $post;
        $this->is_delete = $this->post_data['is_delete'];
    }
    public function check_chapter_set($modify_type){
        //$modify_type = data_isset($this->post_origin_data['modify_type'],'intval',0);//0:cover_url;1:title;2:删除小节chapter_video_id;3:open_group    
        if($modify_type == 0 || $modify_type == 1 || $modify_type == 3){
            $chapter = $this->check_chapter_id();
            $data = array();
            if($modify_type == 0){
                $type = 'cover_url';
                $content = $this->check_cover_url();
                $operate_info = '封面修改';
            }elseif($modify_type == 1){
                $type = 'title';
                $content = $this->check_title();
                $operate_info = '章节名称修改';
            }elseif($modify_type == 3){
                $type = 'open_group';
                $open_group = str_replace('&quot;', '"', data_isset($this->post_data['open_group'],'trim'));
                $content = $open_group; 
                $operate_info = '开放班级修改';
            }
            $data[$type] = $content;
            $flag = M('chapter')->where('id='.$chapter['id'])->save($data);
            if(!$flag){
                if($chapter[$type] == $data[$type]){//修改
                   $flag = 1; 
                }
            }
        }elseif($modify_type == 2){
            $chapter_video = $this->check_chapter_video();
            $map['is_delete'] = 1;
            $map['update_time'] = time();
            $flag = M('chapter_video')->where('id='.$chapter_video['id'])->save($map);
            
//            foreach ($chapter_video as $key=>$val){
//                $map['is_delete'] = 1;
//                $map['update_time'] = time();
//                $flag = M('chapter_video')->where('id='.$val['id'])->save($map);
//                if(!$flag){
//                    //事物
//                }
//            }
            if($flag){
                $chapter = M('chapter')->field('num')->where('id='.$chapter_video['chapter_id'])->find();
                $newmap['num'] = $chapter['num'] - 1;
                $flag = M('chapter')->where('id='.$chapter_video['chapter_id'])->save($newmap);
            }
            $operate_info = '小节删除';
        }
        $operate = array('flag'=>$flag,'info'=>$operate_info);
        return $operate;
    }
    private function check_chapter_video(){
        $chapter_video_id = data_isset($this->post_data['chapter_video_id'],'intval',0);
        if(empty($chapter_video_id)){
            throw new Exception('所删除的小节不存在',1319);
        }
        $chapter_video = M('chapter_video')->field('cover_url,title,chapter_id,sort,id')->where('id='.$chapter_video_id.' and is_delete=0')->find();
        
        if(empty($chapter_video)){
            throw new Exception('所删除的小节不存在',1319);
        }
       // $chapter_video = M('chapter_video')->field('cover_url,title,chapter_id,sort,id')->where('title!="" and sort='.$chapter_video['sort'].' and is_delete=0')->select();
        
        return $chapter_video;
    }
    public function check_chapter_id(){
        $chapter_id = data_isset($this->post_data['chapter_id'],'intval',0);
        if(empty($chapter_id)){
            throw new Exception('所编辑的章节不存在',1318);
        }
        $chapter = M('chapter')->field('id,num,cover_url,title,open_group')->where('id='.$chapter_id.' and is_delete=0')->find();
        if(empty($chapter)){
            throw new Exception('所编辑的章节不存在',1318);
        }
        return $chapter;
    }

    private function check_title(){
        $title = data_isset($this->post_data['title'],'trim');
        if(empty($title)){
            throw new Exception('标题不能为空',1034);
        }
        $title_len = mb_strlen($title, 'utf-8');
        if($title_len>10 || $title_len<1) {
            throw new Exception('标题字数在1-10个字',1034);
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

    //检测添加编辑时的参数
    public function check_chapter($uid){
        $org_id = data_isset($this->post_data['org_id'],'intval');
        $schedule_logic = new ScheduleLogic();
        $org = $schedule_logic->org_isexist($uid,$org_id);
        if($org['uid'] != $uid){//如果不是机构创建者，就查找是不是机构管理员
            $admin = M('admin')->where('uid='.$uid.' and type="ORG" and related_id ='.$org_id)->find();
            if(empty($admin)){
                throw new Exception('只有机构创建者或机构管理员才有权限',1301);
            }
        }
        $chapter_id = data_isset($this->post_data['chapter_id'],'intval',0);
        if($chapter_id > 0){
            $chapter = M('chapter')->where('id='.$chapter_id)->find();
            if(empty($chapter)){
                throw new Exception('所编辑的章节不存在',1318);
            }
            $this->is_delete = data_isset($this->post_data['is_delete'],'intval',0);
        }
        $data = array();
        if($this->is_delete != 1){
            $title = $this->check_title();
            $cover_url = $this->check_cover_url();
            $num = data_isset($this->post_data['num'],'intval',0);
            if($num == 0){
                throw new Exception('章节数要大于0',1317);
            }
            $data = array(
                'uid'=>$uid,
                'org_id'=>$org_id,
                'title'=>$title,
                'cover_url'=>$cover_url,
                'num'=>$num,
            );
        }
        return array('data'=>$data,'chapter_id'=>$chapter_id);
    }
    //添加，编辑
    public function operate($params){
        if($params['chapter_id'] > 0){//删除和编辑
            $params['data']['update_time'] = time();
            $map['id'] = $params['chapter_id'];
            if($this->is_delete == 1){//删除
                $params['data']['is_delete'] = 1;
            }
            $flag = M('chapter')->where($map)->save($params['data']); 
            if($this->is_delete == 1){
                $operate = array('flag'=>$flag,'info'=>'删除');
            }else{                         
                $operate = array('flag'=>$flag,'info'=>'编辑');
            } 
        }else{//添加
            $params['data']['create_time'] = time();
            $flag = M('chapter')->add($params['data']);
            $operate = array('flag'=>$flag,'info'=>'添加');
            if($flag){
                $params['chapter_id'] = M('chapter')->getLastInsID();
                $operate = array('flag'=>$flag,'info'=>'添加','chapter_id'=>$params['chapter_id']);
            }
        }
        $data = array(
            'uid'=>$params['data']['uid'],
            'chapter_id'=>$params['chapter_id'],
            'org_id'=>$params['data']['org_id'],
            'num'=>$params['data']['num'],
            'info'=>$operate['info'],
            );
        if(!$flag){
            throw new Exception($operate['info'].'失败',0);
        }else{
            if($this->is_delete == 1){//删除章节 同时删除小节
                $video_data['is_delete'] = 1;
                $video_data['update_time'] = time();
                $map_video['chapter_id'] = $params['chapter_id'];
                $video_delete = M('chapter_video')->where($map_video)->save($video_data);
//                if(!$video_delete)
//                        throw new Exception($operate['info'].'失败',0);
            }else{
                $this->chapter_video_add($data);
            }
        }
        return $operate;
    }
    //查看章节是否存在
    private function chapter_video_add($arr){
        $count = M('chapter_video')->where('chapter_id='.$arr['chapter_id'])->count();
        $count = intval($count);
        $num = $arr['num'];
        if($count < $num){
            $chapters = M('chapter_video')->where('chapter_id='.$arr['chapter_id'])->field('sort')->order('sort desc')->find();
            if(empty($chapters)){
                $sort = 0;
            }else{
                $sort = $chapters['sort'] ;
            }
            $data_all = array();
            for($count;$count<$num;$count++){//批量添加节数
                $data = array();
                $data['uid'] = $arr['uid'];
                $data['chapter_id'] = $arr['chapter_id'];
                $data['org_id'] = $arr['org_id'];
                $data['sort'] = ++$sort;
                $data['create_time'] = time();
                $data_all[] = $data;
            }
            $result = M('chapter_video')->addAll($data_all);
            if(!$result){
                throw new Exception($arr['info'].'失败',0);
            }
        }
    }
}
