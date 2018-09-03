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
 * Author: 小能人
 * Date: 2018-05-22
 * Description:彩票规则
 */

namespace app\manage\controller;

use app\manage\controller\Base;

class Lotteryrule extends Base
{
    private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->apiUrl['index']        = 'Api/LotteryRule/listData';
        $this->apiUrl['edit']         = 'Api/LotteryRule/detailData';
        $this->apiUrl['add_save']     = 'Api/LotteryRule/saveData';
        $this->apiUrl['edit_save']    = 'Api/LotteryRule/saveData';
        $this->apiUrl['quickedit']    = 'Api/LotteryRule/quickEditData';
        $this->apiUrl['del']          = 'Api/LotteryRule/delData';
    }

	//列表页面
	public function index()
    {
		$menuid     = input('menuid',0) ;
		$search 	= input('search','');
        $page       = input('page',1);

        //页面操作功能菜单
        $topMenu    = formatMenuByPidAndPos($menuid,2, $this->menu);
        $rightMenu  = formatMenuByPidAndPos($menuid,3, $this->menu);

        //获取表头以及搜索数据
        $tags       = strtolower(request()->controller() . '/' . request()->action());
        $listNode   = $this->getListNote($tags) ;

        if (in_array(3,$this->groupId)) {
            $search['lottery_id']   = !empty($this->userInfo['lottery_id']) ? $this->userInfo['lottery_id'] : '1000';
        }else{
            $search['lottery_id']   = '88,96,99,102,108,115';
        }
        
        //获取列表数据
        $parame['uid']      = $this->uid;
        $parame['hashid']   = $this->hashid;
        $parame['page']     = $page;
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        //请求数据
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');

        $res                = $this->apiData($parame,$this->apiUrl[request()->action()]);
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

            foreach ( $listData as $key => $value) {
                $desc    = $value['description'];
                $desc    = !empty($desc) ? explode('|',$desc) : [];
                $strs    = '';

                if (!empty($desc)) {
                    foreach ($desc as $k => $v) {
                        $strs   .= "<p style='width:500px;'>".($k+1).':'.$v."<p/>";
                    }
                }

                cache("Lotteryrule_views_".$value['id'],$strs);
            }
        }

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '彩票规则列表';
        $pageData['title2']             = '彩票规则索引与管理';
        $pageData['notice']             = ['彩票规则只是展示部分字段信息，详情请点击编辑查看.'];

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
        $ids     = (isset($ids['ids']) && !empty($ids['ids'])) ? $ids['ids'] : $this->error('请选择要操作的数据');
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
    private function getDetail($id = 0,$tag='')
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
            $tag        = !empty($tag) ? $tag : request()->action();
            $apiUrl     = (isset($this->apiUrl[$tag]) && !empty($this->apiUrl[$tag])) ? $this->apiUrl[$tag] : $this->error('未设置接口地址');
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

    public function setodds($id)
    {
        //数据提交
        if (request()->isPost())
        {
            $parame     = request()->param();
            $info       = cache('setodds_info');
            if (empty($info))  $this->error("彩票规则信息不存在");

            $odds               = $info['odds'];
            $odds2              = $info['odds2'];
            $difference_value   = $info['difference_value'];
            $agent_odds         = $parame['agent_odds'];
            $agent_odds2        = !empty($parame['agent_odds2']) ? $parame['agent_odds2'] : '';

            if ($agent_odds > 0 && ($agent_odds > $odds || $agent_odds < $odds-$difference_value))
            $this->error("赔率设置超出平台设置范围");

            if (!empty($agent_odds2) && $agent_odds2 > 0) {
                $odds2              = !empty($odds2) ? explode(',',$odds2) : [];
                $agent_odds2        = !empty($agent_odds2) ? explode(',',$agent_odds2) : [];
                if (empty($odds2) && !empty($agent_odds2))
                $this->error("该规则平台未设置详细赔率");

                $n1                 = count($odds2);
                $n2                 = count($agent_odds2);
                if ($n1 !== $n2)
                $this->error("详细赔率与平台设置数量不一致");

                /*foreach ($agent_odds2 as $key => $value) {
                    $v1     = !empty($value) ? explode('-',$value) : [];
                    $v2     = !empty($odds2[$key]) ? explode('-',$odds2[$key]) : [];

                    if (count($v1) !== 2)  $this->error("代理详细赔率格式错误");
                    if (count($v2) !== 2)  $this->error("平台详细赔率格式错误");

                    if ($v1[0] > $v2[0]||$v1[0]<$v2[0]-$difference_value||$v1[1]>$v2[1]||$v1[1]<$v2[1]-$difference_value) {
                        $this->error("详细赔率设置超出平台设置范围1");
                    }
                }*/
            }

            $updata                     = [];
            $updata['odds1']            = $parame['agent_odds'];
            $updata['odds2']            = $parame['agent_odds2'];
            $updata['uid']              = $this->uid;

            $model                      = model('lottery_odds');
            $model->saveLotteryOdds($info['tag'],$updata);

            $this->success('设置成功',Cookie('__forward__')) ;
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/' . request()->action());
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = $this->getDetail($id,'edit');
        $info2                          = model('lottery_odds')->getLotteryOddsByTag($info['tag']);
        
        $info['agent_odds']             = (isset($info2['odds1']) && !empty($info2['odds1'])) ? $info2['odds1'] : $info['agent_odds'];
        $info['agent_odds2']            = (isset($info2['odds2']) && !empty($info2['odds2'])) ? $info2['odds2'] : $info['agent_odds2'];

        cache('setodds_info',$info);

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

    public function views($id)
    {
        echo cache("Lotteryrule_views_".$id);;exit;
    }


    //玩法说明
    public function shuoming()
    {   
        if (request()->isPost()){
            $postData  = input('post.');
            $res = $this->comm(1,$postData);
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/shuoming');
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('shuoming')->where(['id'=>1])->find();

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

    //推广规则
    public function tgguize()
    {   
        if (request()->isPost()){
            $postData  = input('post.');
            $res = $this->comm(2,$postData);
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/shuoming');
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('shuoming')->where(['id'=>2])->find();

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

    //推广明细
    public function tgmingxi()
    {   
        if (request()->isPost()){
            $postData  = input('post.');
            $res = $this->comm(3,$postData);
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/shuoming');
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('shuoming')->where(['id'=>3])->find();

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

    //帮助中心
    public function help()
    {   
        if (request()->isPost()){
            $postData  = input('post.');
            $res = $this->comm(4,$postData);
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/shuoming');
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('shuoming')->where(['id'=>4])->find();

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

    //会员折扣
    public function zhekou()
    {   
        if (request()->isPost()){
            $postData  = input('post.');
            $res = $this->comm(5,$postData);
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/shuoming');
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('shuoming')->where(['id'=>5])->find();

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

    //优惠活动
    public function youhui()
    {   
        if (request()->isPost()){
            $postData  = input('post.');
            $res = $this->comm(6,$postData);
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/shuoming');
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('shuoming')->where(['id'=>6])->find();

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

    //常见问题
    public function wenti()
    {   
        if (request()->isPost()){
            $postData  = input('post.');
            $res = $this->comm(7,$postData);
        }

        //表单模板
        $tags                           = strtolower(request()->controller() . '/shuoming');
        $formData                       = $this->getFormFields($tags,1);

        //数据详情
        $info                           = model('shuoming')->where(['id'=>7])->find();

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

    public function comm($id=0,$postData=[])
    {
        //数据提交
        if (request()->isPost()){
            $update_data = [];
            $update_data['title']       = $postData['title'];
            $update_data['content']     = $postData['content'];

            $res = model('shuoming')->where(['id'=>$id])->update($update_data);

            if($res){

                $this->success('更新成功',Cookie('__forward__'));
            }else{

                $this->error('更新失败');
            }
        }

    }



}
?>