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
namespace app\home\controller;

use app\common\controller\Base;

class Article extends Base
{
    public function index()
    {
    	$param 	= request()->param();

    	$atype 	= isset($param['atype']) ? intval($param['atype']) : 0;
    	echo $atype;exit();
    }
}