<?php

/**
 * Class SalesRecordAction
 *
 *
 */
class SalesRecordAction extends BaseAction{

    public function showchart(){
        $day = $this->_param("recent","intval",7);
        $data = $service = D("SalesRecord","Service")->countRecent($day);
        $this->data = json_encode($data);
        $this->display();
    }

}