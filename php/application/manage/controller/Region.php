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
 * Author: xnrcms<56290971@qq.com>
 * Date: 2018-04-11
 * Description:区域地址模块
 */

namespace app\manage\controller;

use app\manage\controller\Base;

class Region extends Base
{
    private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->apiUrl['index']        = 'Admin/Region/listData';
        $this->apiUrl['edit']         = 'Admin/Region/detailData';
    }

	//列表页面
	public function index()
    {
		$menuid     = 23 ;
		$search 	= input('search','');
        $page       = input('page',1);
        $pid        = input('pid',0);

        //页面操作功能菜单
        $topMenu    = formatMenuByPidAndPos($menuid,2, $this->menu);
        $rightMenu  = formatMenuByPidAndPos($menuid,3, $this->menu);

        //获取表头以及搜索数据
        $tags       = strtolower(request()->controller() . '/' . request()->action());
        $listNode   = $this->getListNote($tags) ;

        //获取列表数据
        $parame             = [];
        $parame['uid']      = $this->uid;
        $parame['hashid']   = $this->hashid;
        $parame['page']     = $page;
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;
        $parame['pid']      = $pid;

        //请求数据
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');

        $res                = $this->apiData($parame,$this->apiUrl[request()->action()]);
        $data               = $this->getApiData() ;

        if ($pid > 0) {
            $parame             = [];
            $parame['uid']      = $this->uid;
            $parame['hashid']   = $this->hashid;
            $parame['id']       = $pid;
            $res                = $this->apiData($parame,$this->apiUrl['edit']);
            if (empty($this->getApiError())) {
              
              $info            = $this->getApiData();
              $pid             = $info['pid'];
            }
        }

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

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '地区设置';
        $pageData['title2']             = '可对系统内置的地区进行编辑';
        $pageData['notice']             = [
            '全站所有涉及的地区均来源于此处，强烈建议对此处谨慎操作。',
            '编辑地区信息后，需手动更新地区缓存(平台  > 设置 > 清理缓存 > 地区)，前台才会生效。',
            '所属大区为默认的全国性的几大区域，只有省级地区才需要填写大区域，目前全国几大区域有：华北、东北、华东、华南、华中、西南、西北、港澳台、海外','所在层级为该地区的所在的层级深度，如北京>北京市>朝阳区,其中北京层级为1，北京市层级为2，朝阳区层级为3',];

        $this->extends_param .= 'pid='.$pid.'&';
        
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
        return view();
	}

	//新增页面
	public function add()
    {
		//数据提交
        if (request()->isPost()) $this->update();

        //表单模板
        $tags                           = strtolower(request()->controller() . '/addedit');
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
        $tags                           = strtolower(request()->controller() . '/addedit');
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
            $apiUrl     = (isset($this->apiUrl[request()->action()]) && !empty($this->apiUrl[request()->action()])) ? $this->apiUrl[request()->action()] : $this->error('未设置接口地址');
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

    /**
    * @Author :田立龙<longvip199405@163.com>
    * @date：2018/1/9 13:26
    * @description：地址联动
    */
    public function change($pid)
    {   
        $postData        = input('post.');
        $pid             = (isset($postData['pid']) && $postData['pid'] > 0) ? $postData['pid'] : 0;

        //获取列表数据
        $parame             = [];
        $parame['uid']      = $this->uid;
        $parame['hashid']   = $this->hashid;
        $parame['page']     = 1;
        $parame['search']   = '' ;
        $parame['pid']      = $pid;

        //请求数据
        $apiUrl             = isset($this->apiUrl['index']) ? $this->apiUrl['index'] : $this->error('未设置接口地址');
        $res                = $this->apiData($parame,$apiUrl,false);
        $data               = $this->getApiData() ;

        return json($data['lists']);
    }
}
?>