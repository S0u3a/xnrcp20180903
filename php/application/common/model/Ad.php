<?php
namespace app\common\model;

use think\Model;
use think\Db;

class Ad extends Base
{
    //默认主键为id，如果你没有使用id作为主键名，需要在此设置
    protected $pk = 'id';

    public function formatWhereDefault($model,$parame){

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

        if (isset($parame['pos_id'])) {
          
          $model->where('main.pos_id','eq',intval($parame['pos_id']));
        }

        if (isset($parame['status'])) {
          
          $model->where('main.status','eq',intval($parame['status']));
        }

        return $model;
    }

    public function checkValue($value,$id,$field){

        $res    = $this->where('id','not in',[$id])->where($field,'eq',$value)->value($field);

        return !empty($res) ? true : false;
    }
}
