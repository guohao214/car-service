<?php 
namespace Home\Model;
use Think\Model;

/**
 * 分类模型
 */
class CallModel extends Model{
    protected $_validate = array(
        array('name', 'require', '姓名不能空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        // array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('mobile', 'require', '手机号不能空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('mobile', '/0?(13|14|15|18)[0-9]{9}/', '手机号为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
        array('number', 'require', '身份证不能空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('number', '\d{17}[\d|x]|\d{15}', '请输入正确的身份证号', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('mobile', 'require', '手机号不能空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );
}