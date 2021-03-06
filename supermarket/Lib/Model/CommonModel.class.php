<?php
/**
 * Class CommonModel
 *
 * 公共模型类，参考了TP官方的示例代码以及其他开源项目
 */
class CommonModel extends Model
{

    // 获取当前用户的ID
    public function getUserId()
    {
        return isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : 0;
    }

    /**
    +----------------------------------------------------------
     * 根据条件禁用表数据
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param array $options 条件
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */
    public function forbid($options, $field = 'status')
    {
        if (false === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return true;
        }
    }

    /**
    +----------------------------------------------------------
     * 根据条件批准表数据
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param array $options 条件
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */

    public function checkPass($options, $field = 'status')
    {
        if (false === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return true;
        }
    }


    /**
    +----------------------------------------------------------
     * 根据条件恢复表数据
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param array $options 条件
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */
    public function resume($options, $field = 'status')
    {
        if (false === $this->where($options)->setField($field, 1)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return true;
        }
    }

    /**
    +----------------------------------------------------------
     * 根据条件恢复表数据
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param array $options 条件
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */
    public function recycle($options, $field = 'status')
    {
        if (false === $this->where($options)->setField($field, 0)) {
            $this->error = L('_OPERATION_WRONG_');
            return false;
        } else {
            return true;
        }
    }
}

?>