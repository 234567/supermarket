<?php
// 角色模型
class RoleModel extends CommonModel {
    public $_validate = array(
        array('name','require','名称必须'),
    );

    public $_auto		=	array(
        array('create_time','time',self::MODEL_INSERT,'function'),
        array('update_time','time',self::MODEL_UPDATE,'function'),
    );
}