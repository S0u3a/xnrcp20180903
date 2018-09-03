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

class Bank extends Base
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

    /*api:f57722e0ddbddb9bd1e98af00940f4b1*/
    /**
     * 用户银行卡绑定
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function userBind($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:f57722e0ddbddb9bd1e98af00940f4b1*/

    /*api:437d02ea932f39d5f717531b6fef6163*/
    /**
     * 用户绑定银行卡详情
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function userBankDetail($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:437d02ea932f39d5f717531b6fef6163*/

    /*api:c563e522fc3935420b9163c1874b86a0*/
    /**
     * 用户删除绑定的银行卡
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function userDelBind($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:c563e522fc3935420b9163c1874b86a0*/

    /*api:b88ebc257638a0480a07f0bfb2c79037*/
    /**
     * 用户提现申请
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function cash($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:b88ebc257638a0480a07f0bfb2c79037*/

    /*api:f29fe215b9ee885e349dd450cbb829d8*/
    /**
     * 提现状态记录
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function cashList($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:f29fe215b9ee885e349dd450cbb829d8*/

    /*api:dd7072fdb56159fdea1c56c8c23f2607*/
    /**
     * 修改提现密码
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function cashPwdUpdate($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:dd7072fdb56159fdea1c56c8c23f2607*/

    /*api:6d24faeacf3004fbbb3d4691a67d0767*/
    /**
     * 设置提现密码
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function setCashPwd($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:6d24faeacf3004fbbb3d4691a67d0767*/

    /*api:39b517e262a046f2f6a36c0524a4f42d*/
    /**
     * 手机短信找回提现密码
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function cashPwdBack($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:39b517e262a046f2f6a36c0524a4f42d*/

    /*api:7716a7ab92a0bc4cf3c0c7a68a805fd4*/
    /**
     * 后台提现记录（后台）
     * @access public
     * @param  [array] $parame 扩展参数
     * @return [json]          接口数据输出
    */
    public function cashListForAdmin($parame = []){

        //执行接口调用
        return $this->execApi($parame);
    }

    /*api:7716a7ab92a0bc4cf3c0c7a68a805fd4*/

    /*接口扩展*/
}