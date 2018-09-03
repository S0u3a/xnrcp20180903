<?php
/**
 * Controller层 系统插件
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\admin\controller;

use app\common\controller\Base;

class Plugin extends Base
{
    public      $apiHash        = '';
    public      $apiMethod      = '';
    protected   $apinum         = 180000;//接口编号
    
	//接口默认不提供index访问
    public function index()
    {
    	return json($this->getReturnData());
    }

    /*
     接口头请求参数定义格式:
     ['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述']

     接口头返回参数定义格式:
     ['字段标识','字段类型(Int,String,Json,Float,Bool)','字段名称']
    */
   
    //接口头--插件列表
    public function lists($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功后获取的唯一标识,注意保存'],
        ['uid','Int',1,'100011',0,'用户身份ID','用户登录成功后获取的唯一ID，注意保存'],
        ['page','Int',0,'0','1','页数','分页时的页数'],
        ['search','Json',0,'0','/','筛选条件','筛选条件必须是一个一维数组的json数组'],

		//['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
		];

        //接口调用成功需要返回的参数
        $backData   = [
        ['id','Int','ID','插件ID'],
        ['title','String','名称','插件名称'],
        ['description','String','描述','插件描述'],
        ['icon','String','地址','插件LOGO地址'],
        ['code','String','标识','插件标识'],
        ['ptype','String','类型','插件类型'],
        ['status','String','状态','插件状态：1启用，2禁用'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','字段名称','字段描述'],
        ];

        //接口文档数据
        $this->apidoc($checkData,$backData);
        //设置接口校验数据
        $this->setCheckData($checkData);
        //设置接口返回数据
        $this->setBackData($backData);

        //用户提交的数据
        $postData                               = !empty($parame) ? $parame : request()->param();

    	//数据校验
    	$checkData 								= $this->checkData($postData);
    	if (!$checkData) {

    		return json($this->getReturnData());
    	}

    	//执行方法名 默认当前action
    	$method									= request()->param('isManyApi') == 1 ? $this->apiMethod : request()->action();
    	
		//定义类名
		$className								= 'Plugin';
		//定义命名空间
		$namespace								= '\app\admin\helper';
		//数据传递
		$parame									= json_encode($checkData);
		//执行操作
		$this->helper($parame,$namespace,$className,$method);
		//数据返回
		return json($this->getReturnData());
    }

    //接口头--插件新增更新
    public function saveData($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功后获取的唯一标识,注意保存'],
        ['uid','Int',1,'100011',0,'用户身份ID','用户登录成功后获取的唯一ID，注意保存'],
        ['title','String',1,'180001','/','名称','插件名称'],
        ['ptype','String',1,'180002','/','类型','插件类型'],
        ['code','String',1,'180003','/','标识','插件标识'],
        ['description','String',0,'0','/','描述','插件描述'],
        ['status','Int',0,'0','/','状态','插件状态：1启用，2禁用'],
        ['id','Int',0,'0','/','ID','插件ID，大于0时为更新数据'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
        ];

        //接口调用成功需要返回的参数
        $backData   = [
        ['id','Int','数据ID','新增或者更新的数据ID'],
        
        //['字段标识','字段类型(Int,String,Json,Float,Bool)','字段名称','字段描述'],
        ];

        //接口文档数据
        $this->apidoc($checkData,$backData);
        //设置接口校验数据
        $this->setCheckData($checkData);
        //设置接口返回数据
        $this->setBackData($backData);

        //用户提交的数据
        $postData                               = !empty($parame) ? $parame : request()->param();

        //数据校验
        $checkData                              = $this->checkData($postData);
        if (!$checkData) {
            return json($this->getReturnData());
        }

        //执行方法名 默认当前action
        $method                                 = request()->param('isManyApi') == 1 ? $this->apiMethod : request()->action();
        
        //定义类名
        $className                              = 'Plugin';
        //定义命名空间
        $namespace                              = '\app\admin\helper';
        //数据传递
        $parame                                 = json_encode($checkData);
        //执行操作
        $this->helper($parame,$namespace,$className,$method);
        //数据返回
        return json($this->getReturnData());
    }

    //接口头--插件详细信息
    public function details($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功时获取的hashid'],
        ['uid','Int',1,'100011','/','用户身份ID','用户登录成功时获取的uid'],
        ['id','Int',1,'700004',0,'ID','需要操作的数据广告位ID'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
        ];

        //接口调用成功需要返回的参数
        $backData   = [
        ['id','Int','ID','插件ID'],
        ['title','String','名称','插件名称'],
        ['description','String','描述','插件描述'],
        ['code','String','标识','插件标识'],
        ['ptype','String','类型','插件类型'],
        ['status','Int','状态','插件状态：1正常，2禁用'],
        ['config_value','String','配置项','配置项']

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','字段名称','字段描述'],
        ];

        //接口文档数据
        $this->apidoc($checkData,$backData);
        //设置接口校验数据
        $this->setCheckData($checkData);
        //设置接口返回数据
        $this->setBackData($backData);

        //用户提交的数据
        $postData                               = !empty($parame) ? $parame : request()->param();

        //数据校验
        $checkData                              = $this->checkData($postData);
        if (!$checkData) {
            return json($this->getReturnData());
        }

        //执行方法名 默认当前action
        $method                                 = request()->param('isManyApi') == 1 ? $this->apiMethod : request()->action();
        
        //定义类名
        $className                              = 'Plugin';
        //定义命名空间
        $namespace                              = '\app\admin\helper';
        //数据传递
        $parame                                 = json_encode($checkData);
        //执行操作
        $this->helper($parame,$namespace,$className,$method);
        //数据返回
        return json($this->getReturnData());
    }

    //字段快捷更新接口
    public function quickEdit($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功时获取的hashid'],
        ['uid','Int',1,'100011','/','用户身份ID','用户登录成功时获取的uid'],
        ['id','Int',1,'700004',0,'ID','需要操作的数据用户组ID'],
        ['fieldName','String',1,'100013','/','字段','需要更新的菜单字段'],
        ['updata','String',1,'100014','/','数据','需要更新的数据值'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
        ];

        //接口调用成功需要返回的参数
        $backData   = [
        ['id','Int','ID','插件ID'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','字段名称','字段描述'],
        ];

        //接口文档数据
        $this->apidoc($checkData,$backData);
        //设置接口校验数据
        $this->setCheckData($checkData);
        //设置接口返回数据
        $this->setBackData($backData);

        //用户提交的数据
        $postData                               = !empty($parame) ? $parame : request()->param();

        //数据校验
        $checkData                              = $this->checkData($postData);
        if (!$checkData) {
            return json($this->getReturnData());
        }

        //执行方法名 默认当前action
        $method                                 = request()->param('isManyApi') == 1 ? $this->apiMethod : request()->action();
        
        //定义类名
        $className                              = 'Plugin';
        //定义命名空间
        $namespace                              = '\app\admin\helper';
        //数据传递
        $parame                                 = json_encode($checkData);
        //执行操作
        $this->helper($parame,$namespace,$className,$method);
        //数据返回
        return json($this->getReturnData());
    }

    //数据删除接口
    public function delData($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功时获取的hashid'],
        ['uid','Int',1,'100011','/','用户身份ID','用户登录成功时获取的uid'],
        ['id','String',1,'700004',0,'ID','需要操作的数据用户组ID,批量删除时用英文逗号分隔'],
        
        //['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
        ];

        //接口调用成功需要返回的参数
        $backData   = [
        ['count','Int','删除个数','成功删除菜单的个数'],
        
        //['字段标识','字段类型(Int,String,Json,Float,Bool)','字段名称','字段描述'],
        ];

        //接口文档数据
        $this->apidoc($checkData,$backData);
        //设置接口校验数据
        $this->setCheckData($checkData);
        //设置接口返回数据
        $this->setBackData($backData);

        //用户提交的数据
        $postData                               = !empty($parame) ? $parame : request()->param();

        //数据校验
        $checkData                              = $this->checkData($postData);
        if (!$checkData) {
            return json($this->getReturnData());
        }

        //执行方法名 默认当前action
        $method                                 = request()->param('isManyApi') == 1 ? $this->apiMethod : request()->action();
        
        //定义类名
        $className                              = 'Plugin';
        //定义命名空间
        $namespace                              = '\app\admin\helper';
        //数据传递
        $parame                                 = json_encode($checkData);
        //执行操作
        $this->helper($parame,$namespace,$className,$method);
        //数据返回
        return json($this->getReturnData());
    }
}
