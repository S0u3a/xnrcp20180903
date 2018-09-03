<?php
/**
 * Controller层 会员等级
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\controller;

use app\common\controller\Base;

class UserLevel extends Base
{
    public      $apiHash        = '';
    public      $apiMethod      = '';
    protected   $apinum         = 150000;//接口编号
    
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
   
    //接口头--用户组列表
    public function lists($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功后获取的唯一标识,注意保存'],
        ['uid','Int',1,'100011',0,'用户身份ID','用户登录成功后获取的唯一ID，注意保存'],
        ['page','Int',1,'700001','1','页数','分页时的页数'],
        ['search','Json',0,'0','/','筛选条件','筛选条件必须是一个一维数组的json数组'],

		//['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
		];

        //接口调用成功需要返回的参数
        $backData   = [
        ['id','Int','ID','会员等级ID'],
        ['title','String','名称','会员等级名称'],
        ['description','String','描述','会员等级描述'],
        ['amount','String','消费额度','会员等级满足的消费额度'],
        ['discount','String','折扣率','会员等级满足的折扣率'],
        ['status','String','状态','会员等级状态：1正常，2禁用'],
        ['create_time','String','添加时间','添加时间'],
        ['update_time','String','更新时间','更新时间'],

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
		$className								= 'UserLevel';
		//定义命名空间
		$namespace								= '\app\api\helper';
		//数据传递
		$parame									= json_encode($checkData);
		//执行操作
		$this->helper($parame,$namespace,$className,$method);
		//数据返回
		return json($this->getReturnData());
    }

    //接口头--会员等级新增更新
    public function saveData($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功后获取的唯一标识,注意保存'],
        ['uid','Int',1,'100011',0,'用户身份ID','用户登录成功后获取的唯一ID，注意保存'],
        ['title','String',1,'150001','/','名称','会员等级名称'],
        ['status','Int',0,'0','/','状态','会员等级状态：1启用，2禁用'],
        ['description','String',0,'0','/','描述','分组描述'],
        ['amount','Float',0,'0','/','消费额度','会员等级满足的消费额度'],
        ['discount','Int',0,'0','/','折扣率','会员等级满足的折扣率'],
        ['id','Int',0,'0','/','ID','会员等级ID，大于0时为更新数据'],

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
        $className                              = 'UserLevel';
        //定义命名空间
        $namespace                              = '\app\api\helper';
        //数据传递
        $parame                                 = json_encode($checkData);
        //执行操作
        $this->helper($parame,$namespace,$className,$method);
        //数据返回
        return json($this->getReturnData());
    }

    //接口头--用户组详细信息
    public function details($parame=[])
    {
        //接口动态参数定义，根据业务需求决定
        $checkData  = [
        ['hashid','String',1,'100009','/','用户身份加密串','用户登录成功时获取的hashid'],
        ['uid','Int',1,'100011','/','用户身份ID','用户登录成功时获取的uid'],
        ['id','Int',1,'150004',0,'ID','需要操作的数据用户组ID'],

        //['字段标识','字段类型(Int,String,Json,Float,Bool)','是否必填','参数编号','默认值','字段名称','字段描述'],
        ];

        //接口调用成功需要返回的参数
        $backData   = [
        ['id','Int','ID','会员等级ID'],
        ['title','String','名称','会员等级名称'],
        ['description','String','描述','会员等级描述'],
        ['status','Int','状态','会员等级状态：1-启用,2-禁用'],
        ['amount','String','消费额度','会员等级满足的消费额度'],
        ['discount','String','折扣率','会员等级满足的折扣率'],

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
        $className                              = 'UserLevel';
        //定义命名空间
        $namespace                              = '\app\api\helper';
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
        ['id','Int','ID','用户分组ID'],

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
        $className                              = 'UserLevel';
        //定义命名空间
        $namespace                              = '\app\api\helper';
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
        $className                              = 'UserLevel';
        //定义命名空间
        $namespace                              = '\app\api\helper';
        //数据传递
        $parame                                 = json_encode($checkData);
        //执行操作
        $this->helper($parame,$namespace,$className,$method);
        //数据返回
        return json($this->getReturnData());
    }
}
