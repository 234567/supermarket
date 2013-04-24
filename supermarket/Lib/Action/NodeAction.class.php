<?php
class NodeAction extends BaseAction {
    public function _initialize() {
        parent::_initialize();
        Vendor('Common.Tree');	//导入通用树型类
    }

    public function index(){
//        $this->list = genTree();
        $Node = D('node')->select();
        $array = array();
        // 构建生成树中所需的数据
        foreach($Node as $key => $r) {
            $r['id']      = $r['id'];
            $r['title']   = $r['title'];
            $r['name']    = $r['name'];

            $r['edit'] = '<a class="btn btn-mini" href="'.U('node/edit').'?id='.$r['id'].'">编辑</a>';
            switch($r['status']){
                case -1:
                    $r['status'] = '<span class="label label-inverse">已删除</span>';
                    $r['op'] = '<a class="btn btn-inverse btn-mini" href="'.U('node/recycle').'?id='.$r['id'].'">还原</a>';
                    $r['del'] = '';
                    break;

                case 0:
                    $r['status'] = '<span class="label label-danger">已禁用</span>';
                    $r['op'] = '<a class="btn btn-mini btn-success" href="'.U('node/resume').'?id='.$r['id'].'">恢复</a>';
                    $r['del'] = '<a class="btn btn-mini btn-warning" href="'.U('node/del').'?id='.$r['id'].'">删除</a>';
                    break;

                case 1:
                    $r['status'] = '<span class="label label-success">启用</span>';
                    $r['op'] = '<a class="btn btn-mini btn-danger" href="'.U('node/forbid').'?id='.$r['id'].'">禁用</a>';
                    $r['del'] = '<a class="btn btn-mini btn-warning" href="'.U('node/del').'?id='.$r['id'].'">删除</a>';
                    break;
            }

            switch ($r['level']) {
                case 1:
                    $r['level'] = '<span class="label label-info">项目</span>';break;
                case 2:
                    $r['level'] = '<span class="label label-inverse">模块</span>';break;
                case 3:
                    $r['level'] = '<span class="label">操作</span>';break;
            }
            $array[]      = $r;
        }

        $str  = "<tr class='tr'>
				    <td align='center'>\$id</td>
				    <td align='center'>\$name</td>
				    <td >\$spacer \$title</td>
				    <td align='center'>\$level</td>
				    <td align='center'>\$status</td>
					<td align='center'>\$op \$edit \$del</td>
				  </tr>";

        $Tree = new Tree();
        $Tree->icon = array('&nbsp;&nbsp;│ ','&nbsp;&nbsp;├─ ','&nbsp;&nbsp;└─ ');
        $Tree->nbsp = '&nbsp;&nbsp;';
        $Tree->init($array);
        $html = $Tree->get_tree(0, $str);

        $this->table_body = $html;
        $this->display();
    }

   public function add(){
       $allNode =  M('node')->select();
       $array = array();

       foreach($allNode as $k => $r) {
           $r['id']         = $r['id'];
           $r['title']      = $r['title'];
           $r['name']       = $r['name'];
           $r['disabled']   = $r['level']==3 ? 'disabled' : '';
           $array[$r['id']] = $r;
       }
       $str  = "<option value='\$id' \$selected \$disabled >\$spacer \$title</option>";
       $Tree = new Tree();
       $Tree->init($array);
       $this->parentNode = $Tree->get_tree(0, $str);
       $this->display();
   }

}