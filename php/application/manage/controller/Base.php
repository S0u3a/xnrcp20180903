<?php

namespace app\manage\controller;

use think\Controller;
use think\facade\Lang;

/**
 * 后台管理基类
 */
class Base extends Controller
{
	/**
	 * 后台控制器初始化
	 */
	public function __construct()
    {
        parent::__construct();
        $this->baseData         = [];
        //当前用户ID
        $this->uid              = is_login();

        //超级管理员ID
        $this->administratorid  = config('extend.administrator_id');

        //当前用户身份秘钥
        $this->hashid           = session('user_auth.user_hash');

        //是否需要自动登录
        $this->autoLogin();

        //这里定义允许访问的IP
		/*if(false){
			// 检查IP地址访问
			if(!in_array(get_client_ip(),explode(',',C('ADMIN_ALLOW_IP')))) $this->error('403:禁止访问');
		}*/

		//菜单ID
        $this->menuid           = input('menuid',0);

		//当前登录用户详细资料
		$this->userInfo		= $this->userInfo();
        $this->groupId      = !empty($this->userInfo['group_id']) ? explode(',',$this->userInfo['group_id']):[0];

        //权限过滤
        if(!$this->checkMenu()) $this->error('未授权访问！');

		//当前用户拥有的所有菜单权限
		$this->menu 			= cache('SystemAuthMenu' . $this->hashid);

        if(empty($this->menu)){

        	//菜单数据缓存
			$parame['uid']		= $this->uid;
	        $parame['hashid']	= $this->hashid;
            $parame['page']     = 1;
            $parame['search']   = '';

            $res 				= $this->apiData($parame,'Api/Sys/menus');
            $allMenu 			= $this->getApiData();

            $this->menu 		= (!empty($allMenu) && isset($allMenu['lists'])) ? $allMenu['lists'] : [];

            cache('SystemAuthMenu' . $this->hashid,$this->menu);
        }

        $this->menu              = $this->formatAuthMenu($this->menu,$this->userInfo['rules'],$this->uid,$this->menuid);
        $this->extends_param     = '';
        $this->isdev             = config('extend.is_dev');
	}

    /**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     */
    public function tree($tree = null,$leve=1,$type='auth')
    {
        $leve ++ ;
        $view   = 'public/expand/'.(!empty($type) ? $type : 'auth').'_tree';
        return $this->fetch($view, ['level'=>$leve,'tree'=>$tree]);
    }

    /**
     * 根据用户权限过滤菜单
     * @param  array           $arr    需要处理的菜单数据
     * @param  array/string    $menuid 用户拥有的菜单ID
     * @param  integer         $uid    用户ID
     * @return array                   处理后的菜单数据
     */
    private function formatAuthMenu($arr=[],$menuid=[],$uid=0)
    {
        if (empty($arr) || $uid <= 0) return [];

        foreach ($arr as $key => $value) {

            if ($value['status'] != 1) {
                unset($arr[$key]);
                continue;
            }

            //格式化fsize
            $fwidth         = 800;
            $fheight        = 550;
            if(!empty($value['fsize'])){

                $fsizeArr       = explode('*',$value['fsize']);

                $fwidth         = intval($fsizeArr[0]);
                $fheight        = intval($fsizeArr[1]);
            }

            $arr[$key]['fwidth']    = $fwidth;
            $arr[$key]['fheight']   = $fheight;

        }

        if ($uid === 1) {
            
            return $arr;
        }

        $menuid             = (!empty($menuid) && is_string($menuid)) ? explode(',',$menuid) : $menuid;

        foreach ($arr as $key => $value) {

            if (!in_array($value['id'],$menuid)) {

                unset($arr[$key]);
            }
            else{

                //格式化fsize
                $fwidth         = 800;
                $fheight        = 550;
                if(!empty($value['fsize'])){

                    $fsizeArr       = explode('*',$value['fsize']);

                    $fwidth         = intval($fsizeArr[0]);
                    $fheight        = intval($fsizeArr[1]);
                }

                $arr[$key]['fwidth']    = $fwidth;
                $arr[$key]['fheight']   = $fheight;
            }
        }

        return $arr;
    }

    /**
     * 获取指定级别的菜单项
     * @param  array    $arr 需要处理的菜单数据
     * @param  integer  $pid 菜单上级ID
     * @return array         处理后的菜单数据
     */
    public function formatMenu($arr=[],$pid=0){
        $menu  = [];
        if (!empty($arr)) {
            foreach ($arr as $key => $value) {
                if ($value['pid'] == $pid) $menu[]     = $value;
            }
        }

        return $menu;
    }

    /**
     * 检测是否有权限执行
     * @return bool 是否有权限
     */
    private function checkMenu()
    {
        if ( isset($this->userInfo['rules']) && !empty($this->userInfo['rules']) )
        {
            if (request()->controller() === 'Index') return true;
            if ($this->userInfo['rules'] === 'all' || $this->userInfo['is_super'] == 1)  return true;
            if (!is_string($this->userInfo['rules'])) return false;

            $rules          = explode(',',$this->userInfo['rules']);
            if ($this->menuid >0 && in_array($this->menuid,$rules)) return true;
        }

        return false;
    }

	/**
	 * 获取用户信息
	 */
	final protected function userInfo()
    {
		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
        $parame['id']       = $this->uid;

        $res 				= $this->apiData($parame,'Api/User/userDetail');

        $userInfo			= $res  ? $this->getApiData() : [];

        if (!empty($userInfo))
        {	
			$userInfo['nickname']	 = empty($userInfo['nickname'])?$userInfo['username']:$userInfo['nickname'];
			$userInfo['last_time']	 = !empty($userInfo['last_login_time']) ? $userInfo['last_login_time'] : '/';
			$userInfo['reg_time']	 = !empty($userInfo['create_time']) ? $userInfo['create_time'] : '/';
			$userInfo['update_time'] = !empty($userInfo['update_time']) ? $userInfo['update_time'] : '/';
        }

		return $userInfo;
	}

	/**
	 * 执行登录跳转
	 */
	protected function goLogin()
	{
		if (request()->isAjax()) $this->error('您还没有登录!',url('Login/index'));
        if (isset($_SERVER['HTTP_REFERER'])) exit('<script>top.location.href="'.url('Login/index').'"</script>');
        header("Location:".url('Login/index'));exit();
    }

	//执行自动登录
	protected function autoLogin()
    {
        //用户信息存在不用登录
        if (!empty($this->uid) && $this->uid > 0 && !empty($this->hashid)) return true;

		$cookie_username	= cookie(md5('admin_username' . config('extends.uc_auth_key')));
		$cookie_password	= cookie(md5('admin_password' . config('extends.uc_auth_key')));

		if($cookie_username && $cookie_password){

			$username	= string_encryption_decrypt($cookie_username,'DECODE');
			$password	= string_encryption_decrypt($cookie_password,'DECODE');
			$username 	= string_safe_filter($username);

			/* 调用登录接口登录 */
			$parame['username']		= $username;
			$parame['password']		= $password;
			$parame['utype']		= 1;
			$parame['jpushid']		= '';

			$requestRes 			= apiReq($parame,'Api/User/ulogin');

			$backData 				= $requestRes[0];
			$errorInfo				= $requestRes[1];

			if(empty($errorInfo)){

				$backData		= json_decode($backData,true);

				if ($backData['Code'] == '000000') {
                    //指定cookie保存30天时间
                    cookie(md5('admin_username'.config('extend.uc_auth_key')),string_encryption_decrypt($username,'ENCODE'),2592000);
                    cookie(md5('admin_password'.config('extend.uc_auth_key')),string_encryption_decrypt($password,'ENCODE'),2592000);
					
					/* 记录登录SESSION和COOKIES */
					$auth = [
			            'uid'             => $backData['Data']['uid'],
                        'user_hash'       => $backData['Data']['hashid'],
					];

					session('user_auth', $auth);
					session('user_auth_sign', data_auth_sign($auth));

                    return true;
				}

			}
		}

        session('[destroy]');
        cookie(null);

        $this->goLogin();
	}

    protected function apiData($parame,$apiUrl)
    {
    	$this->setApiData([]);
        $this->setApiError([]);

        //请求参数或则接口地址为空，直接返回FALSE
    	if (empty($parame) || empty($apiUrl)) return false;

    	$requestRes 			= apiReq($parame,$apiUrl);

    	$backData 				= $requestRes[0];
        $errorInfo				= $requestRes[1];

        if(empty($errorInfo)){

            $backData			= is_array($backData) ? $backData : json_decode($backData,true);
            $backData           = is_array($backData) ? $backData : json_decode($backData,true);

            if (!isset($backData['Code'])){

        		$this->setApiError('接口报错'); 
        		return false;
        	}

            if ($backData['Code'] === '000000'  && isset($backData['Data'])) {
            	
            	$this->setApiData($backData['Data']);
            	return true;
            }else{

            	$this->setApiError($backData['Msg']);
       			return false;
            }
        }

        $this->setApiError($errorInfo['Msg']);
        return false;
    }

    private function setApiData($data=[])
    {
    	$this->apiData 		= $data;
    }

    private function setApiError($msg='')
    {
    	$this->apiError 	= $msg;
    }

    protected function getApiError()
    {
    	return $this->apiError;
    }

    protected function getApiData()
    {

    	return $this->apiData;
    }

    //获取列表设置
    protected function getListNote($listid = '')
    {
        //初始化数据
        $data                   = ['info'=>[],'search'=>[],'thead'=>[]];

        $listid    = !empty($listid) ? $listid : strtolower(request()->controller().'/'.request()->action());
        
        //获取列表模板详情
        $parame                 = [];
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['id']           = $listid;
        $res                    = $this->apiData($parame,'Api/Sys/listdetail');
        $info                   = $res ? $this->getApiData() : [];

        if (empty($info))  return $data;

        //获取列表模板字段数据
        $parame                 = [];
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['pid']          = $listid;
        $parame['page']         = 1;
        $parame['search']       = '';
        $res                    = $this->apiData($parame,'Api/Sys/listtpl');

        $listTpl                = $res ? $this->getApiData() : [];
        $list                   = (!empty($listTpl) && isset($listTpl['lists'])) ? $listTpl['lists'] : [];

        if (empty($list))  return $data;

        //数据格式化
        $i          = 0 ;
        $j          = 0 ;
        $search     = array() ;
        $thead      = array() ;

        //去除禁用的
        if (!empty($list)) {
            foreach ($list as $index => $item) {
                if ($item['status'] != 1) unset($list[$index]);
            }
        }

        //格式化数据
        if (!empty($list)) {

            $width              = 0;
            $counts             = count($list);
            $nums               = 0;

            foreach ($list as $index => $item) {

                $nums++;
                $list[$index]['config'] = json_decode($item['config'] , true) ;

                //处理默认数据
                $default                = !empty($list[$index]['config']['default']) ? explode(':',$list[$index]['config']['default']) : [];

                $list[$index]['config']['default'] = [];

                if (isset($default[0]) && isset($default[1]))
                {
                    if ($default[0] == 'parame') {
                        $list[$index]['config']['default']['type'] = $default[0];
                        $list[$index]['config']['default']['parame'] = $default[1];
                    } else {
                        $parame = array() ;
                        $arr = explode(',',$default[1]) ;
                        foreach ($arr as $key => $value) {
                            $arr = explode('=',$value) ;
                            $parame[$arr[0]] = $arr[1] ;
                        }
                        $list[$index]['config']['default']['type'] = $default[0];
                        $list[$index]['config']['default']['parame'] = count($arr)>1 ? $parame : $default[1];
                    }                    
                }

                if ($counts == $nums) {
                    $item['width']          = $width >= 100 ? 0 : 100-$width;
                }else{
                    $width                  += $item['width'];
                }

                if ($width >= 100)  continue;

                //表头位数据
                $thead[$index]['title']    = $item['title'] ;
                $thead[$index]['tag']      = $item['tag'] ;
                $thead[$index]['width']    = $item['width'] ;
                $thead[$index]['edit']     = $list[$index]['config']['edit'] ;
                $thead[$index]['search']   = $list[$index]['config']['search'] ;
                $thead[$index]['type']     = $list[$index]['config']['type'] ;
                $thead[$index]['attr']     = $list[$index]['config']['attr'] ;
                $thead[$index]['default']  = $list[$index]['config']['default'] ;

                //搜索位数据
                if ($list[$index]['config']['search'] ==1){
                    $search[$i]['title']    = $item['title'] ;
                    $search[$i]['tag']      = $item['tag'] ;
                    $search[$i]['width']    = $item['width'] ;
                    $search[$i]['edit']     = $list[$index]['config']['edit'] ;
                    $search[$i]['search']   = $list[$index]['config']['search'] ;
                    $search[$i]['type']     = $list[$index]['config']['type'] ;
                    $search[$i]['attr']     = $list[$index]['config']['attr'] ;

                    $search[$i]['default']  = $list[$index]['config']['default'] ;

                    $i++ ;
                }
            }
        }

        $data['info']   = $info ;
        $data['search'] = $search ;
        $data['thead']  = $thead ;

        return $data ;
    }

    //获取表单设置
    protected function getFormFields($formId,$isEdit)
    {
        //初始化数据
        $data                   = ['info'=>[],'list'=>[]];

        //获取表单模板详情
        $parame                 = [];
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['id']           = $formId;
        $res                    = $this->apiData($parame,'Api/Sys/fromdetail');
        $info                   = $res ? $this->getApiData() : [];

        if (empty($info))  return $data;

        //获取表单模板字段数据
        $parame                 = [];
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['pid']          = $formId;
        $parame['page']         = 1;
        $parame['search']       = '';
        $res                    = $this->apiData($parame,'Api/Sys/fromtpl');

        $listTpl                = $res ? $this->getApiData() : [];
        $formList               = (!empty($listTpl) && isset($listTpl['lists'])) ? $listTpl['lists'] : [];

        if (empty($formList))  return $data;

        //缓存表单一条数据
        if (isset($info['id']) && $info['id'] > 0 )  cache('DevformDetails'.$info['id'],$info);

        if ($isEdit == '-2')  return ['info'=>$info,'list'=>$formList] ;

        //数据整理
        $formFields         = array() ;
        foreach ($formList as $index => $datum) {

            if ($datum['status'] != 1 || empty($datum['config'])) continue;
            $config         = !empty($datum['config']) ? json_decode($datum['config'], true) : [];

            unset($datum['config']);
            $config         = array_merge($datum,$config);

            $formFields[]   = $config;
        }

        $type                   = $isEdit>0 ? 'edit' : 'add' ;
        //格式化
        $i = 0 ;
        $formField = array() ;
        foreach ($formFields as $index => $item) {
            $formFields[$index] = $item;

            if ($formFields[$index][$type] <= 0 && $isEdit != '-1') continue;

            if(!empty($formFields[$index]['default'])){
                //获取当前默认值类型
                $default = explode(':', $formFields[$index]['default']);
                $formFields[$index]['default'] = [];
                if ( isset($default[0]) && isset($default[1]) )
                {
                    if ($default[0] == 'parame') {
                        $formFields[$index]['default']['type'] = $default[0];
                        $formFields[$index]['default']['parame'] = $default[1];
                    } else {
                        $parame = array() ;
                        $arr = explode(',',$default[1]) ;
                        foreach ($arr as $key => $value) {
                            $arr = explode('=',$value) ;
                            $parame[$arr[0]] = $arr[1] ;
                        }
                        $formFields[$index]['default']['type'] = $default[0];
                        $formFields[$index]['default']['parame'] = count($arr)>1 ? $parame : $default[1];
                    }
                }
            }

            $formField[$i] = $formFields[$index];
            $i++;
        }

        $arr = [];
        if (!empty($formField)) {
            
            foreach ($formField as $k => $v) {
                
                $group = empty($v['group']) ? '基本信息' : $v['group'];

                $arr[$group][]        = $v;
            }
        }

        return ['info'=>$info,'list'=>$arr] ;
    }

    /**
     * [quickEdit 快捷编辑]
     * @return [json] [反馈信息]
     */
    protected function questBaseEdit($apiUrl='')
    {
        $fieldName      = input('post.fieldName');
        $dataId         = intval(input('post.dataId'));
        $value          = trim(input('post.value'));

        if (empty($apiUrl)) $this->error('更新失败[apiUrl]！');
        if (empty($fieldName)) $this->error('更新失败[fieldName]！');
        if ($dataId == 0) $this->error('更新失败[dataId]！');

        $parame                 = [];
        $parame['uid']          = $this->uid;
        $parame['hashid']       = $this->hashid;
        $parame['id']           = $dataId;
        $parame['fieldName']    = $fieldName;
        $parame['updata']       = $value;

        $res                    = $this->apiData($parame,$apiUrl);

        $data                   = $res  ? $this->getApiData() : $this->error($this->getApiError());

        return (isset($data['id']) && $data['id'] > 0) ? $data['id'] : 0;
    }

    public function assignData($data = [])
    {
        $baseData['userInfo']       = $this->userInfo;
        $baseData['uid']            = $this->uid;
        $baseData['hashid']         = $this->hashid;
        $baseData['menuid']         = $this->menuid;
        $baseData['menu']           = $this->menu;
        $baseData['extends_param']  = $this->extends_param;
        $baseData['isdev']          = $this->isdev;
        $baseData['thisObj']        = $this;
        $baseData['listId']         = 0;
        $baseData['formId']         = 0;
        $baseData['isTree']         = 0;
        $baseData['pageData']       = ['isback'=>0,'title1'=>'','title2'=>'','notice'=>''];
        $backData['defaultData']    = [];
        $baseData['threePartyplug'] = ['bdmap'=>0,'gdmap'=>0,'editor'=>0,'image'=>0,'images'=>0,'file'=>0];
        
        $assignData                 = !empty($data) ? array_merge($baseData,$data) : $baseData;
        $this->assign($assignData);
    }

    /**
     * 扩展枚举，布尔，单选，复选等数据选项数据
     * @return array 默认数据
     */
    protected function getDefaultParameData()
    {
        return [];
    }
}