<?php
/**
 * Class CategoryAction
 *
 *
 * 商品分类模块
 */
class CategoryAction extends BaseAction{

    /**
     * 根据父分类编号获取子分类列表
     *
     * @param $pid     父分类编号
     */
    public function sub_cate($pid){
        $category = D('Category');
        $list = $category->where(array('pid'=>$pid,))->select();
        $list = $category->parseFieldsMap($list);
        $this->list = $list;
        $this->display();
    }

    /**
     * 添加分类信息
     */
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

    /**
     * 编辑分类信息
     */
    public function edit(){
        $id = $this->_param("id","intval");
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

}
