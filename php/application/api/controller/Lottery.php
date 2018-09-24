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

class Lottery extends Base
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

    /*api:f7cbd4d84eec5b63f3edb55034775e00*/
    /**
     * 购彩选号
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function selectNumber($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:f7cbd4d84eec5b63f3edb55034775e00*/

    /*api:8c67f0c72b9914a112286f3b49baf97b*/
    /**
     * 投注单列表
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function betsLists($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:8c67f0c72b9914a112286f3b49baf97b*/

    /*api:073956fa60eb5d41d122e529e0fe245e*/
    /**
     * 删除投注
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function betsDel($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:073956fa60eb5d41d122e529e0fe245e*/

    /*api:2352754a7e9e151eb789c0d2a869bbfc*/
    /**
     * 期号校验
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function termNumberCheck($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:2352754a7e9e151eb789c0d2a869bbfc*/

    /*api:38438ef47169e464eee3ff4b6c17bf4e*/
    /**
     * 下注
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function bets($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:38438ef47169e464eee3ff4b6c17bf4e*/

    /*api:b88cb069f61a234ce2b9053751df948f*/
    /**
     * 历史开奖查询列表接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function historyList($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:b88cb069f61a234ce2b9053751df948f*/

    /*api:d41a14d4c736a3cd2f54cdb07cab98e0*/
    /**
     * 开奖结果列表接口
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function resList($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:d41a14d4c736a3cd2f54cdb07cab98e0*/

    /*api:e127cb94a3203cc117abf7c5979515f3*/
    /**
     * 彩票规则详情
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function ruleinfo($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:e127cb94a3203cc117abf7c5979515f3*/

    /*api:9806dd2899e8c705bc33b1c38a384229*/
    /**
     * 选号
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function selectNumber2($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:9806dd2899e8c705bc33b1c38a384229*/

    /*api:d7231e54280ba2a4e7a4cc04663fdabe*/
    /**
     * 注单
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function orderList($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:d7231e54280ba2a4e7a4cc04663fdabe*/

    /*api:e286c620a12fb16aaf1fb0b683c9b06a*/
    /**
     * 注单列表（后台管理）
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function orderListForAdmin($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:e286c620a12fb16aaf1fb0b683c9b06a*/

    /*api:9ce6cd7c1c84ea4fda791f2c8dabfd41*/
    /**
     * 测试中奖数据
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function testwin($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:9ce6cd7c1c84ea4fda791f2c8dabfd41*/

    /*api:a1d0b88435868652be9fad2e94c20896*/
    /**
     * 注单2
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function orderList2($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:a1d0b88435868652be9fad2e94c20896*/

    /*api:d2a8a5f4ff6cde000cbc13a019cfb436*/
    /**
     * 注单详情
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function orderDetail($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:d2a8a5f4ff6cde000cbc13a019cfb436*/

    /*api:e357d94342d157266372ce935ef96cf9*/
    /**
     * 小赔率列表
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function ruleList($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:e357d94342d157266372ce935ef96cf9*/

    /*接口扩展*/

    
    public function getlottery($parame = []){
        //执行接口调用
        return $this->execInside($parame);
    }
}