<?php
/**
 * Class CourseController
 * @name 课程管理
 */
 
namespace Home\Controller;
use User\Api\UserApi;
use Home\Model\ContentModel;
use Home\Model\CourseModel;
class CourseController extends HomeController {
    /**
     *@author lisk 2017-05-06 lisk 2017-04-14 modify (17-05-12 16:22)
     *@param  已购课程
     *@return JSON
     */
    public  function specialunpurchase(){
        $uid =  parent::check_login();
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',10);
        $select = M('gift_order');
        $list = M('gift_order')->field('id,combo_id')->where(array('uid' => $uid,'status' => '1','type' => 2))->select();
        if(empty($list)){
                $result = array('status'=>1056,'info'=>'没有更多了');
                parent::put_post($result);
        }else{
            foreach ($list as $key=>$row) {
                $list[$key] = $row['combo_id'];
            }
            $uids = implode(',', $list);
            $list = $this->batchUserWork($uids,$page,$rows);
            $result = array('status'=>1,'data'=>$list,'info'=>'已购课程');
            parent::put_post($result);
        }
    }

    public function batchUserWork($uids,$page,$rows) {
        $Work = M('course');
                    $courseDatas = $Work->alias('c')
                                    ->join('dbh_member m ON m.uid = c.uid')
                                    ->join('dbh_course_category cc ON cc.id = c.course_category_id')
                                    ->field(array('c.id','c.price','course_category_name','course_name','course_cover_url','tags','nickname','avatar'))
                                    ->where('c.id in ('.$uids.')')
                                    ->order('c.id desc')
                                    ->limit(($page - 1) * $rows, $rows)
                                    ->select();
        $this->addTagsForCourse($courseDatas);
        return $courseDatas;
    }
    /**
     *@author lisk 2017-04-10  lisk 2017-04-14 modify (17-05-12 16:22)
     *@param  专辑视频详情页评论
     *@return JSON
     */
    public function specialuncomment(){
        $uid =  parent::check_login();
        $id = data_isset($this->post_origin_data['id'],'intval');
        if(empty($id)){
                $result = array('status'=>1037,'info'=>'视频ID不能为空');
                parent::put_post($result);
        }
        $content = data_isset($this->post_origin_data['content'],'trim');
        if(empty($content)){
                $result = array('status'=>1069,'info'=>'评论内容不能为空');
                parent::put_post($result);
        }
        $map['uid']=$uid;
        $map['special_idd'] =$id;
        $map['content']=$content;
        $map['create_time']=time();
        $select=M('review');
        if($select->add($map)){
                $result = array('status'=>1,'info'=>'评论成功');
                parent::put_post($result);
        } 
                $result = array('status'=>0,'info'=>'评论失败');
                parent::put_post($result);
    }
    /**
     *@author lisk 2017-04-10
     *@param  专辑视频详情页评论点赞
     *@return JSON
     */
    public function specialuncommentlike (){
        $id = data_isset($this->post_origin_data['id'],'intval');
        if(empty($id)){
                $result = array('status'=>0,'info'=>'评论ID不能为空');
                parent::put_post($result);
        }
        $uid =  parent::check_login();
        $map['special_id']=$id;
        $map['uid']=$uid;
        $select=M('review_info');
        if($select->where($map)->delete()){
            M('review')->where('id='.$id)->setDec('review_id');
                $result = array('status'=>1,'info'=>'取消点赞');
                parent::put_post($result);
        }else{
            $map['create_time']=time();
            M('review')->where('id='.$id)->setInc('review_id');
            $select->add($map);
                $result = array('status'=>1,'info'=>'点赞成功');
                parent::put_post($result);
        }

    }
    /**
     *@author lisk 2017-04-10
     *@param  专辑视频详情页分享
     *@return JSON
     */
    public function specialunshare(){
        $id = data_isset($this->post_origin_data['id'],'intval');
        if(empty($id)){
                $result = array('status'=>1136,'info'=>'专辑ID不能为空');
                parent::put_post($result);
        }
        $select=M('special_video');
        if($select->where('id='.$id)->setInc('share')){
            $result = array('status'=>1,'info'=>'分享成功');
        }else{

            $result = array('status'=>1,'info'=>'分享成功');
        }
    }
    /**
     *@author lisk 2017-04-10
     *@param  专辑视频详情页点赞
     *@return JSON
     */
    public function specialunlike(){
        $uid=is_login();
        if(empty($uid)){
            $this->renderFailed('请先登录');
        }
        $id=I('id','','intval');
        if(empty($id)){
            $this->renderFailed('专辑视频ID不能为空');
        }
        $map['uid'] = $uid;
        $map['special_idd'] = $id;

            $Likes = M('review');
            if($Likes->where($map)->delete()) {     
                M('special_video')->where('id='.$id)->setDec('likes');
                $this->renderSuccess('取消点赞');
            }
            else {
                $map['create_time'] = time();
                // $map['special_id']=$id;
                $li=M('review')->add($map);
                M('special_video')->where('id='.$id)->setInc('likes');
                $this->renderSuccess('点赞成功');
            }
    }
    /**
     *@author lisk 2017-04-11
     *@param  专辑视频详情页评论
     *@return JSON
     */
    public function specialunnewcomment(){
        $id=I('id','','intval');
        if(empty($id)){
            $this->renderFailed('视频ID不能为空');
        }
        $uid=is_login();
        $select=M('review')->alias('a')
                            ->join('__MEMBER__ b ON b.uid=a.uid')
                            ->field('a.uid,a.create_time,a.content,a.likes,b.nickname,b.avatar')
                            ->where('a.special_id='.$id)
                            ->order('a.create_time desc')
                            ->select();
        if(empty($uid)){
            foreach ($select as $k => $v) {
                $v['create_time']=date("m-d H:i",$v['create_time']);
                $v['is_like']=0;
                $li[]=$v;
            }
        }else{
            foreach ($select as $k => $v) {
                $v['create_time']=date("m-d H:i",$v['create_time']);
                if($v['uid']==$uid){
                    $v['is_like']=1;
                }else{
                    $v['is_like']=0;
                }
                $li[]=$v;
            }    
        }
        if(count($li)==0){
            $this->renderFailed('没有更多了');
        }
        $this->renderSuccess('详情页评论',$li);

    }
    /**
     *@author lisk 2017-04-11
     *@param  专辑视频详情页
     *@return JSON
     */
    public function specialvideodetails(){
        $page = I('page', '1', 'intval');
        $rows = I('rows', '3', 'intval');
        $id = data_isset($this->post_origin_data['id'],'','intval');
        if(empty($id)){
            $result = array('status'=>0,'info'=>'专辑视频ID不能为空');
            parent::put_post($result);
           // $this->renderFailed('专辑视频ID不能为空');
        }
        $list=M('special_video');
        $select=$list->alias('a')
                    ->join('__MEMBER__ b ON a.uid=b.uid')
                    ->field('a.id,a.uid,a.cover_url,a.video_url,a.likes,a.share,a.title,a.content,a.create_time,b.nickname,b.avatar')
                    ->where('a.id='.$id)
                    ->select();
         $li=array_shift($select);
         $aa=$li['uid'];  //专辑用户ID
         $li['create_time']=date("m-d H:i",$li['create_time']);
        $uid=is_login();
         if(empty($uid)){
            $li['is_like']=-1;
         }else{
            if(M('review')->where($uid.'=uid and special_id='.$id)->find()){
                $li['is_like']=1;
            }else{
                $li['is_like']=0;
            }
         }        
        // $select['comment']=$li;
         $result = array('status'=>1,'info'=>'专辑视频详情页','data'=>$li);
            parent::put_post($result);
        //$this->renderSuccess('专辑视频详情页',$li);
    }
    /**
     *@author lisk 2017-04-10
     *@param  专辑详情页
     *@return JSON
     */
    public function specialdatail(){
        // if(IS_POST){
                $id = data_isset($this->post_origin_data['id'],'intval');
                if(empty($id)){
                    $result = array('status'=>0,'info'=>'专辑ID为空');
                    parent::put_post($result);
                }
                
                $select=M('special_video');
                $list=$select->alias('a')
                            ->field('a.form,a.id,a.uid,a.video_url,a.cover_url,a.title,a.description,a.browse,a.likes,a.share')
                            ->find($id);
                $form=$list['form'];
                $selectall=M('special_video')->alias('a')
                                            ->join('__MEMBER__ b ON b.uid=a.uid')
                                            ->field('a.id,a.uid,a.video_url,a.cover_url,a.title,b.nickname,b.avatar')
                                            ->where($form.'= a.form and  a.is_delete=0')
                                            ->order('a.likes desc')
                                            ->select();
                $list['pic']=$selectall;
                $result = array('status'=>1,'data'=>$list,'info'=>'专辑详情页');
                parent::put_post($result);
        // }
    }
    /**
     *@author lisk 2017-04-10
     *@param  专辑
     *@return JSON
     */
    public function specialshow(){
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',10);
        $select=M('special_video');
        $list=$select->alias('a')
                    ->page($page,$rows)
                    ->join('__MEMBER__ b ON b.uid=a.uid')
                    ->field('a.id,a.video_url,a.cover_url,a.lecturer,a.expect,b.uid,b.nickname,b.avatar')
                    ->group('a.form')
                    ->order('a.browse desc')
                    ->select();
        if(count($list)==0){
                $result = array('status'=>0,'info'=>'没有更多了');
                parent::put_post($result);
        }
                $list = parent::format_data($list);
                $result = array('status'=>1,'data'=>$list,'info'=>'专辑');
                parent::put_post($result);
    }
    /**
     *@author lisk 2017-04-17
     *@param  专辑视频详情页评论列表
     *@return JSON
     */
    public function specialComments(){
        $id=I('id','','intval');
        if(empty($id)){
            $this->renderFailed('专辑ID不能为空');
        }
        $select=M('review')->alias('a')
                ->join('__MEMBER__ b ON b.uid=a.uid')
                ->field('a.id,a.uid,a.create_time,a.content,a.review_id,b.nickname,b.avatar')->where('a.special_id='.$id)->order('a.create_time desc')->select();
        $li['count']=count($select);
        $uid=is_login();
        $ww=M('review_info');
        if(empty($uid)){
                foreach ($select as $k => $v) {
                    $v['create_time']=date("m-d H:i",$v['create_time']);
                    $v['is_like']=-1;
                    $lii[]=$v;
                }
        }else{
                foreach ($select as $k => $v) {
                    $vid=$v['id'];
                    if($ww->where($vid.'=special_id and uid='.$uid)->find()){
                            $v['create_time']=date("m-d H:i",$v['create_time']);
                            $v['is_like']=1;
                            $lii[]=$v;
                        
                    }else{
                        $v['create_time']=date("m-d H:i",$v['create_time']);
                        $v['is_like']=0;
                        $lii[]=$v;
                    }
                }
        }
        if(count($lii)==0){
            $this->renderFailed('没有更多了');
        }
        $list=M('review')->alias('a')
                ->page('1,3')
                ->join('__MEMBER__ b ON b.uid=a.uid')
                ->field('a.id,a.uid,a.create_time,a.content,a.review_id,b.nickname,b.avatar')->where('a.special_id='.$id)->order('a.special_idd desc')->select();
        if(empty($uid)){
                foreach ($list as $k => $v) {
                    $v['create_time']=date("m-d H:i",$v['create_time']);
                    $v['is_like']=-1;
                    $liio[]=$v;
                }
        }else{
                foreach ($list as $k => $v) {
                    $vidd=$v['id'];
                    if($ww->where($vidd.'=special_id and uid='.$uid)->find()){
                        $v['create_time']=date("m-d H:i",$v['create_time']);
                        $v['is_like']=1;
                        $liio[]=$v;
                    }else{
                        $v['create_time']=date("m-d H:i",$v['create_time']);
                        $v['is_like']=0;
                        $liio[]=$v;
                    }
                }
        }
        $li['hot']=$liio;
        $li['pic']=$lii;
        $this->renderSuccess('专辑评论列表',$li);
    }
    public function review($id,$type=0,$flag=0){//$type=2 直播，3 录播 机构视频
        $page = 0;
        if($flag == 1){
            $order = 'a.review_id desc';//最热
            $rows = 3;
        }elseif($flag == 0){
            $order = 'a.create_time desc';//最新
            $rows = 20;
        }
		
        $arr = array('a.video_id','a.orgcomment_id','a.live_id','a.recorded_id');//动态类型数组
        $map[$arr[intval($type)]] = $id;
        if($type == 1){
            $orgnization_video = M('orgnization_video')->field('org_id')->where('id='.$id)->find();
            $orgnization_video = implode(',',$orgnization_video);
            $map['o.org_id'] = $orgnization_video;
        }
        $select=M('review')->alias('a')
                ->join('__MEMBER__ b ON b.uid=a.uid')
                ->join('__MEMBER_ORG__ o on o.uid=a.uid')
                ->field('a.id,a.uid,a.create_time,a.content,a.review_id,o.nickname,b.avatar')
                ->page($page,$rows)
                ->where($map)
                ->group('a.id')
                ->order($order)
                ->select();
 
        return $select;
    }
    public function review_info($id,$type=0,$uid){
        if($type == 4 ||  $type ==10){
            $type = 0;
        }
        $arr = array('comment_id','comment_id','live','comment_id');
        $map[$arr[$type]] = $id;
        $map['uid'] = $uid;
        // var_dump($id);exit;
        $select = M('review_info')->where($map)->find();
        return $select;
    }
    /**
     *@author lisk 2017-04-11
     *@param  视频详情页评论列表  机构视频orgvideo_id 1 直播2 录播3
     *@return JSON
     */
    public function Comments(){
        $id = data_isset($this->post_origin_data['id'],'intval');
        $orgvideo_id = data_isset($this->post_origin_data['type'],'intval',0);
        if(empty($id)){
                $result = array('status'=>1133,'info'=>'ID不能为空');
                parent::put_post($result); 
        }
        // if($orgvideo_id == 1){
        //     $newlist = $this->review_org_video($id);
        // }
        $newlist = $this->review($id,$orgvideo_id,0);//$flag=0 最新评论，$flag1最热评论
        $li['count'] = count($newlist);
        if($li['count'] == 0){
            $result = array('status'=>0,'info'=>'没有更多了');
            parent::put_post($result);
        }
        if($this->user_uid > 0){
            $uid = $this->user_uid;
        }else{
            $uid = is_login();
        }
        if($uid > 0){
            //评论动态
            foreach ($newlist as &$v) {
                $review_info = $this->review_info($v['id'], $orgvideo_id,$uid);
                
                if(!empty($review_info)){
                    $v['is_like'] = 1;                        
                }else{
                    $v['is_like'] = 0;     
                }
                $v['create_time'] = date("m-d H:i",$v['create_time']);
            }
        }
            $hotlist = $this->review($id,$orgvideo_id,1);//$flag=0 最新评论，$flag1最热评论
        if($uid > 0){    
            foreach ($hotlist as &$v) {
                $review_info = $this->review_info($v['id'], $orgvideo_id,$uid);
                if(!empty($review_info)){
                    $v['is_like'] = 1;                        
                }else{
                    $v['is_like'] = 0;     
                }
                $v['create_time'] = date("m-d H:i",$v['create_time']);
            }
        }
        $li['hot'] = $hotlist;
        $li['pic'] = $newlist;
        $li = parent::format_data($li);
        $result = array('status'=>1,'data'=>$li,'info'=>'评论列表');
        parent::put_post($result);
    }
    /**
     * 代码待优化
     *@author lisk 2017年6月23日13:34:58
     *@param APP所有评论接口结合  
     *@return JSON
     */
    public function SynthesizeComment(){
        $uid =  parent::check_login();
        //作品ID
        $work_id = data_isset($this->post_origin_data['id'],'intval');
        if(empty($work_id)) {
                $result = array('status'=>1059,'info'=>'id为空');
                parent::put_post($result);
            }
        //内容
        $content = data_isset($this->post_origin_data['content'],'trim');
        if(empty($content)) {
                $result = array('status'=>1070,'info'=>'请输入内容');
                parent::put_post($result);
            }
        //任务id，如果是老师批阅作业，需要添加任务id
        $task_id = data_isset($this->post_origin_data['task_id'],'intval');
        //类型学生作业传0
        $type = data_isset($this->post_origin_data['type'],'intval');
        // if(empty($type)) {
        //         $result = array('status'=>1070,'info'=>'类型不能为空');
        //         parent::put_post($result);
        //     }
        $Comment = M('review');
        //评论回复传1
        $reply_content = data_isset($this->post_origin_data['reply_content'],'intval');
        $data['uid'] = $uid;
        $data['create_time'] = NOW_TIME;
        if(empty($type)){
            $content_one = M('content')->where('id='.$work_id)->find();
            if(empty($content_one)){
                $result = array('status'=>1059,'info'=>'id不存在');
                parent::put_post($result);
            }
            $data['work_id'] = $work_id;
            $data['content'] = rawurlencode($content);
            $data['task_id'] = $content_one['task_id'];
            $data['to_uid'] = $content_one['uid'];
            $data['review'] = 0;
            if(!empty($task_id)){
                $data['task_id_id'] = $task_id;
            }
            $Comment = M('comment');
            if($reply_content == 1){
                $li = $Comment->field('work_id')->where('id='.$work_id)->find();
                $data['work_id'] = implode(',',$li);
                $data['parent_id'] = $work_id;
            }
            if($Comment->add($data)){
                if($data['task_id_id']){//给作业发布评语 推送 201706
                    $logic = new ContentLogic();
                    $logic->send_comment_inform($uid, $data['to_uid'],$content_one['org_id'], $work_id,$task_id);
                }
                if(!$content_one['is_read']){
                    $map['is_read'] = 1;
                }
                $map['comments'] = $content_one['comments'] + 1;
                $Content = M('Content');
                $Content->where('id='.$work_id)->save($map);
                if($type == 3){
                    M('live_recorded')->where('id='.$work_id)->setInc('comments');
                }
                $result = array('status'=>1,'info'=>'评论成功');
                parent::put_post($result);
            }
            $result = array('status'=>0,'info'=>'评论失败');
            parent::put_post($result);
            //外视频 
        }elseif($type == 4){
            $data['video_id'] = $work_id;
            if($reply_content == 1){
                $li = $Comment->field('video_id')->where('id='.$work_id)->find();
                $data['video_id'] = implode(',',$li);
                $data['parent_id'] = $work_id;
            }
            $data['content'] = $content;
                if($Comment->add($data)){
                    $Content = M('live_video');
                    $Content->where('id='.$work_id)->setInc('content');
                    $result = array('status'=>1,'info'=>'评论成功');
                }else{
                    $result = array('status'=>0,'info'=>'评论失败');
                }
                parent::put_post($result);
        }elseif($type == 10){
            $data['special_id'] = $work_id;
            if($reply_content == 1){
                $li = $Comment->field('special_id')->where('id='.$work_id)->find();
                $data['special_id'] = implode(',',$li);
                $data['parent_id'] = $work_id;
            } 
            $data['content'] = $content;
                if($Comment->add($data)){
                    $special_video = M('special_video');
                    $special_video->where('id='.$work_id)->setInc('content');
                    $result = array('status'=>1,'info'=>'评论成功');
                }else{
                    $result = array('status'=>0,'info'=>'评论失败');
                }
                parent::put_post($result);
        }else{
            $Comment = M('review');
            if(empty($type)){
                $data['video_id'] = $work_id;
            }elseif($type == 1){

                if($reply_content == 1){
                    $recorded_id = implode(',',$Comment->field('orgcomment_id')->where('id='.$work_id)->find());
                    $data['parent_id'] = $work_id;
                    $data['orgcomment_id'] = $recorded_id;
                }else{
                $orgcomment_id = implode(',',M('orgnization_video')->field('id')->where('id='.$work_id)->find());
                    $data['orgcomment_id'] = $orgcomment_id;
                }
                    
            }elseif($type == 2){
                if($reply_content == 1){
                    $recorded_id = implode(',',$Comment->field('live_id')->where('id='.$work_id)->find());
                    // var_dump($recorded_id);exit;
                    $data['recorded_id'] = $recorded_id;
                    $data['live_id'] = $recorded_id;
                }else{
                    $data['recorded_id'] = $work_id;
                }
                // var_dump($data);exit;

            }elseif($type == 3){
                if($reply_content == 1){
                    $recorded_id = implode(',',$Comment->field('recorded_id')->where('id='.$work_id)->find());
                    $data['recorded_id'] = $recorded_id;
                }else{
                    $data['recorded_id'] = $work_id;
                }
            }
            if($reply_content == 1){
                $data['parent_id'] = $work_id;
            }
            $data['content'] = $content;
                if($Comment->add($data)){
                    if(empty($type)){
                        $Content = M('live_video');
                        $Content->where('id='.$work_id)->setInc('content');
                    }else{
                        if($type == 1){
                                $Content = M('orgnization_video');
                                $Content->where('id='.$work_id)->setInc('comments');
                        }elseif($type == 2){
                                $Content = M('live');
                                $Content->where('id='.$work_id)->setInc('comments');
                        }elseif($type == 3){
                                $Content = M('live_recorded');
                                $Content->where('id='.$work_id)->setInc('comments');
                        }
                    }
                    
                    $result = array('status'=>1,'info'=>'评论成功');
                }else{
                    $result = array('status'=>0,'info'=>'评论失败');
                }
                    parent::put_post($result);
        }
    }
    /**
     *@author lisk 2017年6月23日13:34:58
     *@param APP所有评论列表接口结合  
     *@return JSON
     */
    public function SynthesizeList(){
        $uid =  parent::check_login();
        //作品ID
        $work_id = data_isset($this->post_origin_data['id'],'intval');
        // log_new($this->post_origin_data,444);
        //类型
        $type = data_isset($this->post_origin_data['type'],'intval',0);
        // log_new($type,444);
        if(empty($work_id)) {
            $result = array('status'=>1059,'info'=>'作品id为空');
            parent::put_post($result);
            }
        $Comment = M('comment');
        $content = M('content');
        if(empty($type)){
            $org_id = $content->field('org_id')->where('id='.$work_id)->find();
            if($org_id['org_id'] == 0){
                $org_id = M('live_recorded')->field('org_id')->where('id='.$work_id)->find();
            }
            $org_id = implode(',',$org_id);
            $ContentModel = new ContentModel();
            if($org_id['org_id'] == 0){

                //动态
                $list = $ContentModel->commentlist2($work_id,1,$uid);   //最新
                $list1 = $ContentModel->commentlist2($work_id,2,$uid);   //热门

            }else{
                //机构动态
                $map['o.org_id'] = $org_id;
                $map['c.work_id'] = $work_id;
                $map['c.task_id_id'] = 0;
                $list = $ContentModel->Comment($map,1,$uid); //最新
                $list1 = $ContentModel->Comment($map,2,$uid);  //热门
            }
            if(count($list) == 0) {
                $result = array('status'=>1056,'info'=>'没有更多了');
                parent::put_post($result);
            }
            //设置默认头像
            $Api = new UserApi;
            $listi = $Api->setDefaultAvatar($list);

            $lista['sum'] = $listi['sum'];
            unset($listi['sum']);
            unset($list1['sum']);
            $coeee = M('review_info');
            foreach ($listi as $k => $v) {
                if(array_key_exists($v['like'],$v)){
                }else{
                    $listi[$k]['like'] = intval($v['review']);
                }
                    unset($listi[$k]['review']);
                    $newcomment_id = $v['id'];
                    if(empty($uid)){

                        $list11['is_like'] = -1;
                        $listi[$k] = array_merge($listi[$k],$list11);
                        
                    }else{
                        if($coeee->where($newcomment_id.'=comment_id and uid='.$uid)->find()){

                            $list11['is_like'] = 1;
                            $listi[$k] = array_merge($listi[$k],$list11);
                        }else{
                            
                            $list11['is_like'] = 0;
                            $listi[$k] = array_merge($listi[$k],$list11);
                        }
                    }
            } 
            foreach ($list1 as $k=>$v) {
                $list1[$k]['like'] = $v['review'];
                $hotcomment_id = $v['id'];
                    if(empty($uid)){
                        $list11['is_like'] = -1;
                        $list1[$k] = array_merge($list1[$k],$list11);
                        
                    }else{

                        if($coeee->where($hotcomment_id.'=state_id and uid='.$uid)->find()){
                            $list11['is_like'] = 1;
                            $list1[$k] = array_merge($list1[$k],$list11);
                        }else{
                            
                            $list11['is_like'] = 0;
                            $list1[$k] = array_merge($list1[$k],$list11);
                        }
                    }
            }

            $Api = new UserApi;
            $list1 = $Api->setDefaultAvatar($list1);
            foreach ($list1 as &$row) {
                $row['content'] = rawurldecode($row['content']);
            }
            $listi = $Api->setDefaultAvatar($listi);
            foreach ($listi as &$row) {
                $row['content'] = rawurldecode($row['content']);
            }
            if(empty($list1)){
                $list1 = array();
            }
             $lista['hot'] = $list1;
             $lista['newest'] = $listi;       
             $lista = parent::format_data($lista);        
                $result = array('status'=>1,'data'=>$lista,'info'=>'评论列表');
                parent::put_post($result);
        }else{
            $CourseModel = new CourseModel();   
            $newlist = $CourseModel->review($work_id,$type,0,$uid);//$flag=0 最新评论，$flag1最热评论
            $Comments_live['sum'] = $newlist['sum'];
            if($type == 1){
                    $count['comments'] = $newlist['sum'];
                    $info = M('orgnization_video')->where('id='.$work_id)->save($count);
            }
            unset($newlist['sum']);
            if($Comments_live['sum'] == 0){
                $result = array('status'=>1056,'info'=>'没有更多了');
                parent::put_post($result);
                echo "string";exit;
            }
            if($uid > 0){
                //评论动态
                foreach ($newlist as $k =>$v) {
                    unset($v['review_id']);
                    $review_info = $this->review_info($v['id'], $type,$uid);
                    if($v['uid'] == $uid){
                        $newlist[$k]['is_uid'] = 1; 
                    }else{
                        $newlist[$k]['is_uid'] = 0; 
                    }
                    if(!empty($review_info)){
                        $newlist[$k]['is_like'] = 1;                        
                    }else{
                        $newlist[$k]['is_like'] = 0;     
                    }
                    // $newlist[$k]['like'] = $v['review_id'];
                    // unset($newlist[$k]['review_id']);
                }
            }
            $hotlist = $CourseModel->review($work_id,$type,1,$uid);//$flag=0 最新评论，$flag1最热评论
            if($uid > 0){    
                foreach ($hotlist as $k => $v) {
                    $review_info = $this->review_info($v['id'], $orgvideo_id,$uid);
                    if($v['uid'] == $uid){
                        $hotlist[$k]['is_uid'] = 1; 
                    }else{
                        $hotlist[$k]['is_uid'] = 0; 
                    }
                    if(!empty($review_info)){
                        $hotlist[$k]['is_like'] = 1;                        
                    }else{
                        $hotlist[$k]['is_like'] = 0;     
                    }
                    $hotlist[$k]['like'] = $v['review_id'];
                    unset($hotlist[$k]['review_id']);
                }
            }
            if(empty($hotlist)){
                $hotlist = array();
            }
            $Comments_live['hot'] = $hotlist;
            $Comments_live['newest'] = $newlist;
            $Comments_live = parent::format_data($Comments_live);
            $result = array('status'=>1,'data'=>$Comments_live,'info'=>'评论列表');
            parent::put_post($result);
        }
    }
    /**
     *@author lisk 2017-04-10
     *@param 视频分享次数 机构视频
     *@return JSON
     */
    public function share(){
        $id = data_isset($this->post_origin_data['id'],'intval');
        $orgvideo_id = data_isset($this->post_origin_data['type'],'intval',0);
        if(empty($id)){
                $result = array('status'=>0,'info'=>'视频ID不能为空');
                parent::put_post($result);
        }
        if(empty($orgvideo_id)){

            $select=M('live_video');
            if($select->where('id='.$id)->setInc('share')){
                $result = array('status'=>1,'info'=>'分享成功');
            }else{
                $result = array('status'=>0,'info'=>'分享失败');
            }
        }else{
            $select=M('orgnization_video');
            if($select->where('id='.$id)->setInc('share')){
                $result = array('status'=>1,'info'=>'分享成功');
            }else{
                $result = array('status'=>0,'info'=>'分享失败');
            }
        }
        parent::put_post($result);
        // $list=$select->where('id='.$id)->setInc('content');
    }
     /**
     *@author lisk 2017-04-15
     *@param 全部视频
     *@return JSON
     */
    public function AllVideo(){
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',10);
        $uid = data_isset($this->post_origin_data['uid'],'intval');
        if(empty($uid)){
                $result = array('status'=>0,'info'=>'用户ID不能为空');
                parent::put_post($result);
        }
        $select=M('live_video');
        $list=$select->alias('a')
                        ->page($page,$rows)
                        ->join('__MEMBER__ b ON b.uid=a.uid')
                        ->field('b.nickname,b.avatar,a.id,a.cover_url,a.video_url,a.title,a.video_time')
                        ->where('a.uid='.$uid)
                        ->select();
                $list = parent::format_data($list);
                $result = array('status'=>1,'data'=>$list,'info'=>'全部视频');
                parent::put_post($result);
    }
    /**
     *@author lisk 2017-04-08
     *@param 视频详情页  考题（活动版）占时
     *@return JSON
     */
    public function videodetails(){

        $id = data_isset($this->post_origin_data['id'],'intval');
        $orgvideo_id = data_isset($this->post_origin_data['type'],'intval',0);
        if(empty($id)){
            $this->renderFailed('视频ID不能为空');
        }
        $list = M('live_video');
        // if($orgvideo_id)
        // if(empty($orgvideo_id)){
        //     $select = $list->alias('a')
        //                 ->join('__MEMBER__ b ON a.uid=b.uid')
        //                 ->field('a.id,a.cover_url,a.video_url,a.title,a.create_time,a.likes,a.share,a.content,a.video_time,b.uid,b.nickname,b.avatar')
        //                 ->where('a.id='.$id)
        //                 ->select();
        // }else
        if($orgvideo_id == 1){
            $orgnization_video = M('orgnization_video')->field('org_id')->where('id='.$id)->find();
            $orgnization_video = implode(',',$orgnization_video);
            $select = $this->orgvideo($id,$orgnization_video);
        }elseif($orgvideo_id == 5){
            $examination = M('examination')->field('id,org_id,title,description,content_json,template as template_id')->where('id='.$id)->find();
                $list = parent::format_data($examination);
                $result = array('status'=>1,'data'=>$list,'info'=>'详情页');
                parent::put_post($result);
        }elseif($orgvideo_id == 2){
            //知识点详情页
            $chapter_video = M('chapter_video')->field('id,title,description,cover_url,video_url')->where('id='.$id)->find();
            if(empty($chapter_video)){
                $result = array('status'=>1056,'info'=>'不存在');
                parent::put_post($result);
            }
                $list = parent::format_data($chapter_video);
                $result = array('status'=>1,'data'=>$list,'info'=>'详情页');
                parent::put_post($result);
        }else{
            $select = $list->alias('a')
                        ->join('__MEMBER__ b ON a.uid=b.uid')
                        ->field('a.id,a.cover_url,a.video_url,a.title,a.create_time,a.likes,a.share,a.content,a.video_time,b.uid,b.nickname,b.avatar')
                        ->where('a.id='.$id)
                        ->select();
        }
         $info = array_shift($select);
         $video_uid = $info['uid'];

         $info['video_time']=date("i:s",$info['video_time']);
         $info['create_time']=date("m-d H:i",$info['create_time']);
        if($this->user_uid > 0){
            $uid = $this->user_uid;
        }else{
            $uid = is_login();
        }
         if(empty($uid)){
            $info['is_like'] =-1;
         }else{
            if(empty($orgvideo_id)){

                if(M('review')->where($uid.'=uid and video_idd='.$id)->find()){
                    $info['is_like'] = 1;
                }else{
                    $info['is_like'] = 0;
                }
            }else{
                if(M('review')->where($uid.'=uid and orgvideo_id='.$id)->find()){
                    $info['is_like'] = 1;
                }else{
                    $info['is_like'] = 0;
                }
            }
         }

        if(empty($uid)){
            $info['is_pay']=0;
        }else{
            if($video_uid == $uid){
                $info['is_pay']=1;
            }else{

                $info_follow=M('follow')->where($video_uid.'=follow_who and who_follow='.$uid)->find();
                if(empty($info_follow)){
                    $info['is_pay']=0;
                }else{
                    $info['is_pay']=1;
                }
            }
        }

        $info = parent::format_data($info);
        $result = array('status'=>1,'data'=>$info,'info'=>'视频详情页');
        parent::put_post($result); 
    }
    /**
     *@author lisk 2017-04-08
     *@param 视频评论  机构视频  直播orgvideo_id为1机构视频 2为直播 3为录播
     *@return JSON
     */
    public function pubComment() {
        // if(IS_POST) {
            $uid =  parent::check_login();
            $id = data_isset($this->post_origin_data['id'],'intval');
            $orgvideo_id = data_isset($this->post_origin_data['type'],'intval',0);
            if(empty($id)) {
                $result = array('status'=>1096,'info'=>'视频id为空');
                parent::put_post($result);
            }   
            $content = data_isset($this->post_origin_data['content'],'trim');
            if(empty($content)) {
                $result = array('status'=>1097,'info'=>'请输入内容');
                parent::put_post($result);
            }
            $data['uid'] = $uid;

            if(empty($orgvideo_id)){
                $data['video_id'] = $id;
            }elseif($orgvideo_id == 1){
                $data['orgcomment_id'] = $id;
            }elseif($orgvideo_id == 2){
                $data['live_id'] = $id;
            }elseif($orgvideo_id == 3){
                $data['recorded_id'] = $id;
            }


            $data['content'] = $content;
            $data['create_time'] = time();
            $Comment = M('review');

                if($Comment->add($data)){
                    if(empty($orgvideo_id)){
                        $Content = M('live_video');
                        $Content->where('id='.$id)->setInc('content');
                    }elseif($orgvideo_id == 1){
                        $Content = M('orgnization_video');
                        $Content->where('id='.$id)->setInc('comments');
                    }elseif($orgvideo_id == 2){
                        $Content = M('live');
                        $Content->where('id='.$id)->setInc('comments');
                    }elseif($orgvideo_id == 3){
                        $Content = M('live_recorded');
                        $Content->where('id='.$id)->setInc('comments');
                    }
                    $result = array('status'=>1,'info'=>'评论成功');
                }else{
                    $result = array('status'=>0,'info'=>'评论失败');
                }
                    parent::put_post($result);
    }
    /**
     *@author lisk 2017-04-08
     *@param 视频显示
     *@return JSON
     */
    public function videodisplay(){
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',10);
        $form_id = data_isset($this->post_origin_data['form_id'],'intval');
        if(empty($form_id)){
                $result = array('status'=>1032,'info'=>'类型不能为空');
                parent::put_post($result);
        }
        $list=M('live_video');
        $select=$list->alias('a')
                    ->page($page, $rows)
                    ->join('__MEMBER__ b ON b.uid=a.uid')
                    ->field('b.nickname,b.avatar,a.id,a.cover_url,a.video_url,a.title,a.video_time')
                    ->where('a.is_delete=0 and a.form_id = '.$form_id)
                    // ->where('a.is_delete =0')
                    ->order('a.create_time desc')
                    ->select();
        foreach ($select as $k => $v) {
            $video_time=date("i:s",$v['video_time']); 
            $v['video_time']=$video_time;
            $list_info[]= $v;

        }
        if(count($list_info)==0){
                $result = array('status'=>1056,'info'=>'没有更多了');
            }else{
                $list_info = parent::format_data($list_info);
                $result = array('status'=>1,'data'=>$list_info,'info'=>'视频显示');
            }
                parent::put_post($result);
    }
    /**
     *@author lisk 2017-04-08
     *@param 视频类型
     *@return JSON
     */
    public function form(){
        $list=M('live_video_form');
        $select=$list->alias('a')
                    ->field('a.id,a.moid')
                    ->select();
                   
        if(count($select)==0){
                $result = array('status'=>0,'info'=>'没有更多了');
            }else{
                $list = parent::format_data($list);
                $result = array('status'=>1,'data'=>$select,'info'=>'视频类型');
            }
                parent::put_post($result);
    }
    /**
     *@author lisk 2017-04-08
     *@param 上传视频
     *@return JSON
     */
    public function video(){
        // if(IS_POST){
            $uid =  parent::check_login();
            $map['uid']=$uid;
            $title = data_isset($this->post_origin_data['title'],'trim');
            $map['title']=$title;
            if(empty($title)){
                $result = array('status'=>1079,'info'=>'描述不能为空');
                parent::put_post($result);
            }
            $form_id = data_isset($this->post_origin_data['form_id'],'intval');
            $map['form_id']=$form_id;
            if(empty($form_id)){
                $result = array('status'=>1050,'info'=>'标签不能为空');
                parent::put_post($result);
            }
            $video_url = data_isset($this->post_origin_data['video_url'],'trim');
            $map['cover_url']=data_isset($this->post_origin_data['cover_url'],'trim');
            $map['video_url']=$video_url;
            if(empty($video_url)){
                $result = array('status'=>1096,'info'=>'视频不能为空');
                parent::put_post($result);
            }
            $map['create_time']=time();
            $map['video_time']=I('video_time','','intval');
            $list=M('live_video');
            $synchronization = data_isset($this->post_origin_data['id'],'trim');
            // $this->renderSuccess('上传成功',$map);
            if($info=$list->add($map)){
                if(!empty($synchronization)){
                    $select_info = $this->synchronization_info($synchronization,$map,$info);
                }
                $result = array('status'=>1,'info'=>'上传成功');
            }else{
                $result = array('status'=>0,'info'=>'上传失败');
            }
                parent::put_post($result);
        // }
    }
    public function synchronization_info($synchronization,$map,$info){
        unset($map['form_id']);
        unset($map['video_time']);
        $select = M('orgnization_video');
        $a=array();
        foreach (explode(',',$synchronization) as $s) {
            $li=implode( explode(',',$s) );
            $map['org_id'] = $li;
            $list = $select->add($map);
        }
        $result = array('status'=>1,'info'=>'添加成功');
        parent::put_post($result);
    }
    /**
     *@author lisk 2017-04-11
     *@param 视频点赞  
     *@return JSON
     */
    public function videounlike(){
        $uid =  parent::check_login();
        $id = data_isset($this->post_origin_data['id'],'intval');
        $orgvideo_id = data_isset($this->post_origin_data['type'],'intval',0);
        if(empty($id)){
                $result = array('status'=>1133,'info'=>'视频ID不能为空');
                parent::put_post($result); 
        }
        $map['uid'] = $uid;
        $map['video_idd'] = $id;
        $select = M('review');
        $org_video = M('orgnization_video');
        $live_video = M('live_video');
        if($orgvideo_id == 1){
            if($select->where($id.'=orgvideo_id and uid='.$uid)->delete()){
                $org_video->where('id='.$id)->setDec('likes');
                $result = array('status'=>1,'info'=>'取消点赞');
            }else{
                $map_info['uid']=$uid;
                $map_info['orgvideo_id']=$id;
                $map_info['create_time']=time();
                $org_video->where('id='.$id)->setInc('likes');
                $select->add($map_info);
                $result = array('status'=>1,'info'=>'点赞成功');
            }
                parent::put_post($result);
        }elseif($orgvideo_id == 11){
            $info['uid'] = $uid;
            $info['examination_id'] = $id;
            $examination = M('examination');
            if($select->where($map)->delete()){
                $examination->where('id='.$id)->setDec('likes');
                $result = array('status'=>1,'info'=>'取消点赞');
            }else{
                $info['create_time'] = time();
                $examination->where('id='.$id)->setInc('likes');
                $select->add($map);
                $result = array('status'=>1,'info'=>'点赞成功');
            }
        }
        else{
            if($select->where($map)->delete()){
                $live_video->where('id='.$id)->setDec('likes');
                $result = array('status'=>1,'info'=>'取消点赞');
            }else{
                $map['create_time']=time();
                $live_video->where('id='.$id)->setInc('likes');
                $select->add($map);
                $result = array('status'=>1,'info'=>'点赞成功');
            }
                parent::put_post($result);
        }
        // if(empty($orgvideo_id)){

        //     if($select->where($map)->delete()){
        //         $live_video->where('id='.$id)->setDec('likes');
        //         $result = array('status'=>1,'info'=>'取消点赞');
        //     }else{
        //         $map['create_time']=time();
        //         $live_video->where('id='.$id)->setInc('likes');
        //         $select->add($map);
        //         $result = array('status'=>1,'info'=>'点赞成功');
        //     }
        //         parent::put_post($result);
        // }
        // else{
        //     if($select->where($id.'=orgvideo_id and uid='.$uid)->delete()){
        //         $org_video->where('id='.$id)->setDec('likes');
        //         $result = array('status'=>1,'info'=>'取消点赞');
        //     }else{
        //         $map_info['uid']=$uid;
        //         $map_info['orgvideo_id']=$id;
        //         $map_info['create_time']=time();
        //         $org_video->where('id='.$id)->setInc('likes');
        //         $select->add($map_info);
        //         $result = array('status'=>1,'info'=>'点赞成功');
        //     }
        //         parent::put_post($result);
        // }
    }
    /**
     *@author lisk 2017-04-06
     *@param 视频评论取消点赞 点赞   机构视频
     *@return JSON
     */
    public function unlike() {
        // if(IS_POST) {
            $uid =  parent::check_login();
            $review = data_isset($this->post_origin_data['id'],'intval');
            $orgvideo_id = data_isset($this->post_origin_data['type'],'intval',0);
            if(empty($review)) {
                $result = array('status'=>1131,'info'=>'评论id为空');
                parent::put_post($result);
            }
            $map['uid'] = $uid;
            $Likes = M('review');
            $select=M('review_info');
            if(empty($orgvideo_id)){
                $map['comment_id'] = $review;

                if($select->where($map)->delete()) {
                    //更新评论点赞数
                    // M('review')->where('id='.$review)->setDec('review_id');
                    M('comment')->where('id='.$review)->setDec('review');
                    $result = array('status'=>1,'info'=>'取消点赞');
                    parent::put_post($result);
                }
                else {
                    $map['create_time'] = time();
                    $select->add($map);
                    // M('review')->where('id='.$review)->setInc('review_id');
                    M('comment')->where('id='.$review)->setInc('review');

                    $result = array('status'=>1,'info'=>'点赞成功');
                    parent::put_post($result);
                }
            }
            else{
                //评论ID
                $map['comment_id'] = $review;
                // $map['org_id'] = $review;
                if($select->where($map)->delete()) {
                    //更新评论点赞数
                    $info = M('review')->where('id='.$review)->setDec('review_id');
                    $result = array('status'=>1,'info'=>'取消点赞');
                }
                else {
                    $map['create_time'] = time();
                    $select->add($map);
                    $info =  M('review')->where('id='.$review)->setInc('review_id');
                    $result = array('status'=>1,'info'=>'点赞成功');
                }
                    parent::put_post($result);
            }
        // }
    }
    /**
     *@author lisk 2017-04-06
     *@param 视频评论取消点赞 点赞   机构视频  2017年7月7日14:19:19 解决iOS测试线上冲突
     *@return JSON
     */
    public function NewUnIike() {
        // if(IS_POST) {
            $uid =  parent::check_login();
            $review = data_isset($this->post_origin_data['id'],'intval');
            $orgvideo_id = data_isset($this->post_origin_data['type'],'intval',0);
            if(empty($review)) {
                $result = array('status'=>1131,'info'=>'评论id为空');
                parent::put_post($result);
            }
            $map['uid'] = $uid;
            $Likes = M('review');
            $select=M('review_info');
            if(empty($orgvideo_id)){
                $map['comment_id'] = $review;

                if($select->where($map)->delete()) {
                    //更新评论点赞数
                    // M('review')->where('id='.$review)->setDec('review_id');
                    M('comment')->where('id='.$review)->setDec('review');
                    $result = array('status'=>1,'info'=>'取消点赞');
                    parent::put_post($result);
                }
                else {
                    $map['create_time'] = time();
                    $select->add($map);
                    // M('review')->where('id='.$review)->setInc('review_id');
                    M('comment')->where('id='.$review)->setInc('review');

                    $result = array('status'=>1,'info'=>'点赞成功');
                    parent::put_post($result);
                }
            }
            else{
                //评论ID
                $map['comment_id'] = $review;
                // $map['org_id'] = $review;
                    log_new(55555555555);
                if($select->where($map)->delete()) {
                    //更新评论点赞数
                    $info = M('review')->where('id='.$review)->setDec('review_id');
                    $result = array('status'=>1,'info'=>'取消点赞');
                }
                else {
                    $map['create_time'] = time();
                    $select->add($map);
                    $info =  M('review')->where('id='.$review)->setInc('review_id');
                    $result = array('status'=>1,'info'=>'点赞成功');
                }
                    parent::put_post($result);
            }
        // }
    }
    // 获取课程列表
    public function courseList()
    {
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',20);
        $course_category_id = data_isset($this->post_origin_data['course_category_id'],'intval',12);
        $map = array();

        if($course_category_id != 12)
        {
            // 课程分类下的 课程列表数据
            $map['course_category_id'] = $course_category_id;

            $Course = M('Course');
            $select = M('course_purchase');

            $li = $Course->alias('c')
                                    ->join('dbh_member m ON m.uid = c.uid')
                                    // ->fetchSql(true)
                                    ->join('dbh_course_category cc ON cc.id = c.course_category_id')
                                    ->field(array('c.id','course_category_name','course_name','course_cover_url','tags','price','nickname','avatar'))
                                    ->where($map)
                                    ->order('id desc')
                                    ->limit(($page - 1) * $rows, $rows)
                                    ->select();
            $uid =  parent::check_login();
            if(empty($uid)){
                foreach ($li as $k => $v) {
                        $v['buy']='-1';
                        $courseDatas[]=$v;
                }
            }else{
                foreach ($li as $k => $v) {
                    $idd=$v['id'];
                    if($select->where( $idd.'=coures and uid='.$uid)->find()){
                        $v['buy']='1';
                        $courseDatas[]=$v;
                    }else{
                        $v['buy']='0';
                        $courseDatas[]=$v;
                    }
                }
            }

            $this->addTagsForCourse($courseDatas);
        }
        else
        {
            // 推荐分类下的 课程列表数据
            $this->recommendCourseList(0);
        }

        if(!count($courseDatas))
        {
                $result = array('status'=>0,'info'=>'没有更多了');
        }else{
                $result = array('status'=>1,'data'=>$courseDatas,'info'=>'查询成功');
        }
                parent::put_post($result);
    }

    // 获取推荐课程列表  (Banner 数据 app端 轮播图数据)
    public function recommendCourseList($interface = 1)
    {
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',20);
        $course_category_id = data_isset($this->post_origin_data['course_category_id'],'intval',13);
        $Course = M('Course');

        $map = array();

        // $interface = 0;

        // $interface 是否是接口访问 1 是 0 否 (当接口直接访问推荐课程列表时,没有推荐数据)
        // if($course_category_id == 12 && $interface)
        // {
        //     $this->renderFailed('没有更多了'); 
        // }

        if($course_category_id != 12)
        {
            $map['course_category_id'] = $course_category_id;
            $rows = 5;
        }

        $map['is_recommend'] = 1;

        $recommendCourseDatas = $Course->alias('c')
                                        ->join('dbh_member m ON m.uid = c.uid')
                                        // ->fetchSql(true)
                                        ->join('dbh_course_category cc ON cc.id = c.course_category_id')
                                        ->field(array('c.id','course_category_name','course_name','course_cover_url','tags','price','nickname','avatar'))
                                        ->where($map)
                                        ->order('id desc')
                                        ->limit(($page - 1) * $rows, $rows)
                                        ->select();
        // 为课程添加课程标签
        $this->addTagsForCourse($recommendCourseDatas);

        if(!count($recommendCourseDatas))
        {
                $result = array('status'=>0,'info'=>'没有更多了');
                parent::put_post($result);
        }
                $recommendCourseDatas = parent::format_data($recommendCourseDatas);
                $result = array('status'=>1,'data'=>$recommendCourseDatas,'info'=>'查询成功');
                parent::put_post($result);
    }

    public function courseCategoryList()
    {
        $CourseCategory = M('CourseCategory');

        $courseCategoryDatas = $this->getCourseCategoryInfo(0);
                $courseCategoryDatas = parent::format_data($courseCategoryDatas);
                $result = array('status'=>1,'data'=>$courseCategoryDatas,'info'=>'查询成功');
                parent::put_post($result);
    }

    private function getCourseCategoryInfo($parent_id)
    {
        $CourseCategory = M('CourseCategory');

        $courseCategoryDatas = $CourseCategory->where(array('parent_id' => $parent_id))->select();

        foreach ($courseCategoryDatas as $key => $courseCategoryData) 
        {
            $courseCategoryDatas[$key]['subCategory'] = $this->getCourseCategoryInfo($courseCategoryData['id']);
        }

        return $courseCategoryDatas ? $courseCategoryDatas : array();
    }

    // 获取课程顶层分类
    public function topCourseCategoryList()
    {
        $CourseCategory = M('CourseCategory');

        // $icon_url = "concat('".C('HOST_URL')."',course_category_icon) icon_url";
        // $icon_select_url = "concat('".C('HOST_URL')."',course_category_select_icon) icon_select_url";

        $courseCategoryDatas = $CourseCategory->field(array('id','course_category_name,course_category_icon,course_category_select_icon,icon_url,icon_select_url '))
                                                ->where(array('parent_id' => 0,'status' => 1))
                                                ->order('sort asc')
                                                ->select();
                $courseCategoryDatas = parent::format_data($courseCategoryDatas);
                $result = array('status'=>1,'data'=>$courseCategoryDatas,'info'=>'查询成功');
                parent::put_post($result);
    }

    // 获取课程标签
    private function getCourseTagsByCourseId($course_id = 8)
    {
        $courseTagsDatas = array();

        // 获取课程标签的ID集合
        $CourseRelationTags = M('CourseRelationTags');

        $courseTagsIdDatas = $CourseRelationTags->where(array('course_id' => $course_id))->getField('course_tags_id',true);

        // 如果当前课程没有标签 那么直接返回一个空的数组
        if(!$courseTagsIdDatas)
        {
            return $courseTagsDatas;
        }

        // 获取课程标签的集合
        $CourseTags = M('CourseTags');

        $courseTagsDatas = $CourseTags->where(array( 'id' => array('in',$courseTagsIdDatas)))
                                        ->getField('course_tag_name',true);
// var_dump($courseTagsDatas);exit;
        // 返回标签集合
        return $courseTagsDatas;
    }

    // 为课程添加标签
    private function addTagsForCourse(&$courseDatas)
    {
        foreach($courseDatas as $key => $courseData)
        {
            $courseData['tags'] = $this->getCourseTagsByCourseId($courseData['id']);

            $courseDatas[$key] = $courseData;
        }
    }

    /**
     *@return  For course details
     */
    public function courseDetails()
    {
        $course_id = data_isset($this->post_origin_data['course_id'],'intval');
        $user_id = data_isset($this->post_origin_data['uid'],'intval');
        $Course = M('Course');
        // To determine the current course ID is valid
        $is_course_id = $Course->where(array('id' => $course_id))->count();
        if(!$is_course_id)
        {
                $result = array('status'=>1095,'info'=>'查无此课程或当前课程已下架');
                parent::put_post($result);
        }
        $courseData = $Course->alias('c')
                                ->join('dbh_course_abstract ca on c.id = ca.course_id')
                        //lisk  2017-04-18  添加是否rmb字段
                                ->field('c.Free_time,c.is_pav,c.id,c.uid,c.course_name,c.course_url,c.course_cover_url,c.tags,c.price,c.course_category_id,ca.applicable_user,ca.learning_target,ca.teaching_focue')
                                ->where('c.id='. $course_id)
                                ->find();

        if(empty($courseData))
        {
                $result = array('status'=>0,'info'=>'暂无此课程详细信息,请稍后尝试');
                parent::put_post($result);
        }

        // $courseData['course_target']['applicable_user'] = $courseData['applicable_user'];
        // $courseData['course_target']['applicable_user'] = array('title' => '适用人群','content' => $courseData['applicable_user']);
        $courseData['course_target'][] = array('title' => '适用人群','content' => $courseData['applicable_user']);
        // $courseData['course_target']['learning_target'] = $courseData['learning_target'];
        // $courseData['course_target']['learning_target'] = array('title' =>  '学习目标','content' => $courseData['learning_target']);
        $courseData['course_target'][] = array('title' =>  '学习目标','content' => $courseData['learning_target']);
        unset($courseData['applicable_user']);
        unset($courseData['learning_target']);

        $teaching_focue = array('title' => '课程重点','content' => $courseData['teaching_focue']);
        unset($courseData['teaching_focue']);

        // $courseData['teaching_focue']['course_focue'] = $teaching_focue;
        $courseData['teaching_focue'][] = $teaching_focue;

        $courseData['tags'] = $this->getCourseTagsByCourseId($course_id);

        // $courseData['teacher']['experience'] = $this->getMemberExperienceByUser($courseData);
        $courseData['experience'] = array('title' => '个人经历','content' => $this->getMemberExperienceByUser($courseData));

        $is_pay = M('gift_order')->where(array('combo_id' => $course_id,'uid' => $user_id,'status' => '1','type' => 2))->count();
        $apple_pay = M('gift_apple_buy')->where(array('combo_id' => $course_id,'uid' => $user_id,'type' => 4))->find();
        $is_apple_pay = 0;
        if(!empty($apple_pay)){
            if($apple_pay['update_time']>0){
                $is_apple_pay = 1;
            }else{
                $is_apple_pay = 0;
            }
        }
        $courseData['is_pay'] = ($is_pay||$is_apple_pay) ? 1 : 0;
        if($courseData['is_pay'] == 1){
            $courseData['free_time'] = 10000;
            $courseData['is_pav'] = 0;
        }
                $courseData = parent::format_data($courseData);
                $result = array('status'=>1,'data'=>$courseData,'info'=>'查询成功');
                parent::put_post($result);
    }

    // 通过用户uid和课程分类ID获取用户的个人经历 分类ID 用于区分用户的不同经历 有 则获取相应经历 无 则获取全部经历
    private function getMemberExperienceByUser($courseData = array())
    {

        $MemberExperience = M('MemberExperience');

        $memberExperienceDatas = $MemberExperience->alias('me')
                                                    ->field(array('id','member_experience_content','member_experience_time'))
                                                    ->where(array('uid' => $courseData['uid'],'course_category_id' => $courseData['course_category_id']))
                                                    ->select();

        $MemberExperienceAlbum = M('MemberExperienceAlbum');

        foreach ($memberExperienceDatas as $key => $memberExperienceData) 
        {
            $memberExperienceAlbumDatas = $MemberExperienceAlbum//->field($album_url)
                                                                    ->where(array('member_experience_id' => $memberExperienceData['id']))
                                                                    ->getField('experience_album_url',true);
                    
            foreach ($memberExperienceAlbumDatas as $ablumKey => $memberExperienceAlbumData) 
            {
                $memberExperienceAlbumDatas[$ablumKey] = C('HOST_URL').$memberExperienceAlbumData;
            }

            unset($memberExperienceDatas[$key]['id']);


            $memberExperienceDatas[$key]['member_experience_time'] = date('m月*Y',$memberExperienceData['member_experience_time']);

            $memberExperienceAlbumDatas = $memberExperienceAlbumDatas ? $memberExperienceAlbumDatas : array();
            $memberExperienceDatas[$key]['album_count'] = count($memberExperienceAlbumDatas);
            $memberExperienceDatas[$key]['album_urls'] = $memberExperienceAlbumDatas;
        }

        return $memberExperienceDatas ? $memberExperienceDatas : array();
    }

    // 获取当前用户的更多作品
    public function userCourseMore()
    {
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',5);
        $uid = data_isset($this->post_origin_data['course_category_id'],'intval');

        $Course = M('Course');

        $courseDatas = $Course->alias('c')
                                ->join('dbh_member m ON m.uid = c.uid')
                                ->field(array('c.id','course_name','course_cover_url','tags','price','nickname','avatar'))
                                ->where(array('c.uid' => $uid))
                                ->order('id desc')
                                ->limit(($page - 1) * $rows, $rows)
                                ->select();

        $this->addTagsForCourse($courseDatas);

        if(!count($courseDatas))
        {
                $result = array('status'=>0,'info'=>'没有更多了');
                parent::put_post($result);
        }
                $courseDatas = parent::format_data($courseDatas);
                $result = array('status'=>1,'data'=>$courseDatas,'info'=>'查询成功');
                parent::put_post($result);
    }
    /**
    *@author lisk 2017-04-26
    *@param  付费课程评论列表
    *@return json
    */
    public function courseComment()
    {
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',10);
        $course_id = data_isset($this->post_origin_data['course_id'],'intval');
        if(empty($course_id)){
                $result = array('status'=>0,'info'=>'课程ID为空');
                parent::put_post($result);
        }

        $CourseComment = M('CourseComment');

        $courseCommentDatas = $CourseComment->alias('c')
                                            ->page($page,$rows)
                                            ->join('dbh_member m on m.uid = c.uid')
                                            ->field(array('c.id','c.course_id','m.nickname','m.avatar','c.course_comment_content','c.create_time'))
                                            ->where(array('c.course_id' => $course_id))
                                            ->order('id desc')
                                            ->select();
                                            
        $li['count']=count($courseCommentDatas);
        $uid =  parent::check_login();
        $ww=M('review_info');
        if(empty($uid)){
                foreach ($courseCommentDatas as $k => $v) {
                    $v['create_time']=date("m-d H:i",$v['create_time']);
                    // $v['is_like']=-1;
                    $list[]=$v;
                }
        }else{
                foreach ($courseCommentDatas as $k => $v) {
                    $vid=$v['id'];
                    if($ww->where($vid.'=pay_id and uid='.$uid)->find()){
                            $v['create_time']=date("m-d H:i",$v['create_time']);
                            // $v['is_like']=1;
                            $list[]=$v;
                        
                    }else{
                        $v['create_time']=date("m-d H:i",$v['create_time']);
                        // $v['is_like']=0;
                        $list[]=$v;
                    }
                }
        }
        if(count($list)==0){
                $result = array('status'=>1056,'info'=>'没有更多了');
                parent::put_post($result);
        }
        // $courseCommentDatass = $CourseComment->alias('c')
        //                                     ->page($page,$rows)
        //                                     ->join('dbh_member m on m.uid = c.uid')
        //                                     ->field(array('c.id','c.course_id','m.nickname','m.avatar','c.course_comment_content','c.create_time','c.review'))
        //                                     ->where(array('c.course_id' => $course_id))
        //                                     ->order('c.review desc')
        //                                     ->select();
        // if(empty($uid)){
        //         foreach ($courseCommentDatass as $k => $v) {
        //             $v['create_time']=date("m-d H:i",$v['create_time']);
        //             $v['is_like']=-1;
        //             $liio[]=$v;
        //         }
        // }else{
        //         foreach ($courseCommentDatass as $k => $v) {
        //             $vidd=$v['id'];
        //             if($ww->where($vidd.'=pay_id and uid='.$uid)->find()){
        //             $v['create_time']=date("m-d H:i",$v['create_time']);
        //                 $v['is_like']=1;
        //                 $liio[]=$v;                        
        //             }else{
        //                 $v['create_time']=date("m-d H:i",$v['create_time']);
        //                         $v['is_like']=0;
        //                         $liio[]=$v;
        //             }
        //         }
        // }
        // $li['hot']=$liio;
        // $li['pic']=$lii;
                $list = parent::format_data($list);
                $result = array('status'=>1,'data'=>$list,'info'=>'查询成功');
                parent::put_post($result);
    }
    /**
    *@author lisk 2017-04-24
    *@param  付费课程评论
    *@return json
    */
   public function paycomment(){
        $uid =  parent::check_login();
        $id = data_isset($this->post_origin_data['id'],'intval');
        if(empty($id)){
                $result = array('status'=>0,'info'=>'课程ID为空');
                parent::put_post($result);
        }
        $map['course_id']=$id;
        $map['uid']=$uid;
        $content = data_isset($this->post_origin_data['content'],'trim');
        if(empty($content)){
                $result = array('status'=>0,'info'=>'评论内容为空');
                parent::put_post($result);
        }
        $course=M('course')->field('is_pav')->where('is_pav=1 and  id='.$id)->find();//var_dump($course);exit;
        $giftorder=M('gift_order');
        if(!empty($course)){
            $is_pay = $giftorder->where(array('combo_id' => $id,'uid' => $uid,'status' => '1','type' => 2))->count();
            if($is_pay == "0"){
                $result = array('status'=>0,'info'=>'购买完才能评论');
                parent::put_post($result);
            }
        }
        $map['course_comment_content']=$content;
        $map['create_time']=time();
        $select=M('course_comment');
        $list=M('course_purchase');
        if($list->where($id.'=coures and  uid='.$uid)->find()){
            if($select->add($map)){
                $select->where('id='.$id)->setInc('content');
                $result = array('status'=>1,'info'=>'评论成功');
            }else{
                $result = array('status'=>0,'info'=>'评论失败');
            }
                parent::put_post($result);
        }else{
                $result = array('status'=>0,'info'=>'请先购买');
                parent::put_post($result);
        }
   }
    /**
     *@author lisk 2017-04-24
     *@param 付费评论取消点赞 点赞
     *@return JSON
     */
    public function payunlike() {
        // if(IS_POST) {
            $uid = is_login();
            if(!$uid) {
                $this->renderFailed('请先登录');
            }
    
            $review = I('id', '', 'intval');
            if(empty($review)) {
                $this->renderFailed('评论id为空');
            }

            $map['uid'] = $uid;
            $map['pay_id'] = $review;

            $Likes = M('course_comment');
            $select=M('review_info');
            if($select->where($map)->delete()) {
                //更新评论点赞数
                $Likes->where('id='.$review)->setDec('review');
                $this->renderSuccess('取消点赞');
            }
            else {
                $map['create_time'] = time();
                $select->add($map);
                $Likes->where('id='.$review)->setInc('review');
                $this->renderSuccess('点赞成功');
            }
        // }
    }
    //机构视频
    public function orgvideo($id,$orgnization_video){
        $org_list=M('orgnization_video');
        $select=$org_list->alias('a')
                        ->join('__MEMBER__ b ON a.uid=b.uid')
                        ->join('__MEMBER_ORG__ o on o.uid=a.uid')
                        ->field('a.id,a.cover_url,a.video_url,a.title,a.create_time,a.likes,a.share,a.comments,b.uid,o.nickname,b.avatar')
                        ->where($orgnization_video.'=o.org_id and a.id='.$id)
                        ->select();
        return $select;
    }
    /**
     *@author lisk 2017年6月23日17:46:56
     *@param 评论删除
     *@return JSON
     */
    public function  DeleteComment(){
        $uid =  parent::check_login();
        $id = data_isset($this->post_origin_data['id'],'intval');
        $type = data_isset($this->post_origin_data['type'],'intval');
        if(empty($id)){
                $result = array('status'=>1069,'info'=>'ID不为空');
                parent::put_post($result); 
        }
        $map['is_delete'] = 1;
        if($type == 3 || $type == 1){
            $type = 4;
        }
        if($type == 10){
            $type = 4;
        }
        if($type == 4){
            if(M('review')->where('id='.$id)->save($map)){
                $result = array('status'=>1,'info'=>'删除成功');
            }else{
                $result = array('status'=>0,'info'=>'删除失败');
            }
            parent::put_post($result); 
        }else{
            if(M('comment')->where('id='.$id)->save($map)){
                $result = array('status'=>1,'info'=>'删除成功');
            }else{
                $result = array('status'=>0,'info'=>'删除失败');
            }
            parent::put_post($result); 
        }

    }
    /**
     *@author lisk 2017年6月26日11:20:52
     *@param 机构考题列表
     *@return JSON
     */
    public function ExaminationQuestions(){
        $uid =  parent::check_login();
        $page = data_isset($this->post_origin_data['page'],'intval',1);
        $rows = data_isset($this->post_origin_data['rows'],'intval',20);
        $org_id = data_isset($this->post_origin_data['org_id'],'intval');
        $template = data_isset($this->post_origin_data['template'],'intval',0);
        if($template == 0){
            $list =  M('examination')->field('id,title,description,abort_time,org_id,content_json,uid,template as template_id')
                    ->where('is_delete=0 and org_id='.$org_id)
                    ->page($page, $rows)->select();
            foreach ($list as $key => $value) {
                $list[$key]['participation'] = M('orgnization_video')->where('is_delete=0 and examination_id = '.$value['id'])->count();
                $list[$key]['abort_time'] = date('Y-m-d',$value['abort_time']);
            }
        }else{
            $list = array(
                array(
                    "template_id"=>1,
                    "title"=>"纯文字模板",
                    "description"=>"本版仅提供文字内容输入，填写标签及活动内容即可发布",
                    "abort_time"=>"永久",
                    "participation"=>"0"),
                array(
                    "template_id"=>2,
                    "title"=>"图文模板",
                    "description"=>"本版仅提供活动宣传图片展示，上传活动宣传图，填写标签及活动内容即可发布",
                    "pic"=>array('http://live-storage.bipai.tv/template.png'),
                    "abort_time"=>"永久",
                    "participation"=>"0"),
                );
        }
        $list = parent::format_data($list);
        $result = array('status'=>1,'data'=>$list,'info'=>'列表');
        parent::put_post($result);
    }
    public function OperateExamination(){
        log_new($this->post_origin_data, 'ddd');;
        $uid =  parent::check_login();
        $org_id = data_isset($this->post_origin_data['org_id'],'intval');
        $template_id = data_isset($this->post_origin_data['template_id'],'intval',0);
        $title = data_isset($this->post_origin_data['title'],'trim');
        $description = data_isset($this->post_origin_data['description'],'trim');
        if(empty($description)) {
            $result = array('status'=>1051,'info'=>'任务说明不能为空');
            parent::put_post($result);
        }
        if($this->is_entrypt == 0){//JSON数据格式不正确
            $content_json = I('content_json','','');
        }else{
            $content_json = $this->post_origin_data['content_json'];
        }
        $abort_time = data_isset($this->post_origin_data['abort_time'],'intval',0);
        $data = array('uid'=>$uid,'org_id'=>$org_id,'template'=>$template_id,'title'=>$title,'description'=>$description,'content_json'=>$content_json,
                'abort_time'=>$abort_time,'create_time'=>time());
        $flag = M('examination')->add($data);
        if($flag){
            $result = array('status'=>1,'info'=>'添加成功');
        }else{
            $result = array('status'=>0,'info'=>'添加失败');
        }
        parent::put_post($result);
    }
}
