<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think;
/**
 * ThinkPHP系统钩子实现
 */
class Hook {

    static private  $tags       =   array();
    /**
     * 监听标签的插件
     * @param string $tag 标签名称
     * @param mixed $params 传入参数
     * @param mixed $action 方法名
     * @return void
     */
    static public function event($tag, &$params=NULL,$action='run') {
        if(isset(self::$tags[$tag])) {
            if(APP_DEBUG) {
                G($tag.'Start');
                trace('[ '.$tag.' ] --START--','','INFO');
            }
            if(count(self::$tags[$tag]) > 0){
                $result = null;
                $name = self::$tags[$tag][0];
                if('Behavior' == substr($name,-8) ){
                    // 行为扩展必须用run入口方法
                    $class = $name;
                    $tag    =   'run';
                }else{
                    $class   =  "plugins\\{$name}\\{$name}Plugin";
                }
                if(class_exists($class)){ //ThinkCMF NOTE 插件或者行为存在时才执行
                    $addon   = new $class();
                    $result = $addon->$action($params);
                }
                if(APP_DEBUG) { // 记录行为的执行日志
                    trace('[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]','','INFO');
                }
                return $result;
            }

        }
        return;
    }
    /**
     * 动态添加插件到某个标签
     * @param string $tag 标签名称
     * @param mixed $name 插件名称
     * @return void
     */
    static public function add($tag,$name) {
        if(!isset(self::$tags[$tag])){
            self::$tags[$tag]   =   array();
        }
        if(is_array($name)){
            self::$tags[$tag]   =   array_merge(self::$tags[$tag],$name);
        }else{
            self::$tags[$tag][] =   $name;
        }
    }

    /**
     * 批量导入插件
     * @param array $data 插件信息
     * @param boolean $recursive 是否递归合并
     * @return void
     */
    static public function import($data,$recursive=true) {
        if(!$recursive){ // 覆盖导入
            self::$tags   =   array_merge(self::$tags,$data);
        }else{ // 合并导入
            foreach ($data as $tag=>$val){
                if(!isset(self::$tags[$tag]))
                    self::$tags[$tag]   =   array();            
                if(!empty($val['_overlay'])){
                    // 可以针对某个标签指定覆盖模式
                    unset($val['_overlay']);
                    self::$tags[$tag]   =   $val;
                }else{
                    // 合并模式
                    self::$tags[$tag]   =   array_merge(self::$tags[$tag],$val);
                }
            }            
        }
    }

    /**
     * 获取插件信息
     * @param string $tag 插件位置 留空获取全部
     * @return array
     */
    static public function get($tag='') {
        if(empty($tag)){
            // 获取全部的插件信息
            return self::$tags;
        }else{
            return self::$tags[$tag];
        }
    }

    /**
     * 监听标签的插件
     * @param string $tag 标签名称
     * @param mixed $params 传入参数
     * @return void
     */
    static public function listen($tag, &$params=NULL) {
        if(isset(self::$tags[$tag])) {
            if(APP_DEBUG) {
                G($tag.'Start');
                trace('[ '.$tag.' ] --START--','','INFO');
            }
            foreach (self::$tags[$tag] as $name) {
                APP_DEBUG && G($name.'_start');
                $result =   self::exec($name, $tag,$params);
                if(APP_DEBUG){
                    G($name.'_end');
                    trace('Run '.$name.' [ RunTime:'.G($name.'_start',$name.'_end',6).'s ]','','INFO');
                }
                if(false === $result) {
                    // 如果返回false 则中断插件执行
                    return ;
                }
            }
            if(APP_DEBUG) { // 记录行为的执行日志
                trace('[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]','','INFO');
            }
        }
        return;
    }
    
    /**
     * 监听标签的插件,只执行一个插件
     * @param string $tag 标签名称
     * @param mixed $params 传入参数
     * @return mixed boolean|mixed
     */
    static public function listen_one($tag, &$params=NULL) {
        if(isset(self::$tags[$tag])) {
            if(APP_DEBUG) {
                G($tag.'Start');
                trace('[ '.$tag.' ] --START--','','INFO');
            }
            
            if(count(self::$tags[$tag])>0){
                $name=self::$tags[$tag][0];
                APP_DEBUG && G($name.'_start');
                $result =   self::exec($name, $tag,$params);
                if(APP_DEBUG){
                    G($name.'_end');
                    trace('Run '.$name.' [ RunTime:'.G($name.'_start',$name.'_end',6).'s ]','','INFO');
                }
                return $result;
            }
            if(APP_DEBUG) { // 记录行为的执行日志
                trace('[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]','','INFO');
            }
        }
        return false;
    }

    /**
     * 执行某个插件
     * @param string $name 插件名称
     * @param string $tag 方法名（标签名）     
     * @param Mixed $params 传入的参数
     * @return void
     */
    static public function exec($name, $tag,&$params=NULL) {
        if('Behavior' == substr($name,-8) ){
            // 行为扩展必须用run入口方法
        	$class = $name;
            $tag    =   'run';
        }else{
        	$class   =  "plugins\\{$name}\\{$name}Plugin";
        }
        if(class_exists($class)){ //ThinkCMF NOTE 插件或者行为存在时才执行
        	$addon   = new $class();
        	return $addon->$tag($params);
        }
        
    }
}
