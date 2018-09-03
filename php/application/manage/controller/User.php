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
 * Author: xnrcms<562909771@qq.com><562909771@qq.com>
 * Date: 2018-04-09
 * Description:用户控制器
 */

namespace app\manage\controller;

use app\manage\controller\Base;

class User extends Base
{
    private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->apiUrl['index']                      = 'Api/User/listData';
        $this->apiUrl['edit']                       = 'Api/User/userDetail';
        $this->apiUrl['add_save']                   = 'Api/User/saveData';
        $this->apiUrl['edit_save']                  = 'Api/User/saveData';
        $this->apiUrl['quickedit']                  = 'Api/User/quickEditData';
        $this->apiUrl['del']                        = 'Api/User/delData';
        $this->apiUrl['quickEditUserDetailData']    = 'Api/User/quickEditUserDetailData';
    }

    //管理员
    public function adminuser()
    {
        $arr['listid']             = 'user/index';
        $arr['gid']                = 1;
        $arr['isback']             = 0;
        $arr['title1']             = '用户-管理员管理';
        $arr['title2']             = '网站系统用户-管理员索引与管理';
        $arr['notice']             = ['用户-管理员列表管理, 对系统管理员进行维护.'];

        return $this->index($arr);
    }

    //会员列表
    public function homeuser()
    {
        $arr['listid']             = 'user/index';
        $arr['gid']                = 2;
        $arr['isback']             = 0;
        $arr['title1']             = '用户-会员管理';
        $arr['title2']             = '网站系统用户-会员索引与管理';
        $arr['notice']             = ['用户-会员列表管理, 对系统会员进行维护.'];

        return $this->index($arr);
    }

    //代理列表
    public function agentuser()
    {
        $arr['listid']             = 'user/index';
        $arr['gid']                = 3;
        $arr['isback']             = 0;
        $arr['title1']             = '用户-代理管理';
        $arr['title2']             = '网站系统用户-代理索引与管理';
        $arr['notice']             = ['用户-代理列表管理, 对系统代理进行维护.'];

        return $this->index($arr);
    }

	//列表页面
	private function index($arr)
    {
		$menuid     = input('menuid',0) ;
		$search 	= input('search',[]);
        $page       = input('page',1);

        //页面操作功能菜单
        $topMenu    = formatMenuByPidAndPos($menuid,2, $this->menu);
        $rightMenu  = formatMenuByPidAndPos($menuid,3, $this->menu);

        //获取表头以及搜索数据
        $listNode   = $this->getListNote($arr['listid']) ;

        $search['group_id'] = $arr['gid'];

        //获取列表数据
        $parame['uid']      = $this->uid;
        $parame['hashid']   = $this->hashid;
        $parame['page']     = $page;
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        //请求数据
        if (!isset($this->apiUrl['index']) || empty($this->apiUrl['index']))
        $this->error('未设置接口地址');

        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $data               = $this->getApiData() ;

        $total 				= 0;
        $p 					= '';
        $listData 			= [];

        if ($res){

            //分页信息
            $page           = new \xnrcms\Page($data['total'], $data['limit']);
            if($data['total']>=1){

                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                $page->setConfig('header','');
            }

            $p 				= trim($page->show());
            $total 			= $data['total'];
            $listData   	= $data['lists'];
        }

        //缓存数据
        cache('user_list_for_gid',$arr['gid']);

        //页面头信息设置
        $pageData['isback']             = $arr['isback'];
        $pageData['title1']             = $arr['title1'];
        $pageData['title2']             = $arr['title2'];
        $pageData['notice']             = $arr['notice'];

        //渲染数据到页面模板上
        $assignData['_page']            = $p;
        $assignData['_total']           = $total;
        $assignData['topMenu']          = $topMenu;
        $assignData['rightMenu']        = $rightMenu;
        $assignData['listId']           = isset($listNode['info']['id']) ? intval($listNode['info']['id']) : 0;
        $assignData['listNode']         = $listNode;
        $assignData['listData']         = $listData;
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        //异步请求处理
        if(request()->isAjax()){

            echo json_encode(['listData'=>$this->fetch('public/list/listData'),'listPage'=>$p]);exit();
        }

        //加载视图模板
        return view('index');
	}

	//新增页面
	public function add()
    {
		//数据提交
        if (request()->isPost()) $this->update();

        //表单模板
        $tags                           = strtolower(request()->controller() . '/' . request()->action());
        $formData                       = $this->getFormFields($tags,0) ;

        //数据详情
        $info                           = $this->getDetail(0);

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '';
        $pageData['title2']             = '';
        $pageData['notice']             = [];
        
        //记录当前列表页的cookie
        cookie('__forward__',$_SERVER['REQUEST_URI']);

        //渲染数据到页面模板上
        $assignData['formId']           = isset($formData['info']['id']) ? intval($formData['info']['id']) : 0;
        $assignData['formFieldList']    = $formData['list'];
        $assignData['info']             = $info;
        $assignData['defaultData']      = $this->getDefaultParameData();
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //加载视图模板
        return view('addedit');
	}

	//编辑页面
	public function edit($id = 0)
    {
		//数据提交
        if (request()->isPost()) $this->update();

		//表单模板
        $tags                           = strtolower(request()->controller() . '/' . request()->action());
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = $this->getDetail($id);

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '';
        $pageData['title2']             = '';
        $pageData['notice']             = [];
        
        //记录当前列表页的cookie
        cookie('__forward__',$_SERVER['REQUEST_URI']);

        //渲染数据到页面模板上
        $assignData['formId']           = isset($formData['info']['id']) ? intval($formData['info']['id']) : 0;
        $assignData['formFieldList']    = $formData['list'];
        $assignData['info']             = $info;
        $assignData['defaultData']      = $this->getDefaultParameData();
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //加载视图模板
        return view('addedit');
	}

    //数据删除
    public function del()
    {
        $ids     = request()->param();
        $ids     = (isset($ids['ids']) && !empty($ids['ids'])) ? $ids['ids'] : $this->error('请选择要操作的数据');;
        $ids     = is_array($ids) ? implode($ids,',') : '';

        //请求参数
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['id']           = $ids ;

        //请求地址
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');

        //接口调用
        $res       = $this->apiData($parame,$this->apiUrl[request()->action()]);
        $data      = $this->getApiData() ;

        if($res == true){

            $this->success('删除成功',Cookie('__forward__'));
        }else{
            
            $this->error($this->getApiError());
        }
    }

    //快捷编辑
	public function quickEdit()
    {
        //请求地址
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');
        
        //接口调用
        if ($this->questBaseEdit($this->apiUrl[request()->action()])) $this->success('更新成功');
        
        $this->error('更新失败');
    }

    //处理提交新增或编辑的数据
    private function update()
    {
        $formid                     = intval(input('formId'));
        $formInfo                   = cache('DevformDetails'.$formid);
        if(empty($formInfo)) $this->error('表单模板数据不存在');

        //表单数据
        $postData                   = input('post.');

        //用户信息
        $postData['uid']            = $this->uid;
        $postData['hashid']         = $this->hashid;

        $gid                        = cache('user_list_for_gid');
        $postData['gid']            = !empty($gid) ? intval($gid) : 0;

        //表单中不允许提交至接口的参数
        $notAllow                   = ['formId'];

        //过滤不允许字段
        if(!empty($notAllow)){

            foreach ($notAllow as $key => $value) unset($postData[$value]);
        }
        
        //请求数据
        if (!isset($this->apiUrl[request()->action().'_save'])||empty($this->apiUrl[request()->action().'_save'])) 
        $this->error('未设置接口地址');

        $res       = $this->apiData($postData,$this->apiUrl[request()->action().'_save']) ;
        $data      = $this->getApiData() ;

        if($res){

            $this->success($postData['id']  > 0 ? '更新成功' : '新增成功',Cookie('__forward__')) ;
        }else{

            $this->error($this->getApiError()) ;
        }
    }
    
    //获取数据详情
    private function getDetail($id = 0)
    {
        $info           = [];

        if ($id > 0)
        {
            //请求参数
            $parame             = [];
            $parame['uid']      = $this->uid;
            $parame['hashid']   = $this->hashid;
            $parame['id']       = $id ;

            //请求数据
            $apiUrl     = (isset($this->apiUrl['edit']) && !empty($this->apiUrl['edit'])) ? $this->apiUrl['edit'] : $this->error('未设置接口地址');
            $res        = $this->apiData($parame,$apiUrl,false);
            $info       = $res ? $this->getApiData() : $this->error($this->getApiError());
        }

        return $info;
    }

    //扩展枚举，布尔，单选，复选等数据选项
    protected function getDefaultParameData()
    {
        $defaultData['parame']   = [];

        return $defaultData;
    }

    //用户授权
    public function auth(){

        if(request()->isPost())
        {
            $postData       = input('post.');
            $detail_id      = isset($postData['detail_id']) ? intval($postData['detail_id']) : 0;
            $value          = isset($postData['rules']) ? $postData['rules'] : [];
            if ($detail_id <= 0) $this->error('更新失败！');

            $parame                 = [];
            $parame['uid']          = $this->uid;
            $parame['hashid']       = $this->hashid;
            $parame['id']           = $detail_id;
            $parame['fieldName']    = 'rules';
            $parame['updata']       = !empty($value) ? implode(',',$value) : '-1';

            $res                    = $this->apiData($parame,$this->apiUrl['quickEditUserDetailData']);

            $res ? $this->success('授权成功',Cookie('__forward__')) : $this->error($this->getApiError());
        }

        //请求地址
        if (!isset($this->apiUrl['edit']) || empty($this->apiUrl['edit'])) $this->error('未设置接口地址');

        //请求参数
        $parame['uid']      = input('id');
        $parame['hashid']   = input('hashid');

        //接口调用
        $res                = $this->apiData($parame,$this->apiUrl['edit']) ;
        $info               = $res ? $this->getApiData() : $res;

        $userAuth           = empty($info['urules']) ? array() : explode(',',$info['urules']) ;

        /**获取所有的菜单权限*/
        $Tree                           = new \xnrcms\DataTree($this->menu);
        $menuList                       = $Tree->arrayTree();

        $authList           =[];

        if (!empty($menuList)) {
            
            foreach ($menuList as $key => $value) {
                
                if (($key+3)%3 == 0) {
                    $authList['left'][]     = $value;
                }

                if (($key+3)%3 == 1) {
                    $authList['middle'][]   = $value;
                }

                if (($key+3)%3 == 2) {
                    $authList['right'][]    = $value;
                }
            }
        }

        //页面头信息设置
        $pageData['isback']     = 1;
        $pageData['title1']     = '权限';
        $pageData['title2']     = '用户专有权限设置';
        $pageData['notice']     = ['请勾选对应的操作节点',];

        //渲染数据到页面模板上
        $assignData['authList']         = $authList;
        $assignData['userAuth']         = $userAuth;
        $assignData['info']             = $info;
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //记录当前列表页的cookie
        cookie('__forward__',$_SERVER['REQUEST_URI']);

        //加载视图模板
        return view();
    }

    public function setlottery($id)
    {
        //数据提交
        if (request()->isPost()){
            $data               = request()->param();
            $updata                 = [];
            $updata['lottery_id']   = $data['lottery_id'];
            model('user_detail')->updateDetailByUid($data['id'],$updata);
            $this->success('设置成功',Cookie('__forward__')) ;
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/' . request()->action());
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('user_detail')->getOneByUid($id);

        $info['id']                     = $info['uid'];
        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '';
        $pageData['title2']             = '';
        $pageData['notice']             = [];
        
        //记录当前列表页的cookie
        cookie('__forward__',$_SERVER['REQUEST_URI']);

        //渲染数据到页面模板上
        $assignData['formId']           = isset($formData['info']['id']) ? intval($formData['info']['id']) : 0;
        $assignData['formFieldList']    = $formData['list'];
        $assignData['info']             = $info;
        $assignData['defaultData']      = $this->getDefaultParameData();
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //加载视图模板
        return view('addedit');
    }
}
?>