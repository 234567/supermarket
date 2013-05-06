<?php
/**
 * Class PromotionsAction
 *
 * 商品促销模块
 */
class PromotionsAction extends BaseAction
{


    /**
     * 发布折扣
     */
    public function release()
    {
        //实例化Service
        $service = D($this->getActionName(), "Service");
        $result = $service->release();
        if (false == $result) {
            $this->error("该商品已存在折扣信息，不能重复添加折扣信息！", $this->getReturnUrl());
        }
        $this->branch = $result["branch"];
        $this->goods = $result["goods"];
        trace($result["goods"]);
        $this->display();
    }


    /**
     * 修改折扣信息
     */
    public function edit()
    {
        $id = $this->_param("id", "intval");
        $branchId = $_SESSION["staff_info"]["branch_id"];
        $service = D($this->getActionName(), "Service");

        $result = $service->edit($branchId, $id);

        if (false === $result["goods"]) {
            $this->error("无权限删除其他分公司折扣信息!", U("promotions/index"));
        }
        $this->promotions = $result["promotions"];
        $this->goods = $result["goods"];
        $this->display();
    }

    public function del()
    {
        $id = $this->_param("id", "intval");
        $branchId = $_SESSION["staff_info"]["branch_id"];
        $service = D("Promotions", "Service");
        try {
            $service->del($branchId, $id);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success("删除成功！");
    }

    /*
   * 根据条码，判断该商品在现阶段是否存在促销
   * 1.存在，提示，不允许再次添加促销信息
   * 2.不存在，提示：可以添加促销信息
   */
    public function isPromotions()
    {
        $barcode = $this->_param("barcode");
        if (empty($barcode)) {
            $this->error("参数错误！");
        }
        try {
            $service = D("Promotions", "Service");
            $result = $service->isPromotions($barcode);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->ajaxReturn($result, "该商品在此期间未添加促销信息，可添加促销", 1);
    }

    public function checkExist()
    {
        $barcode = $this->_param("value");
        $map = array();
        $now = time();
        $map["branch_id"] = $_SESSION["staff_info"]["branch_id"];
        $map["time_start"] = array("elt", $now);
        $map["time_end"] = array("egt", $now);
        $promotions = M("Promotions")->field("promotions.*,goods.name as goods_name")->
            join("goods ON goods.id = promotions.goods_id and goods.barcode = " . $barcode)->where($map)->find();

        if (!empty($promotions)) {
            if (!isset($promotions["goods_name"])) {
                echo json_encode(
                    array(
                        "value" => $barcode,
                        "valid" => false,
                        "message" => "该条形码没有对应的商品信息！"
                    ));
            }else{
                echo json_encode(
                    array(
                        "value" => $barcode,
                        "valid" => false,
                        "message" => $promotions['goods_name'] . "已经存在促销信息了！请不要重复添加<a href=\"" . U('Promotions/edit?id=' . $promotions["id"]) . "\">点击修改</a>"
                    ));
            }
        } else {
            echo json_encode(
                array(
                    "value" => $barcode,
                    "valid" => true,
                    "message" => "OK，可以促销"
                ));
        }
    }

    //查询历史促销信息
    public function history(){
        $service = D("Promotions","Service");
        $map = array();

        try{
            $result = $service->history($map);
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
        trace($result);
        $this->list = $result["list"];
        $this->page = $result["page"];
        $this->display();
    }
}