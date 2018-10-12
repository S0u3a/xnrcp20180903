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
 * Date: 2018-06-11
 * Description:用户提现模块
 */

namespace app\manage\controller;

use app\manage\controller\Base;

class Cash extends Base
{
    private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->apiUrl['index']        = 'Api/Bank/cashListForAdmin';
        $this->apiUrl['edit']         = 'Admin/Cash/detailData';
        $this->apiUrl['add_save']     = 'Admin/Cash/saveData';
        $this->apiUrl['edit_save']    = 'Admin/Cash/saveData';
        $this->apiUrl['quickedit']    = 'Admin/Cash/quickEditData';
        $this->apiUrl['del']          = 'Admin/Cash/delData';
    }

    //未审核
    public function status1()
    {
        $arr['status']             = 1;
        $arr['isback']             = 0;
        $arr['title1']             = '未审核提现';
        $arr['title2']             = '提现审核与管理';
        $arr['notice']             = ['点击审核对提现用户进行审核'];

        return $this->index($arr);
    }

    //已审核
    public function status2()
    {
        $arr['status']             = 3;
        $arr['isback']             = 0;
        $arr['title1']             = '已审核提现';
        $arr['title2']             = '提现审核与管理';
        $arr['notice']             = ['查看已审核用户'];
        return $this->index($arr);
    }

    //审核失败
    public function status3()
    {
        $arr['status']             = 4;
        $arr['isback']             = 0;
        $arr['title1']             = '审核失败提现';
        $arr['title2']             = '提现审核与管理';
        $arr['notice']             = ['查看审核失败用户'];
        return $this->index($arr);
    }

	//列表页面
	public function index($arr)
    {
		$menuid     = input('menuid',0) ;
        $page       = input('page',1);

        $search             = [];
        $search['status']   = $arr['status'];

        //页面操作功能菜单
        $topMenu    = formatMenuByPidAndPos($menuid,2, $this->menu);
        $rightMenu  = formatMenuByPidAndPos($menuid,3, $this->menu);

        //获取表头以及搜索数据
        $tags       = strtolower('cash/index');
        $listNode   = $this->getListNote($tags) ;

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

    public function examiney()
    {
        $ids     = request()->param();
        $ids     = (isset($ids['ids']) && !empty($ids['ids'])) ? $ids['ids'] : $this->error('请选择要操作的数据');
        $ids     = is_array($ids) ? implode($ids,',') : intval($ids);
        
        //调用代付接口

        //用户信息
        $postData                   = [];
        $postData['uid']            = $this->uid;
        $postData['hashid']         = $this->hashid;
        $postData['id']             = intval($ids);

        //请求数据
        $res       = $this->apiData($postData,'Api/Pay/substitute') ;
        $data      = $this->getApiData();

        if($res){
            $this->success('通过审核成功',Cookie('__forward__')) ;
        }else{
            $this->error($this->getApiError()) ;
        }
    }

    public function examinen()
    {
        $param                      = request()->param();
        $dbModel                    = model('bank_cash');
        $id                         = isset($param['ids']) ? intval($param['ids']) : 0;
        $cash_info                  = $dbModel->getOneByid($id);
        $cash_info                  = !empty($cash_info) ? $cash_info->toArray() : [];
        $money                      = isset($cash_info['money']) ? $cash_info['money']*1 : 0;

        if (empty($cash_info) || $money <= 0) $this->error('提现信息异常') ;

        //退还提现金额
        $userModel   = model('user_detail');
        $userinfo    = $userModel->getOneByUid($cash_info['uid']);

        if (!empty($userinfo))
        {    
            $data                   = [];
            $data['account']        = $userinfo['account']+$money;
            $data['cash_money']     = $userinfo['cash_money']+$money;
            $userModel->updateById($userinfo['id'],$data);
            $userModel->delDetailDataCacheByUid($userinfo['uid']);

            $dbModel->updateById($id,['status'=>4]);
            $this->success('驳回审核成功',Cookie('__forward__')) ;
        }else{
            $this->error('操作失败！') ;
        }
    }

    public function viewinfo()
    {
        $ids     = request()->param();
        $ids     = (isset($ids['id']) && !empty($ids['id'])) ? $ids['id'] : $this->error('请选择要操作的数据');;
        $ids     = is_array($ids) ? implode($ids,',') : intval($ids);

        $dbModel = model('bank_cash');
        $info    = $dbModel->getOneByid($ids);
        $info    = !empty($info) ? $info->toArray() : '';

        $status  = ['未知','审核中','处理中','审核完成','审核失败'];
        echo "申请编号:&nbsp;".$info['id']."<br><br>";
        echo "用户编号:&nbsp;".$info['uid']."<br><br>";
        echo "申请金额:&nbsp;".$info['money']."<br><br>";
        echo "审核状态:&nbsp;".$status[$info['status']]."<br><br>";
        echo "真实姓名:&nbsp;".$info['real_name']."<br><br>";
        echo "身份证号码:&nbsp;".$info['sfz']."<br><br>";
        echo "银行名称:&nbsp;".$info['bank_name']."<br><br>";
        echo "银行卡号:&nbsp;".$info['bank_num']."<br><br>";
        echo "开户行地址:&nbsp;".$info['bank_address']."<br><br>";
        echo "申请时间:&nbsp;".$info['create_time'];exit;
    }
}
?>