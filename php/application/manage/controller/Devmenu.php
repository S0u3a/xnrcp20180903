<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 杭州新苗科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: xnrcms<562909771@qq.com>
 * Date: 2018-02-05
 * Description:系统功能菜单管理
 */

namespace app\manage\controller;

use app\manage\controller\Base;

/**
 * 后台配置控制器
 */
class Devmenu extends Base
{
	private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->apiUrl['index']        = 'Admin/Devmenu/listData';
        $this->apiUrl['edit']         = 'Admin/Devmenu/detailData';
        $this->apiUrl['add_save']     = 'Admin/Devmenu/saveData';
        $this->apiUrl['edit_save']    = 'Admin/Devmenu/saveData';
        $this->apiUrl['quickedit']    = 'Admin/Devmenu/quickEditData';
        $this->apiUrl['del']          = 'Admin/Devmenu/delData';
    }

	/**
	 * 后台菜单首页
	 * @return none
	 */
	public function index()
	{
		//获取列表数据
		$search 			= [];
        $parame['uid']      = $this->uid;
        $parame['hashid']   = $this->hashid;
        $parame['page']     = input('page',1);
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        //请求数据
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');

        $res                = $this->apiData($parame,$this->apiUrl[request()->action()],false);
        $menuData           = $this->getApiData();
        $menuList 			= (isset($menuData['lists']) && !empty($menuData['lists'])) ? $menuData['lists'] : [];

		//页面数据
		$pageData						= [];
		$pageData['isback']     		= 0;
        $pageData['title1']     		= '开发 - 功能菜单管理 ';
        $pageData['title2']     		= '系统功能菜单添加/删除/编辑操作';
        $pageData['notice']     		= ['温馨提示：默认显展示所有菜单，点击减号收缩或点击加号展开'];

		//记录当前列表页的cookie
		Cookie('__forward__',$_SERVER['REQUEST_URI']);

		//渲染数据到页面模板上
		$assignData['pageData'] 		= $pageData;
		$Tree           				= new \xnrcms\DataTree($menuList);
		$assignData['menu'] 			= $Tree->toFormatTree();
		$this->assignData($assignData);

		//加载视图模板
		return view();
	}

	/**
	 * 新增菜单
	 */
	public function add()
	{
		//数据提交
		if (request()->isPost()) $this->update();

		//菜单列表
		$Tree           				= new \xnrcms\DataTree($this->menu);
		$menus 							= $Tree->toFormatTree();

		//数据详情
        $info                           = $this->getDetail(0);
		$info['id']						= 0;
		$info['pid']					= input('pid',0);
		$info['status']					= 1;

		//页面数据
		$pageData						= [];
		$pageData['isback']     		= 1;
        $pageData['title1']     		= '开发 - 功能菜单 ';
        $pageData['title2']     		= '功能菜单索引与管理';
        $pageData['notice']     		= ['星号项是必填项.','添加或者修改菜单时, 请注意选择对应的上级'];

		//渲染数据到页面模板上
		$assignData['pageData'] 		= $pageData;
		$assignData['menus'] 			= $menus;
		$assignData['info'] 			= $info;
		$this->assignData($assignData);

		//加载视图模板
		return view('addedit');
	}

	/**
	 * 编辑配置
	 */
	public function edit($id = 0)
	{
		//数据提交
		if (request()->isPost()) $this->update();

		//数据详情
        $info                           = $this->getDetail($id);
		if(empty($info)) $this->error('数据获取失败',Cookie('__forward__'));
		
		$Tree           				= new \xnrcms\DataTree($this->menu);
		$menus 							= $Tree->toFormatTree();
		
		$pageData						= [];
		$pageData['isback']     		= 1;
        $pageData['title1']     		= '开发 - 功能菜单 ';
        $pageData['title2']     		= '功能菜单索引与管理';
        $pageData['notice']     		= ['星号项是必填项.','添加或者修改菜单时, 请注意选择对应的上级'];

		//渲染数据到页面模板上
		$assignData['pageData'] 		= $pageData;
		$assignData['menus'] 			= $menus;
		$assignData['info'] 			= $info;
		$this->assignData($assignData);

		//加载视图模板
		return view('addedit');
	}

	//提交表单
	protected function update()
	{
		if(request()->isPost())
		{
			//表单数据
	        $postData                   = input('post.');

	        //用户信息
	        $postData['uid']            = $this->uid;
	        $postData['hashid']         = $this->hashid;

	        //表单中不允许提交至接口的参数
	        $notAllow                   = ['formId'];

	        //过滤不允许字段
	        if(!empty($notAllow)){

	            foreach ($notAllow as $key => $value) unset($postData[$value]);
	        }

	        //请求数据
	        if (!isset($this->apiUrl[request()->action().'_save']) || empty($this->apiUrl[request()->action().'_save'])) {
	            
	            $this->error('未设置接口地址');
	        }

	        $res       = $this->apiData($postData,$this->apiUrl[request()->action().'_save']) ;
	        $data      = $this->getApiData() ;

	        if($res){

	        	cache('SystemAuthMenu' . $this->hashid,null);
	            $this->success($postData['id']  > 0 ? '更新成功' : '新增成功',Cookie('__forward__')) ;
	        }else{

	            $this->error($this->getApiError()) ;
	        }
		}

		$this->error('非法提交！');
	}

	/**
	 * 删除后台菜单
	 */
	public function del()
	{
		$ids			= request()->param();
		$ids 			= (isset($ids['ids']) && !empty($ids['ids'])) ? $ids['ids'] : [];

		//请求地址
        if (!isset($this->apiUrl[request()->action()])||empty($this->apiUrl[request()->action()])) 
        	$this->error('未设置接口地址');

		if ( empty($ids) ) $this->error('请选择要操作的数据!');

		$ids 				= is_array($ids) ? implode(',',$ids) : intval($ids);

		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['id']		= $ids;

        //接口调用
        $res       = $this->apiData($parame,$this->apiUrl[request()->action()]) ;
        $data      = $this->getApiData() ;

        if($res == true){

        	cache('SystemAuthMenu' . $this->hashid,null);
            $this->success('删除成功',url('index')) ;
        }else{
            
            $this->error($this->getApiError()) ;
        }
	}

	public function quickEdit()
	{
		//请求地址
        if (!isset($this->apiUrl[request()->action()])||empty($this->apiUrl[request()->action()])) 
        	$this->error('未设置接口地址');
        
        //接口调用
        if ($this->questBaseEdit($this->apiUrl[request()->action()])){

        	cache('SystemAuthMenu' . $this->hashid,null);
        	$this->success('更新成功',Cookie('__forward__'));
        }
        
        $this->error('更新失败');
	}

	//获取数据详情
    private function getDetail($id = 0)
    {
        $info           = [];

        if ($id > 0) {
            
            //请求参数
            $parame             = [];
            $parame['uid']      = $this->uid;
            $parame['hashid']   = $this->hashid;
            $parame['id']       = $id ;

            //请求数据
            $apiUrl     = (isset($this->apiUrl[request()->action()]) && !empty($this->apiUrl[request()->action()])) ? $this->apiUrl[request()->action()] : $this->error('未设置接口地址');
            $res        = $this->apiData($parame,$apiUrl,false);
            $info       = $res ? $this->getApiData() : $this->error($this->getApiError());
        }

        return $info;
    }
}
?>