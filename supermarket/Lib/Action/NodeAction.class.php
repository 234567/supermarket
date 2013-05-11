<?php
class NodeAction extends BaseAction
{
    public function _initialize()
    {
        parent::_initialize();
        Vendor('Common.Tree'); //导入通用树型类
    }

    public function index()
    {
        $Node = D('node')->select();
        $array = array();
        // 构建生成树中所需的数据
        foreach ($Node as $key => $r) {
            $r['id'] = $r['id'];
            $r['title'] = $r['title'];
            $r['name'] = $r['name'];

            $r['edit'] = '<a class="btn btn-mini" href="' . U('node/edit') . '?id=' . $r['id'] . '">编辑</a>';
            switch ($r['status']) {
                case -1:
                    $r['status'] = '<span class="label label-inverse">已删除</span>';
                    $r['op'] = '<a class="btn btn-inverse btn-mini" href="' . U('node/recycle') . '?id=' . $r['id'] . '">还原</a>';
                    $r['del'] = '';
                    break;

                case 0:
                    $r['status'] = '<span class="label label-danger">已禁用</span>';
                    $r['op'] = '<a class="btn btn-mini btn-success" href="' . U('node/resume') . '?id=' . $r['id'] . '">恢复</a>';
                    $r['del'] = '<a class="btn btn-mini btn-warning" href="' . U('node/del') . '?id=' . $r['id'] . '">删除</a>';
                    break;

                case 1:
                    $r['status'] = '<span class="label label-success">启用</span>';
                    $r['op'] = '<a class="btn btn-mini btn-danger" href="' . U('node/forbid') . '?id=' . $r['id'] . '">禁用</a>';
                    $r['del'] = '<a class="btn btn-mini btn-warning" href="' . U('node/del') . '?id=' . $r['id'] . '">删除</a>';
                    break;
            }

            switch ($r['level']) {
                case 1:
                    $r['level'] = '<span class="label label-info">项目</span>';
                    break;
                case 2:
                    $r['level'] = '<span class="label label-inverse">模块</span>';
                    break;
                case 3:
                    $r['level'] = '<span class="label">操作</span>';
                    break;
            }
            $array[] = $r;
        }

        $str = "<tr class='tr'>
				    <td align='center'>\$id</td>
				    <td align='center'>\$name</td>
				    <td >\$spacer \$title</td>
				    <td align='center'>\$level</td>
				    <td align='center'>\$status</td>
					<td align='center'>\$op \$edit \$del</td>
				  </tr>";

        $Tree = new Tree();
        $Tree->icon = array('&nbsp;&nbsp;│ ', '&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;└─ ');
        $Tree->nbsp = '&nbsp;&nbsp;';
        $Tree->init($array);
        $html = $Tree->get_tree(0, $str);

        $this->table_body = $html;
        $this->display();
    }

    public function insert()
    {
        $name = $this->getActionName();
        $model = D($name);
        $vo = $model->create();
        if (false === $vo) {
            $this->error($model->getError());
        }
        $list = $model->add();
        if (false === $list) {
            $this->error("新增失败！");
        }

        $this->success("新增成功！", $this->getReturnUrl());
    }

    public function edit()
    {
        $name = $this->getActionName();
        $model = M($name);
        $id = $this->_param($model->getPk());
        if (empty($id)) {
            $this->error("参数错误！");
        }
        $vo = $model->getById($id);
        $this->vo = $vo;
        $this->display();
    }

    public function update()
    {
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $list = $model->save();
        if (false === $list) {
            $this->error("编辑失败！");
        }
        $this->success("编辑成功！", $this->getReturnUrl());
    }


    public function del()
    {
        $name = $this->getActionName();
        $model = M($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $this->_param($pk);
            if (!isset($id)) {
                $this->error("非法操作！");
            }
            //根据传过来的ID参数，有可能是批量删除，也就是删除多个ID，默认以,分割
            $condition = array($pk => array("in", explode(",", $id)));
            $list = $model->where($condition)->setField('status', -1);
            if (false === $list) {
                $this->error("删除失败！");
            }
            $this->success("删除成功！", $this->getReturnUrl());
        }
    }

    public function forbid()
    {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $this->_param($pk);
        if (empty($id)) {
            $this->error("非法参数！");
        }
        $condition = array($pk => array("in", $id));
        $list = $model->forbid($condition);
        if (false === $list) {
            $this->error("状态禁用失败！");
        }

        $this->success("状态禁用成功！", $this->getReturnUrl());
    }

    public function recycle()
    {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $this->_get($pk);
        if (empty($id)) {
            $this->error("非法参数！");
        }
        $condition = array($pk => array("in", $id));
        $list = $model->recycle($condition);
        if (false === $list) {
            $this->error("状态还原失败！");
        }
        $this->success("状态还原成功！", $this->getReturnUrl());
    }

    function resume()
    {
        //恢复指定记录
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $this->_get($pk);
        if (empty($id)) {
            $this->error("非法参数！");
        }
        $condition = array($pk => array('in', $id));
        if (false == $model->resume($condition)) {
            $this->error('状态恢复失败！');
        }
        $this->success('状态恢复成功！', $this->getReturnUrl());
    }

}