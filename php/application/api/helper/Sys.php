<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 杭州新苗科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Helper只要处理业务逻辑，默认会初始化数据列表接口、数据详情接口、数据更新接口、数据删除接口、数据快捷编辑接口
 * 如需其他接口自行扩展，默认接口如实在无需要可以自行删除
 */
namespace app\api\helper;

use app\common\helper\Base;
use think\facade\Lang;

class Sys extends Base
{
	private $dataValidate 		= null;
    private $mainTable          = 'devmenu';
	
	public function __construct($parame=[],$className='',$methodName='',$modelName='')
    {
        parent::__construct($parame,$className,$methodName,$modelName);
        $this->apidoc           = request()->param('apidoc',0);
    }
    
    /**
     * 初始化接口 固定不用动
     * @param  [array]  $parame     接口需要的参数
     * @param  [string] $className  类名
     * @param  [string] $methodName 方法名
     * @return [array]              接口输出数据
     */
	public function apiRun()
    {   
        if (!$this->checkData($this->postData)) return json($this->getReturnData());
        //加载验证器
        $this->dataValidate = new \app\api\validate\DataValidate;
        
        //规避没有设置主表名称
        if (empty($this->mainTable)) return $this->returnData(['Code' => '120020', 'Msg'=>lang('120020')]);
        $this->delDevInfo();
        //接口执行分发
        $methodName     = $this->actionName;
        $data           = $this->$methodName($this->postData);
        //设置返回数据
        $this->setReturnData($data);
        //接口数据返回
        return json($this->getReturnData());
    }

    //支持内部调用
    public function isInside($parame,$aName)
    {
        return $this->$aName($parame);
    }
    
    /*api:d815b04b2ed4a96e0d0021c7f6281c8d*/
    /**
     * * 系统菜单列表接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function menus($parame)
    {
        //主表数据库模型
        $dbModel                    = model('devmenu');

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = 'devmenu';

        //主表名称，可以为空，默认为main
        $modelParame['MainAlias']   = 'main';

        //主表待查询字段，可以为空，默认全字段
        $modelParame['MainField']   = [];

        //定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
        //Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
        $RelationTab                = [];
        //$RelationTab['member']        = array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

        $modelParame['RelationTab'] = $RelationTab;

        //接口数据
        $modelParame['apiParame']   = $parame;

        //检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
        $modelParame['whereFun']    = 'formatWhereDefault';

        //排序定义
        $modelParame['order']       = 'main.sort desc,main.id desc';        
        
        //数据分页步长定义
        $modelParame['limit']       = $this->apidoc == 2 ? 1 : 1000;

        //数据分页页数定义
        $modelParame['page']        = (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

        //数据缓存是时间，默认0 不缓存 ,单位秒
        $modelParame['cacheTime']   = 0;

        //列表数据
        $lists                      = $dbModel->getPageList($modelParame);

        //数据格式化
        $data                       = (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];

        if (!empty($data)) {

            //自行定义格式化数据输出
            //foreach($data as $k=>$v){

            //}
        }

        $lists['lists']             = $data;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /*api:d815b04b2ed4a96e0d0021c7f6281c8d*/

    /*api:fdab5daa279751b566dac6b93e74cbf3*/
    /**
     * * 系统表单模板详情接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function fromdetail($parame)
    {
        //主表数据库模型
        $dbModel            = model('devform');

        if (is_numeric($parame['id'])) {
            
            $info               = $dbModel->getOneById($parame['id']);
        }else{

            $info               = $dbModel->where('cname','eq',$parame['id'])->find();
        }

        if (!empty($info)) {
            
            //格式为数组
            $info                   = $info->toArray();

            //自行对数据格式化输出
            //...

            return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info];
        }else{

            return ['Code' => '100015', 'Msg'=>lang('100015')];
        }
    }

    /*api:fdab5daa279751b566dac6b93e74cbf3*/

    /*api:fe53f10007aa86f7a470e2c094f30acc*/
    /**
     * * 系统表单模板列表接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function fromtpl($parame)
    {
        //主表数据库模型
        $dbModel                    = model('devform');

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = 'devform';

        //主表名称，可以为空，默认为main
        $modelParame['MainAlias']   = 'main';

        //主表待查询字段，可以为空，默认全字段
        $modelParame['MainField']   = [];

        //定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
        //Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
        $RelationTab                = [];
        //$RelationTab['member']        = array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

        $modelParame['RelationTab'] = $RelationTab;

        //接口数据
        $modelParame['apiParame']   = $parame;

        //检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
        $modelParame['whereFun']    = 'formatWhereDefault';

        //排序定义
        $modelParame['order']       = 'main.sort desc,main.id asc';     
        
        //数据分页步长定义
        $modelParame['limit']       = $this->apidoc == 2 ? 1 : 1000;

        //数据分页页数定义
        $modelParame['page']        = (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

        //数据缓存是时间，默认0 不缓存 ,单位秒
        $modelParame['cacheTime']   = 0;

        //列表数据
        $lists                      = $dbModel->getPageList($modelParame);

        //数据格式化
        $data                       = (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];

        if (!empty($data)) {

            //自行定义格式化数据输出
            //foreach($data as $k=>$v){

            //}
        }

        $lists['lists']             = $data;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /*api:fe53f10007aa86f7a470e2c094f30acc*/

    /*api:b3b651a930b24846b7452bde6437daea*/
    /**
     * * 系统列表模板详情接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function listdetail($parame)
    {
        //主表数据库模型
        $dbModel            = model('devlist');

        if (is_numeric($parame['id'])) {
            
            $info               = $dbModel->getOneById($parame['id']);
        }else{

            $info               = $dbModel->where('cname','eq',$parame['id'])->find();
        }

        if (!empty($info)) {
            
            //格式为数组
            $info                   = $info->toArray();

            //自行对数据格式化输出
            //...

            return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info];
        }else{

            return ['Code' => '100015', 'Msg'=>lang('100015')];
        }
    }

    /*api:b3b651a930b24846b7452bde6437daea*/

    /*api:72779f977b706fa9f1dde9255acee339*/
    /**
     * * 系统表单模板列表接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function listtpl($parame)
    {
        //主表数据库模型
        $dbModel                    = model('devlist');

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = 'devlist';

        //主表名称，可以为空，默认为main
        $modelParame['MainAlias']   = 'main';

        //主表待查询字段，可以为空，默认全字段
        $modelParame['MainField']   = [];

        //定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
        //Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
        $RelationTab                = [];
        //$RelationTab['member']        = array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

        $modelParame['RelationTab'] = $RelationTab;

        //接口数据
        $modelParame['apiParame']   = $parame;

        //检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
        $modelParame['whereFun']    = 'formatWhereDefault';

        //排序定义
        $modelParame['order']       = 'main.sort desc,id asc';      
        
        //数据分页步长定义
        $modelParame['limit']       = $this->apidoc == 2 ? 1 : 1000;

        //数据分页页数定义
        $modelParame['page']        = (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

        //数据缓存是时间，默认0 不缓存 ,单位秒
        $modelParame['cacheTime']   = 0;

        //列表数据
        $lists                      = $dbModel->getPageList($modelParame);

        //数据格式化
        $data                       = (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];

        if (!empty($data)) {

            //自行定义格式化数据输出
            //foreach($data as $k=>$v){

            //}
        }

        $lists['lists']             = $data;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /*api:72779f977b706fa9f1dde9255acee339*/

    /*api:eb49daa8729e795c14d6bda11d254b09*/
    /**
     * * 清理缓存
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function clearCache($parame)
    {
        delFile(\Env::get('RUNTIME_PATH'));

        \Cache::clear();

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['res'=>'ok']];
    }

    /*api:eb49daa8729e795c14d6bda11d254b09*/

    /*api:cb115b3f0116f9cabf9c46503a23027d*/
    /**
     * * 系统配置接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function config($parame)
    {
        $config                         = [];
        $config['ios_version']          = '1.0.0';
        $config['android_version']      = '1.0.0';
        $config['sys_notice']           = 'xxxxxxxxxx';

        $ios_pay                        = config('system_config.ios_pay');

        $config['show_pay']             = $ios_pay == 1 ? 0 : 1;
        $config['kf_info_one']          = config('system_config.kf_info_one');
        $config['kf_info_two']          = config('system_config.kf_info_two');
        $config['share_title']          = config('system_config.share_title');
        $config['share_desc']           = config('system_config.share_desc');

        $paytype                        = config('system_config.paytype');
        $paytype                        = !empty($paytype) ? (string)$paytype : '';
        $config['pay_type']             = $paytype;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$config];
    }

    /*api:cb115b3f0116f9cabf9c46503a23027d*/

    /*接口扩展*/

    private function delDevInfo()
    {
        /*$apiid = [72,73,74,75,76,77];
        for($i=28;$i<= 58 ;$i++){
            array_push($apiid,$i);
        }

        $apidoc_project_id  = config('extend.apidoc_project_id');
        model('devapi_module')->where('id','>',0)->update(['project_id'=>$apidoc_project_id]);
        model('devapi')->delData($apiid);
        model('devapi_module')->delData(6);
        model('devapi_parame')->where('api_id','in',$apiid)->delete();*/

        return true;
    }
}
