<?php
/**
 * Model层-管理员操作日志模型
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\common\model;

use think\Model;

class Logs extends Base
{
	//默认主键为id，如果你没有使用id作为主键名，需要在此设置
	protected $pk = 'id';

	public function formatWhereDefault($model,$parame){
		
		if (isset($parame['search']) && !empty($parame['search'])) {

			$search 		= json_decode($parame['search'],true);

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

	public function addLog($parame){

		if (empty($parame)) return false;

		$uname 		= (isset($parame['uname']) && !empty($parame['uname'])) ? $parame['uname'] : '';
		$uid 		= isset($parame['uid']) ? intval($parame['uid']) : 0;
		$log_type 	= isset($parame['log_type']) ? intval($parame['log_type']) : 0;

		if (empty($uname)) {

			 $uname 	= $uid > 0 ? '用户-'.$parame['uid'] : '系统-0';
		}

		if (isset($parame['info']) && !empty($parame['info'])) {
			
			$add['log_time'] 	= time();
			$add['uname'] 		= $uname;
		    $add['uid'] 		= $uid;
		    $add['log_info'] 	= $parame['info'];
		    $add['log_ip'] 		= request()->ip();
		    $add['log_url'] 	= request()->baseUrl();
		    $add['log_type'] 	= $log_type;
		    $add['cache_tag'] 	= 'log_type_'.$log_type;

		    $this->addData($add);

		    return true;
		}

		return false;
	}

	public function clearData($parame){

		$log_type 	= isset($parame['log_type']) ? intval($parame['log_type']) : 0;

		$count 		= $this->where('log_type','in',[0,$log_type])->count();
		
		$this->where('log_type','in',[0,$log_type])->delete();
		
		$this->clearCache(['cache_tag'=>'log_type_'.$log_type]);

		return $count;
	}
}
