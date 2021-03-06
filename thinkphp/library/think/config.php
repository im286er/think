<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class Config {
    static private  $config =   [];   // 配置参数
    static private  $range  =   '_sys_';   // 参数作用域

    // 设定配置参数的作用域
    static public function range($range){
        self::$range   =   $range;
    }

    // 解析其他格式的配置参数
    static public function parse($config,$type='',$range=''){
        if(empty($type)) {
            $type   =   substr(strrchr($config, '.'),1);
        }
        $class  =   '\\think\\config\driver\\'.strtolower($type);
        self::set((new $class())->parse($config),'',$range);
    }

    // 加载配置文件
    static public function load($file,$range=''){
        return self::set(include $file,'',$range);
    }

    // 检测配置是否存在
    static public function has($name,$range=''){
        $range  =   $range ? $range : self::$range;
        $name   =   strtolower($name);

        if (!strpos($name, '.')) {
            return isset(self::$config[$range][$name]);
        }else{
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            return isset(self::$config[$range][$name[0]][$name[1]]);
        }
    }

    // 获取配置参数 为空则获取所有配置
    static public function get($name=null,$range='') {
        $range  =   $range ? $range : self::$range;
        // 无参数时获取所有
        if (empty($name) && isset(self::$config[$range])) {
            return self::$config[$range];
        }
        $name = strtolower($name);
        if (!strpos($name, '.')) {
            // 判断环境变量
            if(isset($_ENV[ENV_PREFIX.$name])){
                return $_ENV[ENV_PREFIX.$name];
            }
            return isset(self::$config[$range][$name]) ? self::$config[$range][$name] : null;
        }else{
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            // 判断环境变量
            if(isset($_ENV[ENV_PREFIX.$name[0].'_'.$name[1]])){
                return $_ENV[ENV_PREFIX.$name[0].'_'.$name[1]];
            }            
            return isset(self::$config[$range][$name[0]][$name[1]]) ? self::$config[$range][$name[0]][$name[1]] : null;
        }
    }
    
    // 设置配置参数 name为数组则为批量设置
    static public function set($name, $value=null,$range='') {
        $range  =   $range ? $range : self::$range;
        if(!isset(self::$config[$range])) {
            self::$config[$range]  =   [];
        }
        if (is_string($name)) {
            $name = strtolower($name);
            if (!strpos($name, '.')) {
                self::$config[$range][$name] = $value;
            }else{
                // 二维数组设置和获取支持
                $name = explode('.', $name);
                self::$config[$range][$name[0]][$name[1]] = $value;                
            }
            return;
        }elseif (is_array($name)){         
            // 批量设置
            self::$config[$range] = array_merge(self::$config[$range], array_change_key_case($name));
            return self::$config[$range];
        }else{
            return null; // 避免非法参数
        }
    }

}
