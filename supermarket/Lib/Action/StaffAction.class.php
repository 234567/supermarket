<?php
/**
 * Class StaffAction
 *
 * 员工管理模块
 */
class StaffAction extends BaseAction
{

    /**
     *  员工列表
     */
    public function index()
    {
        try {
            if ($_SESSION[C("ADMIN_AUTH_KEY")] !== true) {
                //如果不是管理员
                //取出员工所属的分店信息
                $branchId = $_SESSION["staff_info"]["branch_id"];
            }
            $service = D("Staff", "Service");
            $result = $service->getList(array(), $branchId);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();

    }

    /**
     * 修改员工信息（管理员修改）
     */
    public function edit()
    {
        $id = $this->_param("id");
        if (empty($id)) {
            $this->error("参数错误！");
        }
        $vo = M("Staff")->getById($id);
        $result = M("RoleUser")->getByUserId($vo["id"]);
        if (empty($result)) {
            $this->error("员工信息异常！");
        }
        $vo["role_id"] = $result["role_id"];

        $this->vo = $vo;
        $this->display();
    }


    /**
     * 检查帐号是否可用
     */
    public function checkAccount()
    {
        $name = $this->_param("account");

        if (!preg_match('/^[a-z]\w{4,}$/i', $name)) {
            $this->error('帐号必须是字母，且5位以上！');
        }
        // 检测用户名是否冲突

        $result = M("Staff")->getByAccount($name);
        if ($result) {
            $this->error('该用户名已经存在！');
        } else {
            $this->success('该用户名可以使用！');
        }
    }


    /**
     * 重置密码（管理员重置指定帐号的密码
     */
    public function resetPwd()
    {
        $id = $this->_param('id', "intval");
        $password = $this->_param('password');
        if ('' === trim($password)) {
            $this->error('密码不能为空！');
        }
        $Staff = M('Staff');
        $Staff->password = md5($password);
        $Staff->id = $id;
        $result = $Staff->save();
        if (false !== $result) {
            $this->success("密码修改成功，新密码为：$password");
        } else {
            $this->error('重置密码失败！');
        }
    }
}