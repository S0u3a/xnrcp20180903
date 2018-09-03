<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 杭州新苗科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 */
namespace app\api\controller;

use app\common\controller\Base;

class Sys extends Base
{
    //接口构造
    public function __construct(){

        parent::__construct();
    }

    /*api:d815b04b2ed4a96e0d0021c7f6281c8d*/
    /**
     * 系统菜单列表接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function menus($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:d815b04b2ed4a96e0d0021c7f6281c8d*/

    /*api:fdab5daa279751b566dac6b93e74cbf3*/
    /**
     * 系统表单模板详情接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function fromdetail($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:fdab5daa279751b566dac6b93e74cbf3*/

    /*api:fe53f10007aa86f7a470e2c094f30acc*/
    /**
     * 系统表单模板列表接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function fromtpl($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:fe53f10007aa86f7a470e2c094f30acc*/

    /*api:b3b651a930b24846b7452bde6437daea*/
    /**
     * 系统列表模板详情接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function listdetail($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:b3b651a930b24846b7452bde6437daea*/

    /*api:72779f977b706fa9f1dde9255acee339*/
    /**
     * 系统列表模板列表接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function listtpl($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:72779f977b706fa9f1dde9255acee339*/

    /*api:eb49daa8729e795c14d6bda11d254b09*/
    /**
     * 清理缓存
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function clearCache($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:eb49daa8729e795c14d6bda11d254b09*/

    /*api:cb115b3f0116f9cabf9c46503a23027d*/
    /**
     * 系统配置接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function config($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:cb115b3f0116f9cabf9c46503a23027d*/

    /*接口扩展*/
}