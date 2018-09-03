<?php
/**
 * Helper层代码 用户组
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\helper;

use app\common\helper\Base;
use think\facade\Lang;

class UserLevel extends Base
{
	private $dataValidate 		= null;

	public function __construct($parame=[],$className='',$methodName='',$modelName='')
    {
        parent::__construct($parame,$className,$methodName,$modelName);
        $this->apidoc           = request()->param('apidoc',0);
    }
    
    /**
     * 初始化接口 固定不用动
     * @param  [array]  $parame     接口需要的参数
     * @param  [string] $className  类名
     * @param  [string] $methodName 方法名
     * @return [array]              接口输出数据
     */
    public function apiRun()
    {   
        if (!$this->checkData($this->postData)) return json($this->getReturnData());
        //加载验证器
        $this->dataValidate = new \app\api\validate\DataValidate;
        
        //规避没有设置主表名称
        if (empty($this->mainTable)) return $this->returnData(['Code' => '120020', 'Msg'=>lang('120020')]);
        
        //接口执行分发
        $methodName     = $this->actionName;
        $data           = $this->$methodName($this->postData);
        //设置返回数据
        $this->setReturnData($data);
        //接口数据返回
        return json($this->getReturnData());
    }

    //支持内部调用
    public function isInside($parame,$aName)
    {
        return $this->$aName($parame);
    }
    
	/**
	 * 用户组列表
	 * @param  [array] $parame [接口参数]
	 * @return [array]         [接口数据]
	 */
    private function lists($parame) {

		$dbModel					= model('UserLevel');

		//定义数据模型参数

		//主表名称，可以为空，默认当前模型名称
		$modelParame['MainTab']		= 'user_level';

		//主表名称，可以为空，默认为main
		$modelParame['MainAlias']	= 'main';

		//主表待查询字段，可以为空，默认全字段
		$modelParame['MainField']	= [];

		//定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
		//Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
		$RelationTab				= [];
		//$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

		$modelParame['RelationTab']	= $RelationTab;

        //接口数据
        $modelParame['apiParame']   = $parame;

		//检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
		$modelParame['whereFun']	= 'formatWhereDefault';

		//排序定义
		$modelParame['order']		= 'main.id desc';		
		
		//数据分页步长定义
		$modelParame['limit']		= $this->apidoc == 2 ? 1 : 15;

		//数据分页页数定义
		$modelParame['page']		= (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

		//数据缓存是时间，默认0 不缓存 ,单位秒
		$modelParame['cacheTime']	= 0;

		//列表数据
		$lists 						= $dbModel->getPageList($modelParame);

        //数据格式化
        $data                       = (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];

        if (!empty($data)) {
            
            $status                 =['未知','启用','禁用'];

            foreach($data as $k=>$v){

                foreach($v as $kk=>$vv){
                    switch ($kk) {
                        case 'update_time': $data[$k][$kk]      = !empty($vv) ? date('Y-m-d H:i:s',$vv) : '/';break;
                        case 'create_time': $data[$k][$kk]      = !empty($vv) ? date('Y-m-d H:i:s',$vv) : '/';break;
                        case 'status':$data[$k][$kk]            = $status[$vv];break;
                        default:break;
                    }
                }
            }
        }

        $lists['lists']             = $data;

    	return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /**
     * 用户组新增/更新
     * @param  [array] $parame [接口参数]
     * @return [array]         [接口返回参数]
     */
    private function saveData($parame){

    	$dbModel					= model('UserLevel');

        $saveData['title']          = isset($parame['title']) ? $parame['title'] : '';
        $saveData['description']    = isset($parame['description']) ?  $parame['description'] : '';
        $saveData['status']         = isset($parame['status']) ? $parame['status'] : 0;
        $saveData['amount']         = isset($parame['amount']) ? $parame['amount'] : 0;
        $saveData['discount']       = isset($parame['discount']) ? $parame['discount'] : 0;
        $saveData['update_time']    = time();

        $id                         = intval($parame['id']);

    	//检测分组名称是否存在
    	if ($dbModel->checkValue($saveData['title'],$id,'title')) {

			return ['Code' => '150002', 'Msg'=>lang('150002')];
		}
		
        if ($saveData['discount'] < 0 || $saveData['discount'] > 100) {
            
            return ['Code' => '150003', 'Msg'=>lang('150003')];
        }

		$saveData['id']								= $id;

    	if ($id <= 0) {

    		//新增
    		$saveData['create_time']				= time();
            $saveData['status']                     = 1;

    		$info 									= $dbModel->addData($saveData);

    	}else{

    		//编辑
            $saveData['status']                     = $saveData['status'] == 1 ? 1 : 2;
    		$saveData['update_time']				= time();

    		$info 									= $dbModel->updateById($saveData['id'],$saveData);
    	}

    	if (!empty($info)) {

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * [details 用户组详细信息]
     * @param  [array] $parame [接口参数]
     * @return [array]         [接口返回的参数]
     */
    private function details($parame){

    	$dbModel			= model('UserLevel');

    	$info 				= $dbModel->getOneById($parame['id']);

    	if (!empty($info)) {
    		
    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * [quickEdit 用户组快捷编辑]
     * @param  [array] $parame [接口参数]
     * @return [array]         [接口返回的参数]
     */
    private function quickEdit($parame){

    	$dbModel			= model('UserLevel');

    	$info 				= $dbModel->updateById($parame['id'],[$parame['fieldName']=>$parame['updata']]);

    	if (!empty($info)) {

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['id'=>$parame['id']]];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * [delData 用户分组删除]
     * @param  [array] $parame [接口参数]
     * @return [array]         [接口返回的参数]
     */
    private function delData($parame){

    	$dbModel				= model('UserLevel');

    	$delCount				= $dbModel->delData($parame['id']);

    	return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['count'=>$delCount]];
    }
}
