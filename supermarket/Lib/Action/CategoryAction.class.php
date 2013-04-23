<?php
/**
 * User: corn-s
 * Date: 13-4-23
 * Time: 下午6:29
 */
//商品分类模块
class CategoryAction extends BaseAction{

    public function index(){
        $this->title = '商品分类列表';
        $category = D('Category');
        $list = $category->where(array('pid'=>0,))->select();
        $list = $category->parseFieldsMap($list);
        $this->list = $list;
        $this->display();
    }

    public function sub_cate($pid){
        $category = D('Category');
        $list = $category->where(array('pid'=>$pid,))->select();
        $list = $category->parseFieldsMap($list);
        $this->list = $list;
        $this->display();
    }


    public function add(){
        $pid = $this->_get('pid');
        if(!empty($pid) && $pid > 0){
            $category = D('Category');
            $child = $category->parseFieldsMap( $category->getById($pid) );
            if($child['pid'] != 0){
                if($child['pid'] >= 1 && $child <=100){
                    $parent = $category->getById($child['pid']);
                }else{
                    $parent = $category->getById($child['pid']);
                    $super = $category->getById($parent['pid']);
                }
            }
            if ($child) {
                $this->child = $category->parseFieldsMap($child);
                $this->parent =$category->parseFieldsMap($parent);
                $this->super = $category->parseFieldsMap($super);
                $this->display();
            } else {
                $this->error('分类不存在！');
            }

        }
        $this->display();
    }

    public function insert(){
        $category = D('Category');
        $vo = $category->create();
        if (false !== $vo) {
            //填充主键
            $pid = $vo['pid'];
            $count = $category->where(array('pid' =>$pid ))->count();
            $count++;
            $id = $pid.'';

            if($count >0 && $count <= 9){
                $id = $id.'0'.$count;
            }else{
                $id = $id.$count;
            }
            $vo['id']= $id;
            $id = $category->add($vo);
            if (false !== $id ) {
                $this->success('保存成功！',U('Category/index'));
            } else {
                $this->error('写入错误'.$category->getLastSql());
            }
        } else {
            // 字段验证错误
            $this->error($category->getError());
        }
    }

    public function edit($id){
        if (empty($id)) {
            $this->error('请指定要修改的分类！');
        }
        $category = D('Category');
        $child = $category->getById($id);

        if($child['pid'] != 0){
            if($child['pid'] >= 1 && $child <=100){
                $parent = $category->getById($child['pid']);
            }else{
                $parent = $category->getById($child['pid']);
                $super = $category->getById($parent['pid']);
            }
        }

        if ($child) {
            $this->child = $category->parseFieldsMap($child);
            $this->parent =$category->parseFieldsMap($parent);
            $this->super = $category->parseFieldsMap($super);
            $this->display();
        } else {
            $this->error('分类不存在！');
        }
    }




    public function del($id){
        if (empty($id)) {
            $this->error('ID错误！');
        }

        $category = D('Category');
        //如果当前分类下有子分类，则不能删除
        if($category->where(array('pid'=>$id))->count()  >  0){
            $this->error('当前分类下还有子分类，不能进行删除！');
        }

        $goods = D('Goods');
        if($goods->where(array('category_id'=>$id))->count()  >  0){
            $this->error('当前分类下还有商品信息，不能进行删除！');
        }

        $result =  $category->delete($id);
        if (false !== $result) {
            $this->success('删除成功！', U('/category'));
        } else {
            $this->error($category->getError());
        }
    }

    public function listAll(){
        $default = $this->_get("d");
        if(empty($default)){
            $default = 1;
        }
        $category = D('Category');
        $this->category_default = $default;
        $this->list = $category->listAll();
        $this->display();
    }


    public function listSub(){
        $category = D('Category');
        $this->category_list = $category->listSub();
        $this->display();
    }
}
