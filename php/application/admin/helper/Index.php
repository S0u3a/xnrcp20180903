<?php
/**
 * Helper层代码 接口聚合
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\admin\helper;

use app\common\helper\Base;
use think\facade\Lang;

class Index extends Base
{
	private $dataValidate 		= null;
    private $mainTable          = '';

	public function __construct($parame=[],$className='',$methodName='',$modelName='')
    {
        parent::__construct($parame,$className,$methodName,$modelName);
        $this->apidoc           = request()->param('apidoc',0);
    }
    
	//初始化接口 固定不用动
	public function apiRun($parame,$className,$methodName){
		
		//参数数据
		$parame		= !empty($parame) ? json_decode($parame,true) : '';

        //内部调用时，加载语言包
        $this->loadHelperLang($className,(isset($parame['is_inside']) && $parame['is_inside'] == 1) ? true : false);

		//加载验证器
		$this->dataValidate = new \app\api\validate\DataValidate;

		//接口执行分发
		$res = $this->$methodName($parame);

		//接口数据返回
		return is_array($res) ? $res : ["Data"=>$res];
	}

    //支持内部调用
    public function isInside($parame,$aName)
    {
        return $this->$aName($parame);
    }

    private function index($parame)
    {
        //定义可以被访问的模块
    	$allowModel 	= ['api','admin'];

        //获取多接口数据，并解析
    	$apiData      	= request()->param('apiData');
        $apiData        = json_decode($apiData,true);

        $api 			= [];

        if (!empty($apiData)) {
            
            foreach ($apiData as $key => $value) {

            	if (!isset($value['apiParame']) || empty($value['apiParame'])) {
            		
            		return ['Code' => '300006', 'Msg'=>lang('300006',[$value['apiName']])];
            	}

            	//接口参数
            	$apiParame  = $value['apiParame'];

            	//解析接口名称，接口名称格式必须是 模块_控制器_方法 的格式
    			$info 		= explode('/',$value['apiName']);

    			//获取 模块名称，控制器名称，方法名称
				$mName 	= strtolower($info[0]);
				$cName 	= $info[1];
				$aName 	= $info[2];

                //判断接口模块是否在允许模块中，没有直接禁止请求
				if (!in_array(strtolower($mName),$allowModel) ) {

					return ['Code' => '300004', 'Msg'=>lang('300004')];
				}

				//检查控制器是否分存在，不存在禁止请求
				$file       = \Env::get('APP_PATH') . $mName . '/controller/'. $cName .'.php';

				if (!file_exists($file)) {

					return ['Code' => '300005', 'Msg'=>lang('300005',[$value['apiName']])];
				}

				 //定义对应的命名空间以及执行的方法
                $namespace 								= '\app\\'.$mName.'\controller\\' . $cName;
                $api[md5($namespace.$aName)]			= ['namespace'=>$namespace,'method'=>$aName,'apiName'=>$value['apiName'],'data'=>$apiParame];
            }

        }else{

        	return ['Code' => '300003', 'Msg'=>lang('300003'),'Data'=>$apiData];
        }

        if (empty($api)) {

			return ['Code' => '300007', 'Msg'=>lang('300007'),'Data'=>$api];
        }

        $backData  = [];

        //开始执行接口方法
		foreach ($api as $k => $v) {

			$namespace 				= $v['namespace'];
			$method 				= $v['method'];
            $apiName                = $v['apiName'];

			//设置接口参数
            $baseParame             = 
			$parameData 			= array_merge($v['data'],['time'=>$parame['time'],'apiId'=>$parame['apiId'],'terminal'=>$parame['terminal']]);
			
			$apiObject 	                = new $namespace;
			$apiObject->apiMethod	    = $method;

            //获取接口数据
			$res                        = $apiObject->$method($parameData);

            //收集接口数据
            $data                       = $res->getData();

            $data['ApiUrl']             = url($apiName);
            
            $backData[$apiName]    		= $data;
		}

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$backData];
    }
}
