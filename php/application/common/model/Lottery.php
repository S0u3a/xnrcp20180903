<?php
/**
 * Model层-模型基础类
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Cache;

class Lottery extends Base
{
  public function getLotteryInfoByExpect1($expect)
  {
    $info   = $this->where('expect','=',$expect)->field('id,opencode,opentimestamp')->find();
    return !empty($info) ? $info->toArray() : [];
  }

	public function getLotteryInfoByExpect($expect)
  {	
  	$cacheKey 	= 'table_' . $this->name . '_' . $expect;
  	$info 		= cache($cacheKey);
  	$info 		= !empty($info) ? $info : $this->where('expect','=',$expect)->find();
    return !empty($info) ? $info->toArray() : [];
  }

  public function saveLotteryInfoAll($data)
  {
    return $this->saveAll($data);
  }

  public function delLotteryInfoByExpect($expect)
  {
  	cache('table_' . $this->name . '_' . $expect,null);
    return $this->where('expect','=',$expect)->delete();
  }
  
  public function getLotteryInfoLatelyOpen()
  {
    $info     = $this->where('status','=',1)->order('id desc')->find();
    $info     = !empty($info) ? $info->toArray() : $info;
    return $info;
  }

  public function formatWhereDefault2($model,$parame)
  {
    $model->where('opentimestamp','elt',time());

    return $model;
  }
}
