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
 * Date: 2018-02-08
 * Description:系统功能文件管理
 */

namespace app\manage\controller;

use app\manage\controller\Base;

/**
 * 后台文件控制器
 */
class Devfile extends Base
{
	//预定义
    private $listId         = [];
    private $formId         = [];
    private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->listId['index']        = 90;
        $this->formId['add']          = 178;
        $this->formId['edit']         = 178;
    }

	/**
	 * 文件列表
	 * @author xxx
	 */
	public function index()
	{
		$menuid     = input('menuid') ;
        $search     = input('search','');

        $topMenu    = formatMenuByPidAndPos($menuid,2, $this->menu);
        $rightMenu  = formatMenuByPidAndPos($menuid,3, $this->menu);

        //获取表头以及搜索数据
        $listNode   = $this->getListNote(request()->controller()) ;

		$allFile        = glob ( \Env::get('APP_PATH') . 'Admin/Controller/' . '*' );
		$hideFile 		= ['Devfile','Devform','Devlist','Devmenu'];

        if (!empty($allFile)) {

            foreach ($allFile as $key => $file) {

            	$finfo 				= pathinfo($file);
            	$basename 			= $finfo['basename'];
            	$cname 				= str_replace('Controller.class.php','',$basename);

            	if ($finfo['extension'] != 'php' || in_array($cname,$hideFile)) {

            		unset($allFile[$key]);continue;
            	}

            	$ctime 		= date('Y-m-d H:i:s',filemtime ( $file ));
            	$utime 		= date('Y-m-d H:i:s',filectime ( $file ));
            	$ftag 		= md5($file.config('extend.uc_auth_key'));

            	$fname[]	= ['id'=>$cname,'name'=>$basename,'create_time'=>$ctime,'update_time'=>$utime,'tag'=>$ftag];
            }
        }

		//页面数据
		$pageData						= [];
		$pageData['isback']     		= 0;
        $pageData['title1']     		= '开发 - 系统文件列表';
        $pageData['title2']     		= '系统文件索引与管理';
        $pageData['notice']     		= ['列表只是展示部分字段信息，详情请点击编辑查看'];

        //记录当前列表页的cookie
		cookie('__forward__',$_SERVER['REQUEST_URI']);
		
        //渲染数据到页面模板上
		$assignData['_page'] 			= '';
		$assignData['_total'] 			= count($allFile);
		$assignData['topMenu'] 			= $topMenu;
		$assignData['rightMenu'] 		= $rightMenu;
		$assignData['pageData'] 		= $pageData;
		$assignData['listId'] 			= isset($listNode['info']['id']) ? intval($listNode['info']['id']) : 0;
		$assignData['listNode'] 		= $listNode;
		$assignData['listData'] 		= $fname;
		$this->assignData($assignData);

		//加载视图模板
		return view();
	}

	/**
	 * 新增数据
	 */
	public function add()
	{
		//数据提交
		if (request()->isPost()) $this->update();

		//表单模板数据
		$formData           			= $this->getFormFields(request()->controller(),0) ;

		$info               			= [];
        //页面数据
		$pageData						= [];
		$pageData['isback']     		= 1;
        $pageData['title1']     		= '开发 - 文件脚本管理 ';
        $pageData['title2']     		= '文件脚本添加/查看操作';
        $pageData['notice']     		= [];

        //记录当前列表页的cookie
		cookie('__forward__',$_SERVER['REQUEST_URI']);

        //渲染数据到页面模板上
		$assignData['formId'] 			= isset($formData['info']['id']) ? intval($formData['info']['id']) : 0;
		$assignData['formFieldList'] 	= $formData['list'];
		$assignData['info'] 			= $info;
		$assignData['defaultData'] 		= $this->getDefaultParameData();
		$assignData['pageData'] 		= $pageData;
		$this->assignData($assignData);

		//加载视图模板
		return view('addedit');
	}

	//提交表单
	protected function update()
	{
		if(request()->isPost())
		{
			$cname 				= trim(input('name'));
			$auther 			= trim(input('auther'));
			$description 		= trim(input('description'));

			if (empty($cname)) $this->error('控制器名称不能为空');
			if (empty($auther)) $this->error('开发者姓名不能为空');
			if (empty($description)) $this->error('控制器功能描述不能为空');

			$fileName 			= '';
			$name 				= explode('_', $cname);
			if (!empty($name)) {
				foreach ($name as $key => $value) {
					
					$fileName  .= ucfirst($value);
				}
			}

			if (empty($fileName)) $this->error('控制器名称格式错误');

			$file 				= \Env::get('APP_PATH') . 'manage/controller/'.$fileName . '.php';
			$base 				= \Env::get('APP_PATH') . 'manage/tpl/ctpl.php';

			if (file_exists($file)) $this->error('文件已经存在');

			$baseContent	= file_get_contents($base);

			$replace1 		= [
			'{ControllerAuth}',
			'{ControllerDate}',
			'{ControllerDescription}',
			'{ControllerName}',
			'{ApiName}'
			];
			$replace2 		= [$auther,date('Y-m-d',time()),$description,$fileName,$fileName];

			$fileContent	= str_replace($replace1,$replace2, $baseContent);

			file_put_contents($file,$fileContent);

			//创建视图目录以及文件
			$viewDir 		= \Env::get('APP_PATH') . 'manage/view/'.strtolower($fileName);

			//如果没有此目录就创建此目录  
			if(!is_dir($viewDir)) mkdir($viewDir);

			$index 			= \Env::get('APP_PATH') . 'manage/tpl/vindex.html';
			$addedit 		= \Env::get('APP_PATH') . 'manage/tpl/vaddedit.html';
			$index2 		= $viewDir . '/index.html';
			$addedit2 		= $viewDir . '/addedit.html';

			//复制文件
			copy($index,$index2);
			copy($addedit,$addedit2);

			$this->success('创建成功',Cookie('__forward__')) ;
		}

		$this->error('非法提交！');
	}
}
?>