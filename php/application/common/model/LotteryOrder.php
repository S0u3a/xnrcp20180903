<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 杭州新苗科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Model是根据数据库的表名一一对应的文件，文件名和类名必须是表名，采用驼峰命名法，表名如果有下划线(_)需去除，然后
 * 将紧挨下划线的字母大写
 */
namespace app\common\model;

use think\Model;
use think\Db;

class LotteryOrder extends Base
{
    //默认主键为id，如果你没有使用id作为主键名，需要在此设置
    protected $pk = 'id';

    //默认查询方法，如果特殊需求，则自行改造
    public function formatWhereDefault($model,$parame)
    {
        if (isset($parame['search']) && !empty($parame['search'])) {

          $search     = json_decode($parame['search'],true);

          if (!empty($search)) {

            foreach ($search as $key => $value) {

              if (!empty($value) && (is_string($value) || is_numeric($value)) ) {

                $model->where('main.'.$key,'eq',$value);
              }
            }
          }
        }

        $model->where('main.uid','=',$parame['uid']);

        if (isset($parame['otype']) && $parame['otype'] == 1) {
            $model->where('main.status','>=',1);
        }
        if (isset($parame['otype']) && $parame['otype'] == 2) {
            $model->where('main.status','=',3);
            $model->where('main.iswin','=',1);
        }
        if (isset($parame['otype']) && $parame['otype'] == 3) {
            $model->where('main.status','=',2);
        }
        if (isset($parame['otype']) && $parame['otype'] == 4) {
            $model->where('main.status','=',1);
        }

        return $model;
    }

    //默认查询方法，如果特殊需求，则自行改造
    public function orderListForAdmin($model,$parame)
    {
        if (isset($parame['search']) && !empty($parame['search'])) {

          $search     = json_decode($parame['search'],true);

          if (!empty($search)) {

            foreach ($search as $key => $value) {

              if (!empty($value) && (is_string($value) || is_numeric($value)) ) {

                $model->where('main.'.$key,'eq',$value);
              }
            }
          }
        }

        return $model;
    }
    public function getLotteryOrderListByLotteryid($lottery_id=0,$uid=0)
    {
        if ($lottery_id <= 0)  return [];

        $map    = [];
        $map[]  = ['lottery_id','=',$lottery_id];
        $map[]  = ['status','=',1];
        $map[]  = ['uid','=',$uid];

        $list   = $this->where($map)->select()->toArray();
        return $list;
    }

    public function getLotteryOrderList()
    {
        //未开奖并切到开奖时间
        $map    = [];
        $map[]  = ['status','=',2];
        $map[]  = ['opentimestamp','<=',time()];

        $list   = $this->where($map)->limit(20)->order('id desc')->select();

        return !empty($list) ? $list->toArray() : [];
    }

    //自行扩展更多
    //...
}