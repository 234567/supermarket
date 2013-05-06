<?php

/**
 * Class BranchAction
 *
 * 后台分店模块
 *
 */
class BranchAction extends BaseAction{

    /**
     * 查看分店的商品库存信息
     */
    public function goodsStock(){
        $branchId = $this->_param("branchId","intval");
        if(empty($branchId)){
            $this->error("非法参数！");
        }

        //哈哈，如果是不是管理员，无论如何也只能看到自己分店的库存情况
        //或者直接给予错误提示
        $currBid = intval($_SESSION["branch_info"]["id"]);
        //如果不是管理员，那么查看的不是自己分店的信息就要报错
        if(session(C("ADMIN_AUTH_KEY"))  !== true && $branchId != $currBid){
            $this->error("请不要跨越权限尝试查看其他分店的库存信息！");
            //$branchId = $currBid;
        }
        $map = array();
        //以下参数为非必须
        $cid = $this->_param("cid","intval");
        if(!empty($cid)){
            $map["category_id"] = $cid;
        }
        $name = $this->_param("name");
        if(!empty($cid)){
            $map["name"] = array("like","%".$name."%");
        }

        $BranchService = D("Branch","Service");
        $result = $BranchService->getGoodsStock($branchId,$map);

        $this->branchInfo = M("Branch")->getById($branchId);
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();
    }

}
