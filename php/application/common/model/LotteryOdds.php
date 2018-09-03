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

class LotteryOdds extends Base
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

        return $model;
    }

    public function getLotteryOddsByTag($tag)
    {
      $info     = $this->where('rule_tag','=',$tag)->find();
      return !empty($info) ? $info->toArray() : [];
    }

    public function getLotteryAgentOddsByUid($uid,$tag)
    {
      $map      = [];
      $map[]    = ['uid','=',$uid];
      $map[]    = ['rule_tag','=',$tag];
      $info     = $this->where($map)->find();
      return !empty($info) ? $info->toArray() : [];
    }

    public function saveLotteryOdds($tag,$updata)
    {
      if (empty($tag) || empty($updata)) return false;

      $id     = $this->where('rule_tag','=',$tag)->value('id');
      if (empty($id) || $id <= 0) {
        $updata['rule_tag'] = $tag;
        $this->addData($updata);
      }else{
        $this->updateById($id,$updata);
      }
      return true;
    } 
    //自行扩展更多
    //...
}