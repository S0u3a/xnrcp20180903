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

class Article extends Base
{
    //接口构造
    public function __construct(){

        parent::__construct();
    }

    /**
     * 数据列表接口头
     * @access public
     * @param  [array] $parame [扩展参数]
     * @return [json]          [接口数据输出]
    */
    public function listData($parame = [])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据添加/更新头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function saveData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据详情头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function detailData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据快捷编辑头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function quickEditData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /**
     * 接口数据删除头
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
     */
    public function delData($parame=[])
    {
        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:3285f044aed7ea73b2355d4a88115a48*/
    /**
     * 系统公告接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function notice($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:3285f044aed7ea73b2355d4a88115a48*/

    /*api:30d51582facf7bd82a5b655219452c2d*/
    /**
     * 公告列表
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function noticeList($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:30d51582facf7bd82a5b655219452c2d*/

    /*api:223a25b84703b1576806342b71bbbf6c*/
    /**
     * 说明文案详细接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function sm($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:223a25b84703b1576806342b71bbbf6c*/

    /*接口扩展*/
}