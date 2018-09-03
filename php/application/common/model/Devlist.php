<?php
/**
 * Model层-代码示例
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\common\model;

use think\Model;
use think\Db;

class Devlist extends Base
{
    //默认主键为id，如果你没有使用id作为主键名，需要在此设置
    protected $pk = 'id';

    public function formatWhereDefault($model,$parame){
        
        if (is_numeric($parame['pid'])) {

            $model->where('pid','eq',$parame['pid']);
        }else{

            $pid        = $this->where('cname','eq',$parame['pid'])->value('id');

            $model->where('pid','eq',$pid);
        }

        return $model;
    }

    public function formatWhereChildList($model,$parame){

        $model->where('pid','=',$parame['id']);

        return $model;
    }

    public function checkValue($value,$id,$field){

        $info   = $this->getOneById($id);

        $pid    = empty($info) ? 0 : $info['pid'];

    	$res    = $this->where('id','<>',$id)->where('pid','=',$pid)->where($field,'eq',$value)->value($field);

    	return !empty($res) ? true : false;
    }
}
