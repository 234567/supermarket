<?php


/**
 * 模板里面使用到的时间格式化函数
 */
function toDate($time, $format = 'Y-m-d H:i:s') {
    if (empty ( $time )) {
        return '';
    }
    $format = str_replace ( '#', ':', $format );
    return date ($format, $time );
}



/**
 *
+--------------------------------------------------------------------
 * Description 友好显示时间
+--------------------------------------------------------------------
 * @param int $time 要格式化的时间戳 默认为当前时间
+--------------------------------------------------------------------
 * @return string $text 格式化后的时间戳
+--------------------------------------------------------------------
 * @author yijianqing
 * 来源：http://www.thinkphp.cn/code/40.html
+--------------------------------------------------------------------
 */
function mdate($time = NULL) {
    if (empty ( $time )) {
        return '';
    }
    $text = '';
    $now = time();
    $time = $time === NULL ? $now : intval($time);
    if($time <= $now){
        $t = $now - $time; //时间差 （秒）
        if ($t == 0)
            $text = '刚刚';
        elseif ($t < 60)
            $text = $t . '秒前'; // 一分钟内
        elseif ($t < 60 * 60)
            $text = floor($t / 60) . '分钟前'; //一小时内
        elseif ($t < 60 * 60 * 24)
            $text = floor($t / (60 * 60)) . '小时前'; // 一天内
        elseif ($t < 60 * 60 * 24 * 3)
            $text = floor($t/(60*60*24)) ==1? '昨天 ' . date('H:i', $time) : '前天 ' . date('H:i', $time) ; //昨天和前天
        elseif ($t < 60 * 60 * 24 * 30)
            $text = date('m月d日 H:i', $time); //一个月内
        elseif ($t < 60 * 60 * 24 * 365)
            $text = date('m月d日', $time); //一年内
        else
            $text = date('Y年m月d日', $time); //一年以前
    }else{
        $t = $time - $now; //时间差 （秒）
        if ($t < 60)
            $text = $t . '秒后';
        elseif ($t < 60 * 60)
            $text = floor($t / 60) . '分钟'.floor($t % 60).'秒后';
        elseif ($t < 60 * 60 * 24)
            $text = floor($t / (60 * 60)) . '小时'.floor($t %(60*60) ).'分钟后';
        elseif ($t < 60 * 60 * 24 * 3)
            $text = floor($t/(60*60*24))=== 1 ? '明天 ' . date('H:i', $time) : '后天 ' . date('H:i', $time) ;
        elseif ($t < 60 * 60 * 24 * 30)
            $text = date('m月d日 H:i', $time); //一个月以内
        elseif ($t < 60 * 60 * 24 * 365)
            $text = date('m月d日', $time); //一年以内
        else
            $text = date('Y年m月d日', $time); //一年以后

    }

    return $text;
}

//
//function genTree($items,$id='id',$pid='pid',$son = 'children'){
//    $tree = array(); //格式化的树
//    $tmpMap = array();  //临时扁平数据
//
//    foreach ($items as $item) {
//        $tmpMap[$item[$id]] = $item;
//    }
//
//    foreach ($items as $item) {
//        if (isset($tmpMap[$item[$pid]])) {
//            $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
//        } else {
//            $tree[] = &$tmpMap[$item[$id]];
//        }
//    }
//    unset($tmpMap);
//    return $tree;
//}