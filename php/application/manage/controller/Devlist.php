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
 * Description:系统功能列表模板管理
 */

namespace app\manage\controller;

use app\manage\controller\Base;

/**
 * 后台列表模板控制器
 */
class Devlist extends Base
{
	private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $this->apiUrl['index']        = 'Admin/Devlist/listData';
        $this->apiUrl['edit']         = 'Admin/Devlist/detailData';
        $this->apiUrl['add_save']     = 'Admin/Devlist/saveData';
        $this->apiUrl['edit_save']    = 'Admin/Devlist/saveData';
        $this->apiUrl['quickedit']    = 'Admin/Devlist/quickEditData';
        $this->apiUrl['del']          = 'Admin/Devlist/delData';
    }

	/**
	 * 表单列表
	 * @author xxx
	 */
	public function index()
	{
		//获取列表数据
		$search 			= [];
		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= 0;
        $parame['page']     = input('page',1);
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        //请求数据
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()])) 
        $this->error('未设置接口地址');

        $res                = $this->apiData($parame,$this->apiUrl[request()->action()],false);
        $allDevlist         = $this->getApiData() ;
        $list 				= (!empty($allDevlist) && isset($allDevlist['lists'])) ? $allDevlist['lists'] : [];
		$fieldList			= [];

		if (!empty($list)){
			//获取列表模板字段数据
			$search 			= [];
			$parame 			= [];
			$parame['uid']		= $this->uid;
	        $parame['hashid']	= $this->hashid;
			$parame['pid']		= $list[0]['id'];
	        $parame['page']     = input('page',1);
	        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

	        $res                = $this->apiData($parame,$this->apiUrl[request()->action()]);
        	$allDevlist         = $this->getApiData() ;
	        $fieldList 			= (!empty($allDevlist) && isset($allDevlist['lists'])) ? $allDevlist['lists'] : [];
			$fieldInfo 			= ['id'=>0,'pid'=>$parame['pid'],'require'=>0];

			if (!empty($fieldList)) {
				$firstid	= $fieldList[0]['id'];
				$firstpid	= $fieldList[0]['pid'];

				$parame 			= [];
				$parame['uid']		= $this->uid;
		        $parame['hashid']	= $this->hashid;
				$parame['id']		= $firstid;

		        $res                = $this->apiData($parame,$this->apiUrl['edit']);
				$fieldInfo			= $res  ? $this->getApiData() : $fieldInfo;
			}
		}

		//页面数据
		$pageData						= [];
		$pageData['isback']     		= 0;
        $pageData['title1']     		= '开发 - 系统列表板管理 ';
        $pageData['title2']     		= '系统列表模板添加/删除/编辑操作';
        $pageData['notice']     		= ['温馨提示：新增列表模板请点击第一栏加号','新增列表字段请先选择第一栏表单，再点击第二栏的加号'];

		//记录当前列表页的cookie
		cookie('__forward__',$_SERVER['REQUEST_URI']);
		
		//渲染数据到页面模板上
		$assignData['_list'] 			= $list;
		$assignData['_fieldList'] 		= $fieldList;
		$assignData['_fieldInfo'] 		= $fieldInfo;
		$assignData['pageData'] 		= $pageData;
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

		//数据详情
        $info                           = $this->getDetail(0);
		$info['status']					= 1;

		//渲染数据到页面模板上
		$assignData['info'] 			= $info;
		$this->assignData($assignData);

		//加载视图模板
		return view('addedit');
	}

	/**
	 * 编辑数据
	 */
	public function edit($id = 0)
	{
		//数据提交
		if (request()->isPost()) $this->update();

		//数据详情
        $info                           = $this->getDetail($id);
		if(empty($info)) $this->error('数据获取失败',Cookie('__forward__'));

		//渲染数据到页面模板上
		$assignData['info'] 			= $info;
		$this->assignData($assignData);

		//加载视图模板
		return view('addedit');
	}

	/**
	 * 删除数据
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

		if($res){

			//数据返回
			$this->success('删除成功',Cookie('__forward__'));
		} else {

			$this->error($this->getApiError()) ;
		}
	}

	//提交表单
	protected function update()
	{
		if (request()->isPost())
		{
			if (!isset($this->apiUrl[request()->action().'_save']) || empty($this->apiUrl[request()->action().'_save'])) $this->error('未设置接口地址');

			$parame 			= [];
			$parame['uid']		= $this->uid;
	        $parame['hashid']	= $this->hashid;

	        $id 				= intval(input('post.id'));
	        $pid 				= intval(input('post.pid'));
	        $title 				= trim(input('post.title'));
	        $status 			= intval(input('post.status'));
	        $sort 				= intval(input('post.sort'));
	        $tag 				= trim(input('post.tag',''));
	        $cname 				= trim(input('post.cname',''));
	        $width 				= input('post.width','10') ;
	        $config 			= '';

	        if ($pid <= 0) {

	        	if (empty($title)) $this->error('列表名称不能为空');
	        	if (empty($cname)) $this->error('控制器名称不能为空');
	        }else{

	        	if (empty($title)) $this->error('字段名称不能为空');
	        	if (empty($tag)) $this->error('字段标识不能为空');
	        }

	        if($pid > 0){

	            $data['title']          = $title ;
	            $data['tag']            = $tag ;
	            $data['type']           = input('post.type') ;
	            $data['width']          = $width;
	            $data['edit']           = input('post.edit',0) ;
	            $data['search']         = input('post.search',0) ;
	            $data['default']        = input('post.default','') ;
	            $data['attr']           = !empty(input('post.attr')) ? str_replace(array("\r\n", "\r", "\n"), " ", input('post.attr')) : '' ;
	            $config 				= json_encode($data);
	        }

	        $parame['title']	= $title;
	        $parame['status'] 	= $status == 1 ? 1 : 2;
	        $parame['sort'] 	= $sort <= 0 ? 1 : $sort;
	        $parame['tag']		= $tag;
	        $parame['cname']    = $cname;
			$parame['id']		= $id;
			$parame['pid']		= $pid;
			$parame['config']	= $config;
			$parame['width']	= $width;

	        //请求数据
	        $res       = $this->apiData($parame,$this->apiUrl[request()->action().'_save']) ;
	        $devlist   = $this->getApiData() ;

			if($res && !empty($devlist)){

				$devlist['ac']  	= $id > 0 ? 1 : 0;
				$devlist['title'] 	= $title;
				$devlist['pid'] 	= $pid;
				$devlist['status'] 	= $status;
				$devlist['width'] 	= $width;

				//数据返回
				$html = $this->getHtmls($devlist);

				$this->success(  $id >0 ? '更新成功' : '新增成功','', array_merge($devlist,['htmls'=>$html]));
			}
			else
			{
				$error = $this->getApiError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}

		$this->error('非法提交！');
	}

	public function changeFieldList()
	{
		$id 		= intval(input('post.id'));

		$fieldList	= $this->getFormField($id);

		$this->assign('_fieldList', $fieldList);

		$content 	= $this->fetch('filed_list');

		$firstid 	= 0;
		$firstpid 	= $id;
		if (!empty($fieldList)) {
			$firstid	= $fieldList[0]['id'];
			$firstpid	= $fieldList[0]['pid'];
		}

		return json(['content'=>$content,'id'=>$firstid,'pid'=>$firstpid]);
	}

	public function changeFieldInfo()
	{
		$pid 		= intval(input('post.pid'));
		$id 		= intval(input('post.id'));
		$fieldInfo 	= ['id'=>$id,'pid'=>$pid,'status'=>1,'require'=>0];

		if ($id >0) {

			$parame 			= [];
			$parame['uid']		= $this->uid;
	        $parame['hashid']	= $this->hashid;
			$parame['id']		= $id;

			$res                = $this->apiData($parame,$this->apiUrl['edit']);
			$fieldInfo			= $res  ? $this->getApiData() : [];

			//数据格式化
            if($res && $fieldInfo['pid'] > 0){

                $field 			= json_decode($fieldInfo['config'] , true) ;

                $field['attr'] 	= !empty($field['attr']) ? str_replace(' ',"\r", $field['attr']): '' ;

                $fieldInfo 		= array_merge($fieldInfo,$field) ;
            }
		}

		if ($pid <=0 && $id <= 0) $fieldInfo 	= [];

		//渲染数据到页面模板上
		$assignData['_fieldInfo'] 		= $fieldInfo;
		$this->assignData($assignData);

		//加载视图模板
		return view('filed_info');
	}

	protected function getHtmls($data)
	{
		if ($data['ac'] == 1) return '';

		$editUrl 		= url('Devlist/edit',array('id'=>$data['id']));
		$delUrl 		= url('Devlist/edit',array('id'=>$data['id']));
		$quickEditUrl 	= url('Devlist/quickEdit');
		$showId			= $data['pid'] == 0 ? '(<font color="red">'.$data['id'].'</font>)&nbsp;&nbsp;' : '';

		$htmls = '<tr id="devform_id_'.$data['id'].'" data-id ="'.$data['id'].'" data-pid ="'.$data['pid'].'" >
                <td align="left" class="handle" width="70%">
                  <div>
                    <span class="btn"><em><i class="fa fa-cog"></i>'.$data['title'].$showId.'<i class="arrow"></i></em>
                    <ul>
                      <li><a onClick="return layer_show(\'列表模板编辑\',\''.$editUrl.'\',500,350);" href="javascript:;">编辑</a></li>                
                      <li><a onClick="delfun(this)" href="javascript:;" data-url="'.$delUrl.'">删除</a></li>
                    </ul>
                    </span>
                  </div>
                </td>';
		if ($data['pid'] > 0) {

			$htmls .= '<td align="center" class="" width="20%">
	          <div style="text-align: center ;">'.$data['width'].'</div>
	        </td>';
		}

        $htmls .= '<td align="center" class="" width="30%">
                  <div data-yes="启用" data-no="禁用">';
        if ($data['status'] == 1) {

        	$htmls .= '<span class="yes" onClick="CommonJs.quickEdit(this,\''.$quickEditUrl.'\',"status",\''.$data['id'].'\');" ><i class="fa fa-check-circle"></i>启用</span>';
        }else{

        	$htmls .= ' <span class="no" onClick="CommonJs.quickEdit(this,\''.$quickEditUrl.'\',"status",\''.$data['id'].'\');" ><i class="fa fa-ban"></i>禁用</span>';
        }
                    
        $htmls .= '</div></td></tr>';

        return $htmls;
	}

	public function quickEdit()
	{
		//请求地址
        if (!isset($this->apiUrl[request()->action()]) || empty($this->apiUrl[request()->action()]))
        $this->error('未设置接口地址');
        
        //接口调用
        if ($this->questBaseEdit($this->apiUrl[request()->action()])) $this->success('更新成功');
        
        $this->error('更新失败');
	}

	//表单字段列表
	private function getFormField($pid = 0)
	{
		$search 			= [];
		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= $pid;
        $parame['page']     = input('page',1);
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $allDevlist         = $this->getApiData() ;

        $devlist 			= (!empty($allDevlist) && isset($allDevlist['lists'])) ? $allDevlist['lists'] : [];

		return $res ? $devlist : [];
	}

	//列表模板快速设置
	public function set_list($id = 0)
	{
		//数据提交
		if (request()->isPost()) $this->set_list_update();

		$search 			= [];
		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= $id;
        $parame['page']     = input('page',1);
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $allDevlist         = $this->getApiData() ;

        $devlist 			= (!empty($allDevlist) && isset($allDevlist['lists'])) ? $allDevlist['lists'] : [];

		$fieldList 			= $res ? $devlist : [];

		if(!empty($fieldList)){
			foreach ($fieldList as $key => $value) {

				foreach ($value as $kk => $vv) {
					
					if ($kk == 'config') {
						$fieldList[$key][$kk] 	= json_decode($vv,true);
					}
				}

				cache(md5("Admin/Devlist/detailData".$value['id']),$value);
			}
		}

		//记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

		//渲染数据到页面模板上
		$assignData['listPid'] 			= $id;
		$assignData['fieldList'] 		= $fieldList;
		$this->assignData($assignData);

		//加载视图模板
		return view();
	}

	//提交表单
	protected function set_list_update()
	{
		if (request()->isPost())
		{
			$parame 			= [];
			$parame['uid']		= $this->uid;
	        $parame['hashid']	= $this->hashid;
	        
	        $default       		= '' ;
	        $attr           	= '' ;
	        $cname           	= '' ;
	        $pid 				= intval(input('pid'));
	        if ($pid <= 0) $this->error('非法数据！');
	        
	        $id 				= intval(input('id'));
	        $config 			= [];

	        if ($id > 0 ) {
	        	$info 				= cache(md5("Admin/Devlist/detailData".$id));

	        	if (empty($info) ) $this->error('列表模板数据不存在');

	        	$config 			= !empty($info['config']) ? json_decode($info['config'],true) : [];
	        	$default 			= isset($config['default']) ? $config['default'] : '';
	        	$attr 				= isset($config['attr']) ? $config['attr'] : '';
	        	$cname 				= isset($info['cname']) ? $info['cname'] : '';
	        }
	        
	        $title 				= trim(input('title',''));
	        $status 			= intval(input('status'));
	        $sort 				= intval(input('sort',1));
	        $tag 				= trim(input('tag',''));
	        $type 				= trim(input('type','string'));
	        $width 				= input('width','10') ;
	        $edit 				= input('edit',0);
	        $search 			= input('search',0);

	        if (empty($title)) $this->error('表头名称不能为空');
	        if (empty($tag)) $this->error('数据标识不能为空');

            $config['title']          = $title ;
            $config['tag']            = $tag ;
            $config['type']           = $type ;
            $config['width']          = $width;
            $config['edit']           = $edit;
            $config['search']         = $search ;
            $config['default']        = $default ;
            $config['attr']           = $attr ;
            $config 				  = json_encode($config);

	        $parame['title']	= $title;
	        $parame['status'] 	= $status == 1 ? 1 : 2;
	        $parame['sort'] 	= $sort;
	        $parame['tag']		= $tag;
	        $parame['cname']    = $cname;
			$parame['id']		= $id;
			$parame['pid']		= $pid;
			$parame['config']	= $config;
			$parame['width']	= $width;

	        //请求数据
	        $res       			= $this->apiData($parame,$this->apiUrl['edit_save']) ;
	        $devlist   			= $this->getApiData() ;

			if(false !== $res && !empty($devlist)){

				$this->success(  $id >0 ? '更新成功' : '新增成功', Cookie('__forward__'));
			}
			else
			{
				$error = $this->getApiError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}

		$this->error('非法提交！');
	}

	public function cloneList($id =0)
	{
        //数据提交
		if (request()->isPost()) $this->clone_update();

		$search 			= [];
		$parame 			= [];
		$parame['uid']		= $this->uid;
        $parame['hashid']	= $this->hashid;
		$parame['pid']		= $id;
        $parame['page']     = input('page',1);
        $parame['search']   = !empty($search) ? json_encode($search) : '' ;

        $res                = $this->apiData($parame,$this->apiUrl['index']);
        $allDevlist         = $this->getApiData() ;

        $devlist 			= (!empty($allDevlist) && isset($allDevlist['lists'])) ? $allDevlist['lists'] : [];

		$fieldList 			= $res ? $devlist : [];

		if(!empty($fieldList)){
			foreach ($fieldList as $key => $value) {

				foreach ($value as $kk => $vv) {
					
					if ($kk == 'config') {
						$fieldList[$key][$kk] 	= json_decode($vv,true);
					}
				}

				cache(md5("Admin/Devlist/details".$value['id']),$value);
			}
		}

		//记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

		//渲染数据到页面模板上
		$assignData['lid'] 				= $id;
		$assignData['fieldList'] 		= $fieldList;
		$this->assignData($assignData);

		//加载视图模板
		return view();
	}

	protected function clone_update()
	{
		if(request()->isPost())
		{
			$param 					= request()->param();
			$list_title 			= input('list_title');
			$clone 					= (isset($param['clone']) && !empty($param['clone'])) ? $param['clone'] : [];

			if (empty($list_title)) $this->error('列表名称不能为空');
			if (empty($clone)) $this->error('克隆数据不能为空');

			$parame 				= [];
			$parame['uid']			= $this->uid;
	        $parame['hashid']		= $this->hashid;
	        $parame['listname']		= $list_title;
	        $parame['listid']		= intval(input('listId'));
	        $parame['cloneData']	= json_encode($clone);

	        $res 					= $this->apiData($parame,'Admin/Devlist/saveClone');
	        if($res){

				$this->success( '克隆成功', Cookie('__forward__'));
			}
			else
			{
				$error = $this->getApiError();
				$this->error(empty($error) ? '未知错误！' : $error);
			}
		}

		$this->error('非法提交！');
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
            $res        = $this->apiData($parame,$apiUrl);
            $info       = $res ? $this->getApiData() : $this->error($this->getApiError());
        }

        return $info;
    }
}
?>