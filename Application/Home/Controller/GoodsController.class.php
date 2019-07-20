<?php
/**
 * @todo 上线时间短来不及，重复函数，需要统一整合调用
 * Class GroupController
 * @name 群组管理/班级管理
 */

namespace Home\Controller;
use User\Api\UserApi;

use Exception;
class GoodsController extends HomeController {
    //添加
    public function add(){
        header("Access-Control-Allow-Origin:*");
        $model = M('goods');
        $model->startTrans();

        try{
            $data = $this->post_origin_data;
            file_put_contents('log.txt', json_encode($data), FILE_APPEND);
            $goods_data = $data['basicInfo'];
            //$goods_image_data = [];
            

            //file_put_contents('thumb.txt', json_encode($value['arr'][0]),FILE_APPEND);
            // foreach ($data['photoList'] as $key => $value) {
            //     // if($value['name'] == 'thumb'){
            //     //     $goods_data['thumb'] = $value['arr'][0];
            //     //     print_r($goods_data['thumb']);
            //     //     exit;           
            //     // }else if($value['name'] == 'group_thumb'){
            //     //     $goods_data['group_thumb'] = $value['arr'][0];                    
            //     // }else{
            //     //     $type = $value['name'];
            //     //     foreach ($value['arr'] as $k => $v) {
            //     //         $obj = ['type'=>$type,'img' => $v,'goods_id'=>0];
            //     //         array_push($goods_image_data, $obj)    ;             
            //     //     } 
            //     // }

            //     $type = $value['name'];
            //     foreach ($value['arr'] as $k => $v) {
            //         $obj = ['type'=>$type,'img' => $v,'goods_id'=>0];
            //         array_push($goods_image_data, $obj)    ;             
            //     } 
            // }

            
            $goods_id = $model->add($goods_data);            
            if($goods_id){
                $model->commit();
                echo json_encode(array('status'=>1,'msg'=>'添加成功'));

                //$goods_group_data = $data['groupList'];
                // foreach ($goods_image_data as $k => $v) {
                //     $goods_image_data[$k]['goods_id'] = $goods_id  ;         
                // } 
                // $goods_group_id = M('group')->addAll($goods_group_data);
                // foreach ($goods_group_data as $k => $v) {
                //     $goods_group_data[$k]['goods_id'] = $goods_id  ;         
                // } 

                // $goods_image_id = M('goods_image')->addAll($goods_image_data);
                

                // if($goods_image_id && $goods_group_id){
                //     $model->commit();
                //     echo json_encode(array('status'=>1,'msg'=>'添加成功'));
                // }else{
                //     $model->rollback();
                //     echo json_encode(array('status'=>0,'msg'=>'添加失败'));
                // }
                
            }else{
                $model->rollback();
                echo json_encode(array('status'=>0, 'msg'=>'添加失败'));
            }
        }catch(Exception $err){
            $model->rollback();
            echo json_encode(array('status'=>101, 'msg'=>$err.message));
        }
    }
    //编辑
    public function edit(){
        header("Access-Control-Allow-Origin:*");
        try{
            $data = $this->post_origin_data;
            
            $goods_data = $data['basicInfo'];
            $goods_image_data = [];
            $goods_group_data = $data['groupList'];
            $goods_id = $data['goods_id'];
            //file_put_contents('thumb.txt', json_encode($value['arr'][0]),FILE_APPEND);
            foreach ($data['photoList'] as $key => $value) {
                if($value['name'] == 'thumb'){
                    $goods_data['thumb'] = $value['arr'][0];                    
                }else if($value['name'] == 'group_thumb'){
                    $goods_data['group_thumb'] = $value['arr'][0];                    
                }else{
                    $type = $value['name'];
                    foreach ($value['arr'] as $k => $v) {
                        $obj = ['type'=>$type,'img' => $v,'goods_id'=>$goods_id];
                        array_push($goods_image_data, $obj)    ;             
                    } 
                }                   
            }

            foreach ($goods_group_data as $key => $value) {
                $goods_group_data[$key]['goods_id'] = $goods_id;
            }

            $model = M('goods');
            $model->startTrans();
            $where = array('goods_id'=>$goods_id);
            $model->where($where)->save($goods_data);
            
            $del = array('disabled'=>2);
            M('goods_image')->where($where)->save($del);
            M('group')->where($where)->save($del);
            $goods_image_flag = true;
            $goods_group_flag = true;
            if($goods_image_data)
                $goods_image_flag = M('goods_image')->addAll($goods_image_data);
            if($goods_group_data)
                $goods_group_flag = M('group')->addAll($goods_group_data);
            if($goods_image_flag && $goods_group_flag){
                $model->commit();
                echo json_encode(array('status'=>1,'msg'=>'修改成功'));
            }else{
                $model->rollback();
                echo json_encode(array('status'=>0,'msg'=>'修改失败，请重试'));
            }
            
        }catch(Exception $err){
            echo json_encode(array('status'=>101,'msg'=>$err.message));
        }
    }
    //删除
    public function del(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $goods_id = $data['goods_id'];
        $where['goods_id'] = $goods_id;
        $map['disabled'] = 2;
        $goods = M('goods')->where($where)->save($map);
        if($goods){
            echo json_encode(array('status'=>1,'msg'=>'删除成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'删除失败'));
        }
        exit;
    }
    //获取列表
    public function appGoodsList(){
        header("Access-Control-Allow-Origin:*");
        $year = $this->post_origin_data['year'];
        $start = $this->post_origin_data['start'];
        $end = $this->post_origin_data['end'];
        $grade = $this->post_origin_data['grade'];
        $searchType = $this->post_origin_data['searchType'];

        // $where['disabled'] = 1;         
        // if($searchType == 'search_by_grade'){
        //     $where['grade'] = $grade;
        // } else if($searchType == 'search_by_price') {
        //     $where['sell_price'] = array('between',array($start*10000, $end*10000));
        // }
        $where = ' disabled = 1 ';
        if($searchType == 'search_by_grade' && $year){
            $where .= ' and year like "'.$year.'-%"';
        } else if($searchType == 'search_by_price') {
            $where .= ' and sell_price between '.$start.' and '.$end;
        }
        $sql = "
            SELECT
               g.year, g.goods_id, g.goods_name, g.brand_id, b.cn_name as brand_name, g.thumb, g.market_price, g.sell_price
            FROM
               goods g
            LEFT join
               brand b
               ON g.brand_id = b.big_brand_id
            WHERE ".$where." 
             ORDER BY g.goods_id desc
        ";
        
        $goods = M('goods')->query($sql);

        // $goods = M('goods')->field('year,goods_id,goods_name,brand_id,brand_name,thumb,market_price,sell_price')->where($where)->order('goods_id desc')->select();

        if($goods == null){
            $goods = [];
        }

        foreach ($goods as $key=>$value) {
            $goods[$key]['year'] = date('Y',  strtotime($value['year']));
        }
        echo json_encode($goods);
        exit;
    }
    //获取详情(小程序)
    public function appGoodsDetail(){
        $goods_id = $this->post_origin_data['goods_id'];
        $where = [
            'goods_id'=>$goods_id,
            'disabled'=>1
        ];
        
        $goods = M('goods')->field('goods_id,goods_name,upload_list_1,upload_list_3,upload_list_2,upload_list_4,goods_title,brand_id,brand_name,thumb,market_price,sell_price')->where($where)->find();

        $brand_id = $goods['brand_id'];
        $brand = M('brand')->where(['big_brand_id'=>$brand_id])->find();
        $goods['brand_name'] = $brand['cn_name'];

        //$group = M('group')->where($where)->select();
        $goods['upload_list_1'] = json_decode($goods['upload_list_1']);
        $goods['upload_list_2'] = json_decode($goods['upload_list_2']);
        $goods['upload_list_3'] = json_decode($goods['upload_list_3']);
        $goods['upload_list_4'] = json_decode($goods['upload_list_4']);

        if($goods){
            $goods['num'] = count($goods['upload_list_1']) + count($goods['upload_list_2']) + count($goods['upload_list_3']) + count($goods['upload_list_4']);
        }
        echo json_encode($goods);
        exit;
    }
    //获取详情(小程序)
    public function appCarDetail(){
        $goods_id = $this->post_origin_data['goods_id'];
        $where = [
            'goods_id'=>$goods_id,
            'disabled'=>1
        ];
        
        $goods = M('goods')->where($where)->find();   
        $_goods = [];
        $props = [
            'goods_linventory'=>'商品库存',
            'car_type'=>'车型',
            'car_type_name'=>'车型详情',
            'msrp'=>'厂家指导价',
            'firm' => '厂家',
            'grade'=>'级别',
            'energy' =>'能源',
            'year'=>'上市时间',
            'engine'=>'发动机',
            'engine_no'=>'发动机型号',
            'gear_box'=>'变速箱',
            'size'=>'长*宽*高',
            'structure'=>'车身结构',
            'spead'=>'最高车速',
            'ref_accelerate'=>'官方0-100KM加速所花时间',
            'real_accelerate'=> '实际0-100KM加速所花时间',
            'oil_total'=>'工程部综合油耗',
            'oil_real'=>'实测油耗',
            'qa'=> '质保',
            'length' => '长身长度',
            'wide'=> '长身宽度',
            'high'=>'长身高度',
            'wheelbase' =>'轴距',
            'before_wheel'=>'前轮距',
            'behind_wheel'=>'后办距',
            'min_earth' =>'最小离地间隙',
            'door_num' => '车门数',
            'seat_num'=> '座位数',
            'oilbox_bulk'=> '油箱体积',
            'box_bulk' => '箱体积',
            'weight'=> '重量',
            'displacement'=>'排量',
            'in_type'=>'进气形式',
            'arrangement_qi'=>'气肛排列形式',
            'qi_num'=>'气缸数',
            'qi_door_num'=>'气缸门数',
            'reduce_ratio'=>'压缩比',
            'qi_structure'=>'配气结构',
            'cylinder' =>'缸经',
            'distance'=>'行程',
            'max_hp'=>'最大马力',
            'max_power'=>'最大功率',
            'max_power_speed' =>'最大功率转速',
            'torque'=>'最大扭矩',
            'max_torque_speed'=>'最大扭矩转速',
            'engine_technology'=>'发动机特有技术',
            'fuel_form'=>'燃料形式',
            'fuel_no'=>'燃油标号',
            'supply_oil_type'=> '供油方式',
            'cylinder_head' =>'缸盖材料',
            'rohs'=>'环保标准',
            'gear_num' =>'档位个数',
            'gear_box_type'=>'变速箱类型',
            'shorter_form' =>'简称',
            'driving_method' =>'驱动方式',
            'four_drive_form'=>'四驱形式',
            'central_ifferential_structure' =>'中央差速器结构',
            'front_suspension_type'=> '前悬架类型',
            'rear_suspension_type' =>'后悬架类型',
            'help_type'=> '助力类型',
            'front_brake'=>'前制动器类型',
            'input_rear_brake' =>'输入后制动器类型',
            'parking_brake_type' => '驻车制动器类型',
            'front_tire_specification' =>'前轮胎规格',
            'rear_tire_specification' =>'后轮胎规格',
            'spare_tire_specification' =>'备胎规格'
        ];
        foreach ($goods as $key=>$value) {
            if($props[$key]){
                $obj['tag'] = $key;
                $obj['content'] = $value == null ? '' : $value;
                $obj['title'] = $props[$key];
                array_push($_goods, $obj);
            }           
        }
        echo json_encode($_goods);
        exit;
    }
    //获取详情(小程序)
    public function appGoodsImage(){
        $goods_id = $this->post_origin_data['goods_id'];
        //$type = $this->post_origin_data['type'] ? $this->post_origin_data['type'] : 'upload_list_1';
        $where = [
            'goods_id'=>$goods_id,
            'disabled'=>1
        ];
        $goods_image = M('goods')->field('upload_list_1,upload_list_3,upload_list_2,upload_list_4')->where($where)->find();
        foreach ($goods_image as $key => $value) {
            $goods_image[$key] = json_decode($value);
        }
        
        echo json_encode($goods_image);
        exit;
    }

    public function goods(){
        $goods_id = $this->post_origin_data['goods_id'];
        $where = [
            'goods_id'=>$goods_id,
            'disabled'=>1
        ];
        file_put_contents('thumb.txt', json_encode($where),FILE_APPEND);
        $goods = M('goods')->where($where)->find();
        $group = M('group')->where($where)->select();
        $goods_image = M('goods_image')->where($where)->select();
        $photoList = [];
        $sendData = [];
        if($goods){
            $sendData['basicInfo'] = $goods;            
            array_push($photoList, ['arr'=>[$goods['thumb']],'name'=>'thumb'],['arr'=>[$goods['thumb']],'name'=>'group_thumb']);
            if($goods_image){
                $name = ['upload_list_1','upload_list_2','upload_list_3','upload_list_4'];
                $upload_list_1 = [];
                $upload_list_2 = [];
                $upload_list_3 = [];
                $upload_list_4 = [];
                foreach ($goods_image as $key => $value) {
                    if($value['type'] == 'upload_list_1'){
                        array_push($upload_list_1,$value['img']);
                    }
                    if($value['type'] == 'upload_list_2'){
                        array_push($upload_list_2,$value['img']);
                    }
                    if($value['type'] == 'upload_list_3'){
                        array_push($upload_list_3,$value['img']);
                    }
                    if($value['type'] == 'upload_list_4'){
                        array_push($upload_list_4,$value['img']);
                    }
                    file_put_contents('thumb.txt', json_encode($value),FILE_APPEND);
                }

                array_push($photoList,
                    ['arr'=>$upload_list_1,'name'=>'upload_list_1'],
                    ['arr'=>$upload_list_2,'name'=>'upload_list_2'],
                    ['arr'=>$upload_list_3,'name'=>'upload_list_3'],
                    ['arr'=>$upload_list_4,'name'=>'upload_list_4']);
            }
            $sendData['photoList'] = $photoList;
            $sendData['groupList'] = $group;   
            $sendData['num'] =  count($goods_image) ;  
        }
        echo json_encode($sendData);
        exit;
    }

    // 后台管理页面
    public function webAddGoods(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $basicInfo = $data['basicInfo'];
        $model = M('goods');
        $model->startTrans();
        $res = [];

        try{
            $data = $this->post_origin_data;
            file_put_contents('log.txt', json_encode($data), FILE_APPEND);
            $goods_data = $data['basicInfo'];

            foreach ($goods_data as $key => $value) {
                if($key == 'upload_list_1' || $key == 'upload_list_2' || $key == 'upload_list_3' ||$key == 'upload_list_4'){
                    $goods_data[$key] = str_replace("&quot;", "\"", $value);
                }
            }
            
            $goods_id = $model->add($goods_data);
            if($goods_id){
                $groupDataList = $data["groupDataList"];
                foreach ($groupDataList as $k => $v) {
                    $groupDataList[$k]['goods_id'] = $goods_id;
                }
                $goods_group_id = M('group')->addAll($groupDataList);

                $model->commit();
                $res = [
                    "code" => 0,
                    'msg'=>'添加成功',
                    "data" => $data
                ];
            }else{
                $model->rollback();
                $res = [
                    "code" => 1,
                    'msg'=>'添加失败',
                    "data" => null
                ];
            }
        }catch(Exception $err){
            $model->rollback();
            $res = [
                "code" => 1,
                'msg'=>'添加失败 错误详情:' . $err.message,
                "data" => null
            ];
        }
        echo json_encode($res);
        exit;
    }

    public function webEditGoods(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $goods_id = $data['goods_id'];
        $basicInfo = $data['basicInfo'];
        $model = M('goods');
        $model->startTrans();
        $res = [];

        try{
            $data = $this->post_origin_data;
            file_put_contents('log.txt', json_encode($data), FILE_APPEND);
            $goods_data = $data['basicInfo'];

            foreach ($goods_data as $key => $value) {
                if($key == 'upload_list_1' || $key == 'upload_list_2' || $key == 'upload_list_3' ||$key == 'upload_list_4'){
                    $goods_data[$key] = str_replace("&quot;", "\"", $value);
                }
            }
            
            $model->where(["goods_id"=>$goods_id])->save($goods_data);
            file_put_contents('log.txt', json_encode($goods_id), FILE_APPEND);
           // if($goods_id){
                M('group')->where(["goods_id"=>$goods_id])->delete();
                $groupDataList = $data["groupDataList"];
                foreach ($groupDataList as $k => $v) {
                    $groupDataList[$k]['goods_id'] = $goods_id;
                }
                $goods_group_id = M('group')->addAll($groupDataList);

                $model->commit();
                $res = [
                    "code" => 0,
                    'msg'=>'修改成功',
                    "data" => $data
                ];
            // }else{
            //     $model->rollback();
            //     $res = [
            //         "code" => 1,
            //         'msg'=>'修改失败',
            //         "data" => null
            //     ];
            // }
        }catch(Exception $err){
            $model->rollback();
            $res = [
                "code" => 1,
                'msg'=>'修改失败 错误详情:' . $err.message,
                "data" => null
            ];
        }
        echo json_encode($res);
        exit;
    }

    // 后台管理页面
    public function webGoodsList(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $page = $data['page'];
        $length = $data['length'];

        $where = ['disabled'=>1];
        $total = M('goods')->where($where)->order("goods_id asc")->count();
        $list = M('goods')->where($where)->order("goods_id asc")->page($page+1, $length)->select();
        $res = [
            "code" => 0,
            "data" => [
                "list" => $list,
                "total" => $total,
            ]
        ];
        echo json_encode($res);
        exit;
    }


     // 后台管理页面
    public function webGoodsDetail(){
        header("Access-Control-Allow-Origin:*");
        $data = $this->post_origin_data;
        $goods_id = $data['goods_id'];
        $where = ['disabled'=>1, 'goods_id'=>$goods_id];
        $basicInfo = M('goods')->where($where)->find();
        $groupDataList = M('group')->where($where)->select();
        if(empty($groupDataList)){
            $groupDataList = [];
        }
        $res = [
            "code" => 0,
            "data" => [
                "basicInfo" => $basicInfo,
                "groupDataList" => $groupDataList,
            ]
        ];
        echo json_encode($res);
        exit;
    }

}	
