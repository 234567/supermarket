<?php

// 用户模型
class StaffModel extends CommonModel {
    public $_validate	=	array(
        array('account','/^[a-z]\w{3,}$/i','帐号格式错误'),
        array('account','','帐号已经存在',self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT),
        array('password','require','密码必须'),
    );

    public $_auto		=	array(
        array('password','passCrypt',self::MODEL_BOTH,'callback'),
        array('create_time','time',self::MODEL_INSERT,'function'),
        array('update_time','time',self::MODEL_UPDATE,'function'),
    );

    protected function passCrypt() {
        if(isset($_POST['password'])) {
            return hash('md5',$_POST['password'] );
        }else{
            return false;
        }
    }
}