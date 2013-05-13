<?php
/**
 *
 * 自定义的标签库
 *
 *
 */

class TagLibFront extends TagLib
{
    protected $tags = array(
        'list' => array('attr' => 'table,where,order,limit,relation,field,result,page,purl,purlvars', 'close' => 1),
        'category_select' => array('attr' => 'id,name,selected', 'close' => 0),
        'select' => array("attr" => "id,name,model,selected,disabled,style,other,appendoption", "close" => 0),
    );

    //获取列表数据 可关联(字段过滤无效) 可分页
    public function _list($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'list');
        $result = !empty($tag['result']) ? $tag['result'] : 'data';
        if (!empty($tag['table'])) {
            $model = 'D("' . $tag['table'] . '")';
        } else {
            return '';
        }
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        //拼接SQL查询语句
        $parseStr = '<?php ';
        if ($tag['where'] != null && $tag['where'] == '$where') {
            $parseStr .= $tag['where'] != null ? '$map=' . $tag["where"] . ';' : '';
        } else {
            $parseStr .= $tag['where'] != null ? '$map=array(' . trim(str_replace("=", "=>", $tag["where"]), ',') . ');' : '';
        }

        if ($tag["page"]) {
            $parseStr .= '$' . $result . '_count=' . $model;
            $parseStr .= $tag['where'] != null ? '->where($map)' : '';
            $parseStr .= '->cache(true)->count(' . $model . '->getPk());';
            $parseStr .= 'import("ORG.Util.Page"); $' . $result . '_p = new Page($' . $result . '_count, ' . $tag["page"] . ');';
            if ($tag['purlvars'] != null) {
                $parseStr .= '$' . $result . '_page = $' . $result . '_p->show("' . $tag['purl'] . '",' . $tag['purlvars'] . ');';
            } else {
                $parseStr .= '$' . $result . '_page = $' . $result . '_p->show("' . $tag['purl'] . '");';
            }
        }
        $parseStr .= '$' . $result . '_result = ' . $model;
        $parseStr .= $tag['rel
       ation'] != null ? '->relation(array(' . trim($tag["relation"], ',') . '))' : '';
        $parseStr .= $tag['where'] != null ? '->where($map)' : '';
        $parseStr .= $tag['field'] != null ? '->field("' . trim($tag["field"], ',') . '")' : '';
        $parseStr .= $tag['order'] != null ? '->order("' . trim($tag["order"], ',') . '")' : '';
        if ($tag["page"]) {
            $parseStr .= $tag['page'] != null ? '->limit("$' . $result . '_p->firstRow , $' . $result . '_p->listRows")' : '';
        } else {
            $parseStr .= $tag['limit'] != null ? '->limit("' . trim($tag["limit"], ',') . '")' : '';
        }
        $parseStr .= '->select();?>';
        $parseStr .= '<?php if($' . $result . '_result){ $' . $key . '=0;';
        $parseStr .= 'foreach($' . $result . '_result as $key=>$' . $result . '){ ?>';
        $parseStr .= '<?php ++$' . $key . ';$mod = ($' . $key . ' % ' . $mod . ');?>';
        $parseStr .= $content;
        $parseStr .= '<?php }};?>';
        return $parseStr;
    }

    public function _category_select($attr, $content)
    {
        $tag = $this->parseXmlAttr($attr, 'select');
        $id = $tag['id'];
        $name = $tag['name'];
        $selected = $tag['selected'];

        $options = D('Category', "Service")->listSub();
        $parseStr = '<select id="' . $id . '" name="' . $name . '" data-rel="chosen">';
        foreach ($options as $key => $vo) {
            $__LIST__ = $vo['children'];

            if (is_array($__LIST__) && count($__LIST__) !== 0) {
                foreach ($__LIST__ as $key => $child) {
                    $__LIST__ = $child['children'];
                    $parseStr .= '<optgroup label="' . $vo['name'] . ' =>' . $child['name'] . '">';

                    if (is_array($__LIST__) && count($__LIST__) !== 0) {
                        foreach ($__LIST__ as $key => $category) {
                            if (!empty($selected) && ($selected === $category['id'] || $category['id'] === 999999)) {
                                $parseStr .= '<option value="' . $category['id'] . '" selected="selected">' . $category['name'] . '</option>';
                            } else {
                                $parseStr .= '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                            }

                        }
                    }
                    $parseStr .= '</optgroup>';
                }
            }
        }
        $parseStr .= '</select>';

        return $parseStr;
    }

    public function _select($attr, $content)
    {
        //把标签的所有属性解析到$tag数组里面
        $tag = $this->parseXmlAttr($attr, 'select');
        //得到标签里面的属性
        $id = isset($tag['id']) ? $tag['id'] : "";
        $name = isset($tag['name']) ? $tag['name'] : "";
        $model = $tag['model'];
        $selected = isset($tag['selected']) ? $tag['selected'] : 0;
        $disabled = isset($tag['disabled']) ? $tag['disabled'] : 0;
        $style = isset($tag['style']) ? $tag['style'] : "";
        $other = isset($tag['other']) ? $tag['other'] : "";
        $appendoption = isset($tag['appendoption']) && $tag['appendoption'] === 'false' ? false : true;
        $parsestr = '<select id="' . $id . '"  name="' . $name . '" class="' . $style . '" data-rel="chosen" ' . $other . '> ';

        /**
         * 如果是选择LEVEL
         */
        if ($model === 'level') {
            //固定数据
            $arr = array("请选择", "项目", "模块", "操作");

            //生成OPTION列表
            for ($i = 1; $i < 4; $i++) {
                $sel = '<?php if( ' . $i . ' == ' . $selected . '){ echo "selected=\"selected\"";} ?>';
                $parsestr .= '<option value="' . $i . '" ' . $sel . '>' . $arr[$i] . '</option>';
            }

        } elseif ($model === 'node') {
            /**
             * 如果是节点的下拉框
             */
            //生成分类树
            import("@.ORG.Category");
            $cat = new Category($model, array('id', 'pid', 'name', 'fullname'));
            $list = $cat->getList("status=1");

            //生成OPTION列表，增加一个默认的根节点选择
            $parsestr .= '<option value="0" level="0">根节点</option>';
            foreach ($list as $k => $v) {
                $dis = '<?php if( ' . $v['id'] . ' == ' . $disabled . '){ echo "disabled=\"disabled\"";} ?>';
                $sel = '<?php if( ' . $v['id'] . ' == ' . $selected . '){ echo "selected=\"selected\"";} ?>';
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '  level="' . $v['level'] . '" >' . $v['fullname'] . '(' . $v['title'] . ')' . '</option>';
            }

        } elseif ($model === 'role') {
            /**
             * 如果是选择角色下拉列表
             */
            import("@.ORG.Category");
            $cat = new Category($model, array('id', 'pid', 'name', 'fullname'));
            $list = $cat->getList("status=1");

            //生成OPTION列表，增加一个默认的根节点选择
            if ($appendoption) {
                $parsestr .= '<option value="0">无</option>';
            }
            foreach ($list as $k => $v) {
                $dis = '<?php if( ' . $v['id'] . ' == ' . $disabled . '){ echo "disabled=\"disabled\"";} ?>';
                $sel = '<?php if( ' . $v['id'] . ' == ' . $selected . '){ echo "selected=\"selected\"";} ?>';
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '>' . $v['fullname'] . '</option>';
            }


        } else if ($model === "category") {
            /**
             * 商品分类选择列表
             */
            if ($appendoption) {
                $parsestr .= '<option value="0">无</option>';
            }
            import("@.ORG.Category");
            $cat = new Category($model, array('id', 'pid', 'name', 'fullname'));
            $list = $cat->getList("status=1"); //获取分类结构
            foreach ($list as $k => $v) {
                $dis = '<?php if( ' . $v['id'] . ' == ' . $disabled . '){ echo "disabled=\"disabled\"";} ?>';
                $sel = '<?php if( ' . $v['id'] . ' == ' . $selected . '){ echo "selected=\"selected\"";} ?>';
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '>' . $v['id'] . $v['fullname'] . '</option>';
            }

        } else if ($model === 'supplier') {
            /**
             * 选择供货商
             */
            if ($appendoption) {
                $parsestr .= '<option value="0">无</option>';
            }
            $list = M($model)->where("status=1")->select();
            foreach ($list as $k => $v) {
                $dis = $v['id'] == $disabled ? ' disabled="disabled"' : "";
                $sel = $v['id'] == $selected ? ' selected="selected"' : "";
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '>' . $v['real_name'] . '</option>';
            }

        } elseif ($model === 'branch') {
            /**
             * 选择分店
             */
            if ($appendoption) {
                $parsestr .= '<option value="0">无</option>';
            }
            $list = M($model)->select();
            foreach ($list as $k => $v) {
                $dis = '<?php if( ' . $v['id'] . ' == ' . $disabled . '){ echo "disabled=\"disabled\"";} ?>';
                $sel = '<?php if( ' . $v['id'] . ' == ' . $selected . '){ echo "selected=\"selected\"";} ?>';
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '>' . $v['name'] . '</option>';
            }
        } elseif ($model === 'staff') {
            /**
             * 选择员工
             */
            if ($appendoption) {
                $parsestr .= '<option value="0">暂无</option>';
            }

            $list = M($model)->where(array("id" => array("gt", 1)))->select();
            foreach ($list as $k => $v) {
                $dis = '<?php if( ' . $v['id'] . ' == ' . $disabled . '){ echo "disabled=\"disabled\"";} ?>';
                $sel = '<?php if( ' . $v['id'] . ' == ' . $selected . '){ echo "selected=\"selected\"";} ?>';
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '>' . $v['name'] . '</option>';
            }
        } else {
            $list = M($model)->select();
            foreach ($list as $k => $v) {
                $dis = '<?php if( ' . $v['id'] . ' == ' . $disabled . '){ echo "disabled=\"disabled\"";} ?>';
                $sel = '<?php if( ' . $v['id'] . ' == ' . $selected . '){ echo "selected=\"selected\"";} ?>';
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '>' . $v['name'] . '</option>';
            }
        }

        $parsestr .= '</select>';
        return $parsestr;
    }
}