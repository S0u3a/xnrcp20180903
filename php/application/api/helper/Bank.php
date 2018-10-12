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

class Bank extends Base
{
	private $dataValidate 		= null;
    private $mainTable          = 'bank_abbreviate';
	
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

    /**
     * 接口列表数据
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function listData($parame)
    {
        //主表数据库模型
		$dbModel					= model($this->mainTable);

		/*定义数据模型参数*/
		//主表名称，可以为空，默认当前模型名称
		$modelParame['MainTab']		= $this->mainTable;

		//主表名称，可以为空，默认为main
		$modelParame['MainAlias']	= 'main';

		//主表待查询字段，可以为空，默认全字段
		$modelParame['MainField']	= [];

		//定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
		//Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
		$RelationTab				= [];
		//$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

		$modelParame['RelationTab']	= $RelationTab;

        //接口数据
        $modelParame['apiParame']   = $parame;

		//检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
		$modelParame['whereFun']	= 'formatWhereDefault';

		//排序定义
		$modelParame['order']		= 'main.id desc';		
		
		//数据分页步长定义
		$modelParame['limit']		= isset($parame['limit']) ? $parame['limit'] : 100;

		//数据分页页数定义
		$modelParame['page']		= (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

		//数据缓存是时间，默认0 不缓存 ,单位秒
		$modelParame['cacheTime']	= 0;

		//列表数据
		$lists 						= $dbModel->getPageList($modelParame);

		//数据格式化
		$data 						= (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];

    	if (!empty($data)) {

            //自行定义格式化数据输出
    		//foreach($data as $k=>$v){

    		//}
    	}

    	$lists['lists'] 			= $data;

    	return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /**
     * 接口数据添加/更新
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function saveData($parame)
    {
        //主表数据库模型
    	$dbModel					= model($this->mainTable);

        //数据ID
        $id                         = isset($parame['id']) ? intval($parame['id']) : 0;

        //自行定义入库数据 为了防止参数未定义报错，先采用isset()判断一下
        $saveData                   = [];
        //$saveData['parame']         = isset($parame['parame']) ? $parame['parame'] : '';

        //规避遗漏定义入库数据
        if (empty($saveData)) return ['Code' => '120021', 'Msg'=>lang('120021')];

        //自行处理数据入库条件
        //...
		
        //通过ID判断数据是新增还是更新
    	if ($id <= 0) {

            //执行新增
    		$info 									= $dbModel->addData($saveData);
    	}else{

            //执行更新
    		$info 									= $dbModel->updateById($id,$saveData);
    	}

    	if (!empty($info)) {

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * 接口数据详情
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function detailData($parame)
    {
        //主表数据库模型
    	$dbModel			= model($this->mainTable);

        //数据ID
        $id                 = isset($parame['id']) ? intval($parame['id']) : 0;
        if ($id <= 0) return ['Code' => '120023', 'Msg'=>lang('120023')];

        //数据详情
    	$info 				= $dbModel->getOneById($id);

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

    /**
     * 接口数据快捷编辑
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function quickEditData($parame)
    {
        //主表数据库模型
    	$dbModel			= model($this->mainTable);

        //数据ID
        $id                 = isset($parame['id']) ? intval($parame['id']) : 0;
        if ($id <= 0) return ['Code' => '120023', 'Msg'=>lang('120023')];

        //根据ID更新数据
    	$info 				= $dbModel->updateById($id,[$parame['fieldName']=>$parame['updata']]);

    	if (!empty($info)) {

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['id'=>$id]];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * 接口数据删除
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function delData($parame)
    {
        //主表数据库模型
    	$dbModel				= model($this->mainTable);

        //数据ID
        $id                 = isset($parame['id']) ? intval($parame['id']) : 0;
        if ($id <= 0) return ['Code' => '120023', 'Msg'=>lang('120023')];

        //自行定义删除条件
        //...
        
        //执行删除操作
    	$delCount				= $dbModel->delData($id);

    	return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['count'=>$delCount]];
    }

    /*api:f57722e0ddbddb9bd1e98af00940f4b1*/
    /**
     * * 用户银行卡绑定
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function userBind($parame)
    {
        //主表数据库模型
        $dbModel                = model('bank_bind');

        //用户资料信息
        $userModel              = model('user_detail');
        $userinfo               = $userModel->getOneByUid($parame['uid']);
        $cash_pwd               = $dbModel->password(isset($parame['password']) ? $parame['password'] : '');

        //检测用户是否设置了提现密码
        if (!isset($userinfo['cash_pwd']) || empty($userinfo['cash_pwd']))
        return ['Code' => '200010', 'Msg'=>lang('200010')];

        //检测用户提现密码是否错误
        if ($userinfo['cash_pwd'] !== $cash_pwd)
        return ['Code' => '200007', 'Msg'=>lang('200007')];

        //检测用户是否已经绑定过
        $isbind                 = $dbModel->isBind($parame);
        if ($isbind > 0) return ['Code' => '200001', 'Msg'=>lang('200001')];

        //根据bank_id查找是否是支持的银行
        $bank                   = model('bank_abbreviate')->getOneById($parame['bank_id']);
        if (empty($bank)) return ['Code' => '200002', 'Msg'=>lang('200002')];


        //自行书写业务逻辑代码
        $saveData                   = [];
        $saveData['uid']            = $parame['uid'];
        $saveData['bank_name']      = $bank['title'];
        $saveData['bank_id']        = $bank['id'];
        $saveData['abbreviate']     = $bank['abbreviate'];
        $saveData['real_name']      = isset($parame['real_name']) ? $parame['real_name'] : '';
        $saveData['sfz']            = isset($parame['sfz']) ? $parame['sfz'] : '';
        $saveData['bank_num']       = isset($parame['bank_num']) ? $parame['bank_num'] : '';
        $saveData['bank_address']   = isset($parame['bank_address']) ? $parame['bank_address'] : '';
        $saveData['update_time']    = time();
        $saveData['create_time']    = time();

        $res                        = $dbModel->addData($saveData);
        
        //需要返回的数据体
        $Data['id']                 = $res['id'];
        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:f57722e0ddbddb9bd1e98af00940f4b1*/

    /*api:437d02ea932f39d5f717531b6fef6163*/
    /**
     * * 用户绑定银行卡详情
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function userBankDetail($parame)
    {
        //主表数据库模型
        $dbModel                = model('bank_bind');

        //检测用户是否已经绑定过
        $isbind                 = $dbModel->isBind($parame);
        if ($isbind <= 0) return ['Code' => '200003', 'Msg'=>lang('200003')];

        //数据详情
        $info               = $dbModel->getOneById($isbind);

        if (!empty($info)) {
            
            //格式为数组
            $info                   = $info->toArray();

            return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info];
        }else{

            return ['Code' => '100015', 'Msg'=>lang('100015')];
        }

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:437d02ea932f39d5f717531b6fef6163*/

    /*api:c563e522fc3935420b9163c1874b86a0*/
    /**
     * * 用户删除绑定的银行卡
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function userDelBind($parame)
    {
        //主表数据库模型
        $dbModel                = model('bank_bind');

        //数据ID
        $id                 = isset($parame['id']) ? intval($parame['id']) : 0;
        if ($id <= 0) return ['Code' => '120023', 'Msg'=>lang('120023')];

        //检测用户是否已经绑定过
        $isbind                 = $dbModel->isBind($parame);
        if ($isbind <= 0) return ['Code' => '200003', 'Msg'=>lang('200003')];
        if ($id !== $isbind) return ['Code' => '200004', 'Msg'=>lang('200004')];

        //执行删除操作
        $delCount               = $dbModel->delData($isbind);

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['count'=>$delCount]];
    }

    /*api:c563e522fc3935420b9163c1874b86a0*/

    /*api:b88ebc257638a0480a07f0bfb2c79037*/
    /**
     * * 用户提现申请
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function cash($parame)
    {
        //主表数据库模型
        $dbModel                = model('bank_bind');

        //用户资料信息
        $userModel   = model('user_detail');
        $userinfo    = $userModel->getOneByUid($parame['uid']);

        //是否设置了提现密码
        if (!isset($userinfo['cash_pwd']) || empty($userinfo['cash_pwd']))
        return ['Code' => '200010', 'Msg'=>lang('200010')];

        //检测用户是否已经绑定过
        $isbind                 = $dbModel->isBind($parame);
        if ($isbind <= 0) return ['Code' => '200003', 'Msg'=>lang('200003')];

        $minMoney               = 50;
        $cash_money             = isset($parame['cash_money']) ? $parame['cash_money']*1 : 0;
        $cash_pwd               = isset($parame['cash_pwd']) ? $parame['cash_pwd'] : '1qaz2wsx';

        //检测提现密码是否正确
        if ($userinfo['cash_pwd'] != $dbModel->password($cash_pwd))
        return ['Code' => '200007', 'Msg'=>lang('200007')];
    
        //不能低于最小起提
        if ($cash_money < $minMoney) return ['Code' => '200005', 'Msg'=>lang('200005',[$minMoney])];

        //银行卡绑定信息
        $user_bank              = $dbModel->getOneById($isbind);
        
        //判断余额是否足够
        if ($userinfo['cash_money']<$cash_money || $userinfo['account']<$cash_money)
        return ['Code' => '200006', 'Msg'=>lang('200006')];
        
        //入库提现记录
        $saveData                   = [];
        $saveData['uid']            = $parame['uid'];
        $saveData['money']          = $cash_money;
        $saveData['status']         = 1;
        $saveData['bank_name']      = $user_bank['bank_name'];
        $saveData['real_name']      = $user_bank['real_name'];
        $saveData['sfz']            = $user_bank['sfz'];
        $saveData['bank_num']       = $user_bank['bank_num'];
        $saveData['bank_address']   = $user_bank['bank_address'];
        $saveData['create_time']    = time();

        $res                        = model('bank_cash')->addData($saveData);
        if ($res) {
            //减少金额
            $data                   = [];
            $data['account']        = $userinfo['account']-$cash_money;
            $data['cash_money']     = $userinfo['cash_money']-$cash_money;
            $userModel->updateById($userinfo['id'],$data);
            $userModel->delDetailDataCacheByUid($userinfo['uid']);
            
            //写日志
            model('user_account_log')->addAccountLog($userinfo['id'],$cash_money,'提现驳回退还',1,6);
        }

        //需要返回的数据体
        $Data['id']                   = $res['id'];

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:b88ebc257638a0480a07f0bfb2c79037*/

    /*api:f29fe215b9ee885e349dd450cbb829d8*/
    /**
     * * 提现状态记录
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function cashList($parame)
    {
        //主表数据库模型
        $dbModel                    = model('bank_cash');

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = 'bank_cash';

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
        $modelParame['order']       = 'main.id desc';       
        
        //数据分页步长定义
        $modelParame['limit']       = isset($parame['limit']) ? $parame['limit'] : 10;

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

    /*api:f29fe215b9ee885e349dd450cbb829d8*/

    /*api:dd7072fdb56159fdea1c56c8c23f2607*/
    /**
     * * 修改提现密码
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function cashPwdUpdate($parame)
    {
        //主表数据库模型
        $dbModel                = model('bank_bind');

        $old_pwd                = isset($parame['old_pwd']) ? $parame['old_pwd'] : '';
        $new_pwd                = isset($parame['new_pwd']) ? $parame['new_pwd'] : '';
        $rep_pwd                = isset($parame['rep_pwd']) ? $parame['rep_pwd'] : '';

        if (!empty($new_pwd) && md5($new_pwd) !== md5($rep_pwd))
        return ['Code' => '200008', 'Msg'=>lang('200008')];

        $old_pwd                = $dbModel->password($old_pwd);
        $new_pwd                = $dbModel->password($new_pwd);

        //用户资料信息
        $userModel              = model('user_detail');
        $userinfo               = $userModel->getOneByUid($parame['uid']);

        //是否设置了提现密码
        if (empty($userinfo['cash_pwd']))
        return ['Code' => '200010', 'Msg'=>lang('200010')];

        //原始提现密码是否错误
        if ($userinfo['cash_pwd'] != $old_pwd)
        return ['Code' => '200009', 'Msg'=>lang('200009')];

        $userModel->updateById($userinfo['id'],['cash_pwd'=>$new_pwd]);
        $userModel->delDetailDataCacheByUid($userinfo['uid']);

        //需要返回的数据体
        $Data['id']                   = $userinfo['id'];

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:dd7072fdb56159fdea1c56c8c23f2607*/

    /*api:6d24faeacf3004fbbb3d4691a67d0767*/
    /**
     * * 设置提现密码
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function setCashPwd($parame)
    {
        //主表数据库模型
        $dbModel                = model('bank_bind');

        //用户资料信息
        $userModel   = model('user_detail');
        $userinfo    = $userModel->getOneByUid($parame['uid']);

        //是否设置了提现密码
        if (!empty($userinfo['cash_pwd']))
        return ['Code' => '200011', 'Msg'=>lang('200011')];

        $cash_pwd               = isset($parame['cash_pwd']) ? $parame['cash_pwd'] : '';
        $rep_pwd                = isset($parame['rep_pwd']) ? $parame['rep_pwd'] : '';
        if (!empty($cash_pwd) && md5($cash_pwd) !== md5($rep_pwd))
        return ['Code' => '200008', 'Msg'=>lang('200008')];

        //密码设置
        $userModel->updateById($userinfo['id'],['cash_pwd'=>$dbModel->password($cash_pwd)]);
        $userModel->delDetailDataCacheByUid($userinfo['uid']);

        //需要返回的数据体
        $Data['id']              = $parame['uid'];

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:6d24faeacf3004fbbb3d4691a67d0767*/

    /*api:39b517e262a046f2f6a36c0524a4f42d*/
    /**
     * * 手机短信找回提现密码
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function cashPwdBack($parame)
    {
        //主表数据库模型
        $dbModel                = model('bank_bind');
        $password               = isset($parame['password']) ? $parame['password'] : '';
        $mobile                 = isset($parame['mobile']) ? $parame['mobile'] : '';
        $sms_code               = isset($parame['sms_code']) ? $parame['sms_code'] : '';
        $new_pwd                = isset($parame['new_pwd']) ? $parame['new_pwd'] : '';
        $rep_pwd                = isset($parame['rep_pwd']) ? $parame['rep_pwd'] : '';

        $userModel              = model('user_center');
        $userinfo               = $userModel->getOneByid($parame['uid']);

        $new_pwd                = isset($parame['new_pwd']) ? $parame['new_pwd'] : '';
        $rep_pwd                = isset($parame['rep_pwd']) ? $parame['rep_pwd'] : '';
        if (!empty($new_pwd) && md5($new_pwd) !== md5($rep_pwd))
        return ['Code' => '200008', 'Msg'=>lang('200008')];

        if ($dbModel->password($password) !== $userinfo['password'])
        return ['Code' => '200012', 'Msg'=>lang('200012')];

        if (!Mobile_check($mobile,[1]))
        return ['Code' => '200013', 'Msg'=>lang('200013')];

        if(empty($userinfo['mobile']) || $mobile !== $userinfo['mobile'])
        return ['Code' => '200014', 'Msg'=>lang('200014')];

        //检验验证码 定义校验验证码参数
        $checkParame                = [];
        $checkParame['scene']       = 2;
        $checkParame['sms_code']    = $sms_code;
        $checkParame['mobile']      = $mobile;
        $checkParame['check_type']  = 1;

        //检验验证码
        $checkCode                  = $this->helper($checkParame,'Api','Sms','checkCode');
        if ($checkCode['Code'] !== '000000')
        return ['Code' => '200015', 'Msg'=>lang('200015',[$checkCode['Msg']])];

        //用户资料信息
        $userModel                  = model('user_detail');
        $userinfo                   = $userModel->getOneByUid($parame['uid']);

        //密码设置
        $userModel->updateById($userinfo['id'],['cash_pwd'=>$dbModel->password($new_pwd)]);
        $userModel->delDetailDataCacheByUid($userinfo['uid']);

        //删除验证码
        $this->helper(['id'=>$checkCode['Data']['smsid']],'Api','Sms','delCode');

        //需要返回的数据体
        $Data['id']              = $parame['uid'];

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:39b517e262a046f2f6a36c0524a4f42d*/

    /*api:7716a7ab92a0bc4cf3c0c7a68a805fd4*/
    /**
     * * 后台提现记录（后台）
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function cashListForAdmin($parame)
    {
        //主表数据库模型
        $dbModel                    = model('bank_cash');

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = 'bank_cash';

        //主表名称，可以为空，默认为main
        $modelParame['MainAlias']   = 'main';

        //主表待查询字段，可以为空，默认全字段
        $modelParame['MainField']   = [];

        //定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
        //Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
        $RelationTab                = [];
        $RelationTab['user_detail'] = array('Ralias'=>'ud','Ron'=>'ud.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

        $modelParame['RelationTab'] = $RelationTab;

        //接口数据
        $modelParame['apiParame']   = $parame;

        //检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
        $modelParame['whereFun']    = 'formatWhereDefault';

        //排序定义
        $modelParame['order']       = 'main.id desc';       
        
        //数据分页步长定义
        $modelParame['limit']       = isset($parame['limit']) ? $parame['limit'] : 10;

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

    /*api:7716a7ab92a0bc4cf3c0c7a68a805fd4*/

    /*接口扩展*/
}
