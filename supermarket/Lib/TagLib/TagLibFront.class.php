<?php
/**
 *
 * 自定义的标签库
 *
 *
 */

class TagLibFront extends TagLib {
    protected $tags = array(
        'list' => array('attr' => 'table,where,order,limit,relation,field,result,page,purl,purlvars', 'close' => 1),
        'category_select'    =>  array('attr'=>'id,name,selected','close'=>0),
        'select' => array("attr"=>"id,name,model,selected,disabled,style","close"=>0),
    );
    //获取列表数据 可关联(字段过滤无效) 可分页
    public function _list($attr, $content) {
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
        if($tag['where']!= null && $tag['where']=='$where') {
            $parseStr .=$tag['where'] != null ? '$map='.$tag["where"].';' : '';
        }else {
            $parseStr .=$tag['where'] != null ? '$map=array(' . trim(str_replace("=", "=>", $tag["where"]),',') . ');' : '';
        }

        if ($tag["page"]) {
            $parseStr .= '$' . $result . '_count=' . $model;
            $parseStr .= $tag['where'] != null ? '->where($map)' : '';
            $parseStr .='->cache(true)->count(' . $model . '->getPk());';
            $parseStr .= 'import("ORG.Util.Page"); $' . $result . '_p = new Page($' . $result . '_count, ' . $tag["page"] . ');';
            if($tag['purlvars']!=null) {
                $parseStr .= '$' . $result . '_page = $' . $result . '_p->show("'.$tag['purl'].'",'.$tag['purlvars'].');';
            }else {
                $parseStr .= '$' . $result . '_page = $' . $result . '_p->show("'.$tag['purl'].'");';
            }
        }
        $parseStr .= '$' . $result . '_result = ' . $model ;
        $parseStr .= $tag['rel
       ation'] != null ? '->relation(array(' . trim($tag["relation"],',') . '))' : '';
        $parseStr .= $tag['where'] != null ? '->where($map)' : '';
        $parseStr .= $tag['field'] != null ? '->field("'.trim($tag["field"],',').'")' : '';
        $parseStr .= $tag['order'] != null ? '->order("'.trim($tag["order"],',').'")' : '';
        if ($tag["page"]) {
            $parseStr .= $tag['page'] != null ? '->limit("$' . $result . '_p->firstRow , $' . $result . '_p->listRows")' : '';
        }else {
            $parseStr .= $tag['limit'] != null ? '->limit("'.trim($tag["limit"],',').'")' : '';
        }
        $parseStr .= '->select();?>';
        $parseStr .= '<?php if($' . $result . '_result){ $' . $key . '=0;';
        $parseStr .= 'foreach($' . $result . '_result as $key=>$' . $result . '){ ?>';
        $parseStr .= '<?php ++$' . $key . ';$mod = ($' . $key . ' % ' . $mod . ');?>';
        $parseStr .= $content;
        $parseStr .= '<?php }};?>';
        return $parseStr;
    }

    public function _category_select($attr, $content) {
        $tag = $this->parseXmlAttr($attr, 'select');
        $id = $tag['id'];
        $name = $tag['name'];
        $selected = $tag['selected'];

        $options = D('Category')->listSub();
        $parseStr = '<select id="'.$id.'" name="'.$name.'" data-rel="chosen">';
        foreach($options as $key => $vo){
            $__LIST__ = $vo['children'];
            if(is_array($__LIST__ ) && count($__LIST__) !==0){
                foreach($__LIST__ as $key => $child){
                    $__LIST__ = $child['children'];
                    $parseStr .= '<optgroup label="'.$vo['category_name'].' =>'. $child['category_name'].'">';
                    if(is_array($__LIST__) && count($__LIST__) !==0){
                        foreach($__LIST__ as $key => $category){
                            if(!empty($selected) && ($selected === $category['category_id'] ||  $category['category_id'] === 9999)){
                                $parseStr .= '<option value="'.$category['category_id'].'" selected="selected">'.$category['category_name'].'</option>';
                            }else{
                                $parseStr .= '<option value="'.$category['category_id'].'">'.$category['category_name'].'</option>';
                            }

                        }
                    }
                    $parseStr .= '</optgroup>';
                }
            }
        }
        $parseStr.='</select>';

        return $parseStr;
    }

//  'select' => array("attr"=>"id,name,selected,style","close"=>0),
    public function _select($attr,$content){
        //把标签的所有属性解析到$tag数组里面
        $tag = $this->parseXmlAttr($attr, 'select');
        //得到标签里面的属性
        $id = $tag['id'] ? $tag['id'] : "";
        $name = $tag['name'] ? $tag['name'] : "";
        $model = $tag['model'];
        $selected = $tag['selected'] ? $tag['selected'] : 0;
        $disabled = $tag['disabled'] ? $tag['disabled'] : 0;
        $style= $tag['style'];

        $parsestr = '<select id="'.$id.'"  name="'.$name.'" class="'.$style.'" > ';

        if($model === 'level'){

            $arr = array("请选择", "项目", "模块", "操作");
            for ($i = 1; $i < 4; $i++) {
                $sel = ( $selected == $i ) ? " selected='selected'" : "";
                $parsestr .='<option value="' . $i . '" ' . $sel . '>' . $arr[$i] . '</option>';
            }

        }elseif($model === 'node'){
            import("@.ORG.Category");
            $cat = new Category($model, array('id', 'pid', 'name', 'fullname'));
            $list = $cat->getList();               //获取分类结构
            $parsestr .= '<option value="0" level="-1">根节点</option>';
            foreach ($list as $k => $v) {
                $dis = $v['level'] == $disabled ? ' disabled="disabled"' : "";
                $sel = $v['id'] == $selected ? ' selected="selected"' : "";
                    $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '  level="' . $v['level'] .  '" >' . $v['fullname'].'('.$v['title'].')'.'</option>';
            }
        }elseif($model === 'role'){
            import("@.ORG.Category");
            $cat = new Category($model, array('id', 'pid', 'name', 'fullname'));
//            $cat->add(array("id"=>-1,"pid"=>0,"name"=>"无"));
            $list = $cat->getList();               //获取分类结构
            foreach ($list as $k => $v) {
                $dis = $v['id'] == $disabled ? ' disabled="disabled"' : "";
                $sel = $v['id'] == $selected ? ' selected="selected"' : "";
                $parsestr .= '<option value="' . $v['id'] . '"' . $sel . $dis . '>' . $v['fullname'].'</option>';
            }
        }



        $parsestr .= '</select>';
        return $parsestr;
    }
}