<?php

// 用户模型
class StaffModel extends CommonModel
{

    protected $fields = array(
        "id", //员工编号
        "branch_id", //分店编号
        "account", //帐号信息
        "password", //密码
        "name", //员工姓名
        "sex", //员工性别
        "birthday", //出生日期
        "identify_num", //身份证号
        "mobile", //手机号码
        "photo", //员工照片地址
        "remark", //备注信息
        "status", //帐号状态 ，1表示正常，0表示禁用，-1表示删除
        "last_login_time", //最后登陆的时间
        "last_login_ip", //最后登陆的IP地址
        "create_time", //创建时间
        "update_time", //更新时间
        "_pk" => "id",
        "_autoinc" => true,
    );


    public $_validate = array(
        array("account", "/^[a-z]\w{3,}$/i", "帐号格式错误"),
        array("account", "", "帐号已经存在", Model::EXISTS_VALIDATE, "unique", Model::MODEL_INSERT),
        array("password", "require", "密码必须"),
    );

    public $_auto = array(
        array("password", "passCrypt", Model::MODEL_BOTH, "callback"),
        array("create_time", "time", Model::MODEL_INSERT, "function"),
        array("update_time", "time", Model::MODEL_UPDATE, "function"),
        array("birthday","strtotime",Model::MODEL_BOTH,"function" ),
    );

    //可以插入的字段
    protected $insertFields = array(
        //员工信息
        "branch_id","name","sex","birthday","identify_num","mobile","photo","remark",
        //登陆信息
        "account","password");

    protected $updateFields = array(
        //修改时，不能进行分店的转移，性别也暂时不能修改
        "name","sex","birthday","identify_num","mobile","photo","remark",
        //帐号也不能进行修改
        "password",
    );

    //密码使用MD5加密
    protected function passCrypt()
    {
        if (isset($_POST["password"])) {
            return hash("md5", $_POST["password"]);
        } else {
            return false;
        }
    }
}