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

class Lottery extends Base
{
	private $dataValidate 		= null;
    private $mainTable          = 'category';
	
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
		$modelParame['limit']		= $this->apidoc == 2 ? 1 : 10;

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
        $id                 = isset($parame['lotteryid']) ? intval($parame['lotteryid']) : 0;
        if ($id <= 0) return ['Code' => '200001', 'Msg'=>lang('200001')];

        //数据详情
    	$info 				= $dbModel->getOneById($id);

    	if (!empty($info) && $info['pid'] > 0 && $info['status'] == 1) {
    		
            //根据彩种ID选择对应的数据库查询
            //格式为数组
            $info                   = $info->toArray();

            $lottery                = new \app\api\lottery\Lottery($info['id']);

            //已经开奖数据
            $parame                 = [];
            $parame['page']         = 1;
            $parame['limit']        = 21;
            $list                   = $lottery->getLotteryList($parame);

            //待开奖数据
            $stayOpen               = isset($list[0]) ? $list[0] : [];
            //unset($list[0]);

            //sort($list);
            //自行对数据格式化输出
            //...
            if ($id == 100) {
                $table_name                 = 'lottery_hk6';
                $cacheDataKey               = 'updateData_'.$table_name.'_opentimestamp_' . $id;
                $opentimestamp              = cache($cacheDataKey);
                $stayOpen['opentimestamp']  = $opentimestamp;
            }
            
            $data                       = [];
            $data['lottery_id']         = $info['id'];
            $data['lottery_limit']      = $lottery->getLotteryTime();
            $data['term_number']        = $stayOpen['term_number'];
            $data['nearfuture_code']    = isset($list[1]['opencode']) ? $list[1]['opencode'] : '';
            $data['opentimestamp']      = $stayOpen['opentimestamp'];
            $data['lottery_history']    = $list;

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$data];
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

    /*api:f7cbd4d84eec5b63f3edb55034775e00*/
    /**
     * * 购彩选号
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function selectNumber($parame)
    {
        //主表数据库模型
        $dbModel                = model($this->mainTable);

        //数据ID
        $id                 = isset($parame['lotteryid']) ? intval($parame['lotteryid']) : 0;
        if ($id <= 0) return ['Code' => '200001', 'Msg'=>lang('200001')];

        $lottery_rule        = isset($parame['lottery_rule']) ? $parame['lottery_rule'] : '';
        $rules               = model('lottery_rule')->getLotterRule($lottery_rule);
        if (empty($rules))  return ['Code' => '200002', 'Msg'=>lang('200002')];            

        $price               = isset($parame['price']) ? $parame['price'] : 0;
        $unit                = isset($parame['unit']) ? $parame['unit'] : 1;
        $rebate              = isset($parame['rebate']) ? $parame['rebate'] : 0;
        $user_id             = isset($parame['uid']) ? $parame['uid'] : 0;

        $maxRebate           = 13;
        //返利值是否正确
        if ($rebate < 0 || $rebate > 13)
        return ['Code' => '200013', 'Msg'=>lang('200013')];

        $LotteryRule         = new \app\api\lottery\LotteryRule($id);
        $Lottery             = new \app\api\lottery\Lottery($id);

        //计算用户下注数
        $cacheKey   = 'selectNumber2_'.md5($lottery_rule.'_'.$id.'_'.$parame['uid'].$parame['hashid']);
        $betsData   = cache($cacheKey);
        $bets       = (!empty($betsData['bets']) && isset($betsData['bets'][0])) ? $betsData['bets'] : [0,[]];
        if ($bets[0] <= 0) return ['Code' => '200004', 'Msg'=>lang('200004')];

        $num5                    = $betsData['num5'];
        $num4                    = $betsData['num4'];
        $num3                    = $betsData['num3'];
        $num2                    = $betsData['num2'];
        $num1                    = $betsData['num1'];

        //金额换算
        $price                  = ($price*1)*$unit;
        if ($price <= 0)
        return ['Code' => '200015', 'Msg'=>lang('200015')];

        //投注总额
        $money                      = $bets[0]*$price;
        $userModel                  = model('user_detail');
        $userinfo                   = $userModel->getOneByUid($parame['uid']);
        $user_level                 = getUserLevel($userinfo['account_all']);
        $level                      = isset($user_level[0]) ? $user_level[0] : 0;
        $rate                       = isset($user_level[1]) ? $user_level[1] : 0;
        $pay_money                  = $rate > 0 ? $money*$rate : $money;

        $updata                     = [];
        $updata['uid']              = $parame['uid'];
        $updata['num1']             = $num1;
        $updata['num2']             = $num2;
        $updata['num3']             = $num3;
        $updata['num4']             = $num4;
        $updata['num5']             = $num5;
        $updata['lottery_id']       = $id;
        $updata['catid']            = $rules['pid'];
        $updata['status']           = 1;//号码选择，未投注
        $updata['create_time']      = time();
        $updata['bets']             = $bets[0];
        $updata['price']            = $price;
        $updata['money']            = $pay_money;
        $updata['order_money']      = $money;
        //$updata['odds']             = json_encode($odds);
        $updata['unit']             = $unit;
        $updata['rebate']           = $rules['pid'] == 88 ? $rebate : 0;
        $updata['rules']            = $lottery_rule;
        $updata['rules_str']        = !empty($rules['title']) ? $rules['title'] : '';
        $updata['select_code']      = json_encode($bets[1]);
        $updata['lottery_title']    = !empty($rules['lottery_title']) ? $rules['lottery_title'] : '';

        $res                        = model('lottery_order')->addData($updata);
        cache($cacheKey,null);

        //需要返回的数据体
        $Data['id']                   = $res['id'];

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:f7cbd4d84eec5b63f3edb55034775e00*/

    /*api:8c67f0c72b9914a112286f3b49baf97b*/
    /**
     * * 投注单列表
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function betsLists($parame)
    {
        //主表数据库模型
        $dbModel                = model($this->mainTable);

        //根据彩种ID获取用户选号列表
        $lottery_id             = isset($parame['lottery_id']) ? intval($parame['lottery_id']) : 0;
        $lottery                = new \app\api\lottery\Lottery($lottery_id);

        //最近一期数据
        $list                   = $lottery->getLotteryList(['limit'=>1]);

        //待开奖数据
        $lottery_info           = isset($list[0]) ? $list[0] : [];
        $orderList              = model('lottery_order')->getLotteryOrderListByLotteryid($lottery_id,$parame['uid']);
        $pay_money              = 0;
        $order_money            = 0;

        if (!empty($orderList)) {
            foreach ($orderList as $key => $value) {
                $nums            = [$value['num5'],$value['num4'],$value['num3'],$value['num2'],$value['num1']];
                $orderList[$key]['nums']     = trim(implode('|',$nums),'|');

                $pay_money      += $value['money'];
                $order_money    += $value['order_money'];
            }
        }

        $userModel                  = model('user_detail');
        $userinfo                   = $userModel->getOneByUid($parame['uid']);
        $user_level                 = getUserLevel($userinfo['account_all']);
        $level                      = isset($user_level[0]) ? $user_level[0] : 0;
        //$rate                       = isset($user_level[1]) ? $user_level[1] : 0;
        //$pay_money                  = $rate > 0 ? $pay_money*$rate : $pay_money;

        //需要返回的数据体
        $Data                       = [];
        $Data['lottery_id']         = $lottery_id;
        $Data['lottery_limit']      = $lottery->getLotteryTime();
        $Data['term_number']        = $lottery_info['term_number'];
        $Data['expect']             = $lottery_info['expect'];
        $Data['opentimestamp']      = $lottery_info['opentimestamp'];
        $Data['lists']              = $orderList;
        $Data['user_level']         = $level;
        $Data['pay_money']          = sprintf("%.2f",$pay_money);
        $Data['order_money']        = sprintf("%.2f",$order_money);

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:8c67f0c72b9914a112286f3b49baf97b*/

    /*api:073956fa60eb5d41d122e529e0fe245e*/
    /**
     * * 删除投注
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function betsDel($parame)
    {
        //主表数据库模型
        $dbModel                = model('lottery_order');

        $id                     = isset($parame['id']) ? intval($parame['id']) : 0;
        $lottery_id             = isset($parame['lottery_id']) ? intval($parame['lottery_id']) : 0;
        $deltype                = isset($parame['deltype']) ? intval($parame['deltype']) : 0;
        
        switch ($deltype) {
            case 1:
                $delCount               = $dbModel->delData($id); break;
            case 2:
                $orderList              = $dbModel->getLotteryOrderListByLotteryid($lottery_id,$parame['uid']);
                $ids                    = [];
                $ids[]                  = 0;
                if (!empty($orderList)) {
                    foreach ($orderList as $key => $value) {
                        $ids[]          = $value['id'];
                    }
                }

                $ids                    = implode(',',$ids);
                $delCount               = $dbModel->delData($ids);
                break;
            default: return ['Code' => '200010', 'Msg'=>lang('200010')];break;
        }

        //需要返回的数据体
        $Data['count']                  = $delCount;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:073956fa60eb5d41d122e529e0fe245e*/

    /*api:2352754a7e9e151eb789c0d2a869bbfc*/
    /**
     * * 期号校验
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function termNumberCheck($parame)
    {
        //主表数据库模型
        $dbModel                = model($this->mainTable);

        //根据彩种ID获取最新一期彩票信息
        $lottery_id             = isset($parame['lottery_id']) ? intval($parame['lottery_id']) : 0;
        $lottery                = new \app\api\lottery\Lottery($lottery_id);

        //最近一期数据
        $list                   = $lottery->getLotteryList(['limit'=>1]);

        //待开奖数据
        $lottery_info           = isset($list[0]) ? $list[0] : [];
        
        //需要返回的数据体
        $Data                   = [];
        $Data['expect']         = $lottery_info['expect'];

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:2352754a7e9e151eb789c0d2a869bbfc*/

    /*api:38438ef47169e464eee3ff4b6c17bf4e*/
    /**
     * * 购彩下注
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function bets($parame)
    {
        //主表数据库模型
        $dbModel                = model('lottery_order');

        $lottery_id             = isset($parame['lottery_id']) ? intval($parame['lottery_id']) : 0;
        $orderList              = model('lottery_order')->getLotteryOrderListByLotteryid($lottery_id,$parame['uid']);
        $money                  = 0;
        $updata                 = [];

        if (!empty($orderList)) {

            //代理ID
            $agent_id               = 0;
            if ($parame['uid'] >0){
                $userInfo           = model('user_detail')->getOneByUid($parame['uid']);
                $userInfo           = !empty($userInfo) ? $userInfo->toArray() : [];
                if (isset($userInfo['invitation_code']) && !empty($userInfo['invitation_code'])) {
                    $agent_id       = get_invitation_uid($userInfo['invitation_code']);
                }
            }

            //根据彩种ID获取最新一期彩票信息
            $lottery                = new \app\api\lottery\Lottery($lottery_id);
            $list                   = $lottery->getLotteryList(['limit'=>1]);
            $lottery_info           = isset($list[0]) ? $list[0] : [];

            foreach ($orderList as $key => $value)
            {
                //计算总额
                $money           += $value['order_money'];
                $updata[]        = [
                    'id'=>$value['id'],
                    'status'=>2,
                    'agent_id'=>$agent_id,
                    'expect'=>$lottery_info['expect'],
                    'term_number'=>$lottery_info['term_number'],
                    'opentimestamp'=>$lottery_info['opentimestamp']
                ];
            }

            $userModel          = model('user_detail');

            //校验用户余额是否充足
            $userinfo           = $userModel->getOneByUid($parame['uid']);
            $user_level         = getUserLevel($userinfo['account_all']);
            $rate               = isset($user_level[1]) ? $user_level[1] : 0;
            $money              = $rate > 0 ? $money*$rate : $money;

            if (empty($userinfo)||$userinfo['account']<$money)
            return ['Code' => '200008', 'Msg'=>lang('200008')];

            //减少金额
            $data                  = [];
            $data['account']       = $userinfo['account']-$money;
            $data['account_all']   = $userinfo['account_all']+$money;
            $userModel->updateById($userinfo['id'],$data);
            $userModel->delDetailDataCacheByUid($parame['uid']);

            //计算分销
            $this->distribution($userinfo['invitation_code'],$money,$parame['uid']);

            //修改投注状态
            $dbModel->updateByIds($updata);

            //写日志
            model('user_account_log')->addAccountLog($parame['uid'],$money,'彩票投注',2,1);

            return ['Code' => '000000', 'Msg'=>lang('200012')];
        }

        return ['Code' => '200011', 'Msg'=>lang('200011')];
    }

    /*api:38438ef47169e464eee3ff4b6c17bf4e*/

    /*api:b88cb069f61a234ce2b9053751df948f*/
    /**
     * * 历史开奖查询列表接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function historyList($parame)
    {
        //主表数据库模型
        $dbModel                    = model($this->mainTable);
        $lottery_id                 = isset($parame['lottery_id']) ? intval($parame['lottery_id']) : 0;
        $Data                       = [];

        //最近一期彩票信息
        $lottery                    = new \app\api\lottery\Lottery($lottery_id);
        $list                       = $lottery->getLotteryList(['limit'=>1]);
        $near_info                  = isset($list[0]) ? $list[0] : [];

        //获取彩种信息
        $lottery_info               = $dbModel->getOneByid($lottery_id);
        $lottery_info               = !empty($lottery_info) ? $lottery_info->toArray() : [];

        if (!empty($near_info) && !empty($lottery_info))
        {
            $lottery_info['icon']       = isset($lottery_info['icon']) ? get_cover($lottery_info['icon'],'path') : '';
            $lottery_info['expect']     = isset($near_info['expect']) ? $near_info['expect'] : 0;
            $lottery_info['opentime']   = isset($near_info['opentimestamp']) ? $near_info['opentimestamp'] : 0;
        }
        
        //历史开奖信息
        $lists                       = $lottery->getLotteryList(['limit'=>21]);

        $Data['lottery_info']       = $lottery_info;
        $Data['lists']              = $lists;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:b88cb069f61a234ce2b9053751df948f*/

    /*api:d41a14d4c736a3cd2f54cdb07cab98e0*/
    /**
     * * 开奖结果列表接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function resList($parame)
    {
        //主表数据库模型
        $dbModel                    = model($this->mainTable);

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = $this->mainTable;

        //主表名称，可以为空，默认为main
        $modelParame['MainAlias']   = 'main';

        //主表待查询字段，可以为空，默认全字段
        $modelParame['MainField']   = [];

        //定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
        //Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
        $RelationTab                = [];
        //$RelationTab['member']        = array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

        $modelParame['RelationTab'] = $RelationTab;

        $parame['ctype']            = 1;
        $parame['ispid']            = 1;
        $parame['isstatus']         = 1;

        //接口数据
        $modelParame['apiParame']   = $parame;

        //检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
        $modelParame['whereFun']    = 'formatWhereDefault';

        //排序定义
        $modelParame['order']       = 'main.sort desc';       
        
        //数据分页步长定义
        $modelParame['limit']       = 100;

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
            foreach($data as $k=>$v)
            {
                $lottery                = new \app\api\lottery\Lottery($v['id']);
                $lately                 = $lottery->getLotteryInfoLatelyOpen();
                $data[$k]['icon']       = get_cover($v['icon'],'path');
                $data[$k]['opencode']   = $lately['opencode'];
                $data[$k]['expect']     = $lately['expect'];
            }
        }

        $lists['lists']             = $data;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /*api:d41a14d4c736a3cd2f54cdb07cab98e0*/

    /*api:e127cb94a3203cc117abf7c5979515f3*/
    /**
     * * 彩票规则详情
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function ruleinfo($parame)
    {
        //主表数据库模型
        $dbModel                = model($this->mainTable);
        $lottery_rule_model     = model('lottery_rule');

        $user_id                = isset($parame['uid']) ? $parame['uid'] : 0;
        $rule_tag               = isset($parame['rule_tag']) ? $parame['rule_tag'] : '';
        $rules                  = !empty($rule_tag) ? $lottery_rule_model->getLotterRule($rule_tag) : [];

        $agentOdds              = [];
        $agent_id               = 0;
        if ($user_id >0){
            $userInfo           = model('user_detail')->getOneByUid($user_id);
            $userInfo           = !empty($userInfo) ? $userInfo->toArray() : [];
            if (isset($userInfo['invitation_code']) && !empty($userInfo['invitation_code'])) {
                $agent_id       = get_invitation_uid($userInfo['invitation_code']);
            }

            //判断他的上级是否是代理
            if ($agent_id > 0) {

                $agentInfo          = model('user_detail')->getOneByUid($agent_id);
                $agent_lottery_id   = !empty($agentInfo['lottery_id'])?explode(',',$agentInfo['lottery_id']):[];
                if (in_array($rules['pid'],$agent_lottery_id)) {
                    $agent_id       = model('user_group_access')->checkGroupByUidAndGid($agent_id,3);
                }else{
                    $agent_id       = 0;
                }
            }
        }

        if ($agent_id > 0) {
            //获取代理独有赔率
            $agentOdds  = model('lottery_odds')->getLotteryAgentOddsByUid($agent_id,$rule_tag);
        }

        if (!empty($rules)) {

            $odds                = $lottery_rule_model->formatRuleOdds($rules,$agentOdds);

            $rules['odds']       = $odds['odds'];
            $rules['odds2']      = $odds['odds2'];
            $rules['rebate']     = 0.13;
        }

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$rules];
    }

    /*api:e127cb94a3203cc117abf7c5979515f3*/

    /*api:9806dd2899e8c705bc33b1c38a384229*/
    /**
     * * 选号
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function selectNumber2($parame)
    {
        //主表数据库模型
        $dbModel                = model($this->mainTable);

        //数据ID
        $id                 = isset($parame['lotteryid']) ? intval($parame['lotteryid']) : 0;
        if ($id <= 0) return ['Code' => '200001', 'Msg'=>lang('200001')];

        $lottery_rule        = isset($parame['lottery_rule']) ? $parame['lottery_rule'] : '';
        $rules               = model('lottery_rule')->getLotterRule($lottery_rule);
        if (empty($rules))  return ['Code' => '200002', 'Msg'=>lang('200002')];
        
        $num5      = isset($parame['number5']) ? str_replace('~','',$parame['number5']) : '';
        $num4      = isset($parame['number4']) ? str_replace('~','',$parame['number4']) : '';
        $num3      = isset($parame['number3']) ? str_replace('~','',$parame['number3']) : '';
        $num2      = isset($parame['number2']) ? str_replace('~','',$parame['number2']) : '';
        $num1      = isset($parame['number1']) ? str_replace('~','',$parame['number1']) : '';

        //转换数字对应的汉字 根据规则ID 为了考虑前端传的是数字 主要针对num5
        $num5      = $this->format_nums($num5,$lottery_rule);
        $num4      = $this->format_nums($num2,$lottery_rule);
        $num3      = $this->format_nums($num3,$lottery_rule);
        $num2      = $this->format_nums($num2,$lottery_rule);
        $num1      = $this->format_nums($num1,$lottery_rule);

        $cacheKey  = 'selectNumber2_'.md5($lottery_rule.'_'.$id.'_'.$parame['uid'].$parame['hashid']);
        cache($cacheKey,null);

        $LotteryRule         = new \app\api\lottery\LotteryRule($id);
        $Lottery             = new \app\api\lottery\Lottery($id);

        //计算用户下注数
        $bets                = $LotteryRule->getLotteryBetNumber($lottery_rule,$num5,$num4,$num3,$num2,$num1);
        if ($bets[0] <= 0) return $LotteryRule->getError();
        if ($bets[0] >= 22000) return ['Code' => '200016', 'Msg'=>lang('200016')];

        $betsData                = [];
        $betsData['num5']        = $num5;
        $betsData['num4']        = $num4;
        $betsData['num3']        = $num3;
        $betsData['num2']        = $num2;
        $betsData['num1']        = $num1;
        $betsData['bets']        = $bets;

        cache($cacheKey,$betsData);

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['id'=>$bets[0]]];
    }

    /*api:9806dd2899e8c705bc33b1c38a384229*/

    /*api:d7231e54280ba2a4e7a4cc04663fdabe*/
    /**
     * * 注单
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function orderList($parame)
    {
        //主表数据库模型
        $dbModel                    = model('lottery_order');

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = 'lottery_order';

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
        $modelParame['limit']       = 15;

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
            foreach($data as $k=>$v)
            {
                $rules_str               = explode('-',$v['rules_str']);
                $data[$k]['create_time'] = date('Y/m/d H:i',$v['create_time']);
                $data[$k]['rules_str']   = $v['lottery_title'] . '-' . $rules_str[0] . '-' . $rules_str[1];

                $scode                   = [];
                if (!empty($v['select_code'])) {
                    $select_code = json_decode($v['select_code'],true);
                    $select_code = is_string($select_code) ? [$select_code] : $select_code;
                    if (isset($select_code[0]) && is_array($select_code[0])) {
                        foreach ($select_code as $kk => $vv) {
                            $scode[]    = implode(',',$vv);
                        }
                    }else{
                        $scode          = $select_code;
                    }
                   
                    $scode       = is_array($scode) ? $scode : [$scode];
                }
                
                $data[$k]['select_code'] = !empty($scode) ? implode('|',$scode) : '';
                $data[$k]['money']       = $v['order_money'];
            }
        }

        $lists['lists']             = $data;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /*api:d7231e54280ba2a4e7a4cc04663fdabe*/

    /*api:e286c620a12fb16aaf1fb0b683c9b06a*/
    /**
     * * 注单列表（后台管理）
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function orderListForAdmin($parame)
    {
        //主表数据库模型
        $dbModel                    = model('lottery_order');

        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = 'lottery_order';

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
        $modelParame['whereFun']    = 'orderListForAdmin';

        //排序定义
        $modelParame['order']       = 'main.id desc';       
        
        //数据分页步长定义
        $modelParame['limit']       = 15;

        //数据分页页数定义
        $modelParame['page']        = (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

        //数据缓存是时间，默认0 不缓存 ,单位秒
        $modelParame['cacheTime']   = 0;

        //列表数据
        $lists                      = $dbModel->getPageList($modelParame);

        //数据格式化
        $data                       = (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];

        if (!empty($data)) {

            $status = ['未知','已投注,未支付','已投注,已支付','已开奖'];
            //自行定义格式化数据输出
            foreach($data as $k=>$v)
            {
                $data[$k]['create_time'] = date('Y-m-d H:i:s');

                $nums               = [$v['num5'],$v['num4'],$v['num3'],$v['num2'],$v['num1']];
                foreach ($nums as $key => $value) {
                    if (empty($value)) unset($nums[$key]);
                }

                $data[$k]['nums']               = "<span style='width:350px;height:33px;display:block;overflow-y:auto;'>".implode('|',$nums)."</span>";

                $data[$k]['iswin']          = $v['iswin'] == 1 ? '中奖' : '未中奖';
                if (empty($v['opencode']) || empty($v['opentimestamp'])) {
                    $data[$k]['opencode']       = '未开奖';
                    $data[$k]['opentimestamp']  = '未开奖';
                    $data[$k]['iswin']          = '未开奖';
                }

                $data[$k]['status']          = $status[$v['status']];
                $data[$k]['nickname']        = $v['nickname'].'('.$v['id'].')';
            }
        }

        $lists['lists']             = $data;

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /*api:e286c620a12fb16aaf1fb0b683c9b06a*/

    /*api:9ce6cd7c1c84ea4fda791f2c8dabfd41*/
    /**
     * * 测试中奖数据
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function testwin($parame)
    {
        $orderModle      = model("lottery_order");
        $ruleModle       = model("lottery_rule");
        $orderInfo       = $orderModle->getOneByid($parame['id']);
        $this->lotteryConfig      = config('lottery.');

        if (!empty($orderInfo))
        {
            $value       = $orderInfo->toArray();

            //获取表名
            $lottery_table      = '';
            if (isset($this->lotteryConfig['lottery_tag'][$value['lottery_id']])) {
                $lottery_table  = $this->lotteryConfig['lottery_tag'][$value['lottery_id']];
            }else{
                return ['Code' => '00000001', 'Msg'=>lang('00000002')];
            }

            $lotteryModel   = model($lottery_table);
            $lotteryInfo    = $lotteryModel->getLotteryInfoByExpect($value['expect']);
            $lotteryInfo    = !empty($lotteryInfo) ? $lotteryInfo : [];

            if (empty($lotteryInfo)||empty($lotteryInfo['opencode'])||$lotteryInfo['opentimestamp'] >= time()){
                return ['Code' => '00000001', 'Msg'=>lang('00000003')];
            }

            //防止多次执行
            /*$cacheKey       = 'lottery_order_id_create_time_'.$value['id'].$value['create_time'];
            $cacheVal       = $value['id'].$value['create_time'];
            $iscache        = cache($cacheKey);
            if (!empty($iscache) && $iscache == $cacheVal) continue;
            cache($cacheKey,$cacheVal);*/

            //执行中奖判断
            $opencode       = $lotteryInfo['opencode'];
            $opentimestamp  = $lotteryInfo['opentimestamp'];
            $rules          = $value['rules'];
            $select_code    = $value['select_code'];

            $lottery        = new \app\api\lottery\Lottery(0);
            $isWin          = $lottery->winningPrize($opencode,$opentimestamp,$rules,$select_code);
            print_r([$isWin,$opencode,$rules,1]);exit;
            //return ['Code' => '00000002', 'Msg'=>lang('00000002')];
            //中奖 计算中奖金额
            if ($isWin[0] > 0 && !empty($isWin[1]))
            {
                $lotteryRule   = $ruleModle->getLotterRule($rules);
                //wr($lotteryRule);return ['Code' => '00000001', 'Msg'=>lang('00000001')];
                //计算赔率
                $lottery->calculatingOdds($value,$isWin,$lotteryRule);
            }
            return ['Code' => '00000001', 'Msg'=>lang('00000001')];
            //更改订单信息
            $updataOrder                    = [];
            $updataOrder['status']          = 3;
            $updataOrder['win_bets']        = $isWin[0];
            $updataOrder['expect']          = $lotteryInfo['expect'];
            $updataOrder['opencode']        = $opencode;
            $updataOrder['opentimestamp']   = $opentimestamp;
            $updataOrder['win_code']        = json_encode($isWin[1]);
            $updataOrder['iswin']           = $isWin > 0 ? 1 : 0;

            $orderModle->updateById($value['id'],$updataOrder);

        }else{
            return ['Code' => '00000001', 'Msg'=>lang('00000001')];
        }

        //需要返回的数据体
        $Data                   = ['id'=>1];
        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:9ce6cd7c1c84ea4fda791f2c8dabfd41*/

    /*接口扩展*/

    private function addDistributionMoney($uid=0,$money=0)
    {
        if ($uid > 0)
        {   
            $userModel      = model('user_detail');
            $userinfo       = $userModel->getOneByUid($uid);
            $userinfo       = !empty($userinfo) ? $userinfo->toArray() : [];

            //增加累计收益金额
            $data                  = [];
            $data['account']       = $userinfo['account']+$money;
            $data['profit_all']    = $userinfo['profit_all']+$money;
            $userModel->updateById($userinfo['id'],$data);
            $userModel->delDetailDataCacheByUid($userinfo['uid']);

            //写日志
            model('user_account_log')->addAccountLog($userinfo['uid'],$money,'分销返佣',1,5);
        }
    }

    //三级分成
    private function distribution($invitation_code='',$money=0,$uid=0)
    {
        if (empty($invitation_code) || $money <= 0 || $uid <= 0) return false;

        $rate1          = config('system_config.fen_first_rate');
        $rate2          = config('system_config.fen_second_rate');
        $rate3          = config('system_config.fen_third_rate');
        $rate1          = !empty($rate1) ? ($rate1*1)/100 : 0;
        $rate2          = !empty($rate2) ? ($rate2*1)/100 : 0;
        $rate3          = !empty($rate3) ? ($rate3*1)/100 : 0;

        //待分成金额
        $money          = !empty($money) ? $money*1 : 0;
        $updata         = [];

        $userModel      = model('user_detail');

        //一级分销ID
        $uid1           = get_invitation_uid($invitation_code);
        $userinfo1      = $userModel->getOneByUid($uid1);
        $userinfo1      = !empty($userinfo1) ? $userinfo1->toArray() : [];

        //一级分成
        $userinfo2      = [];
        if (!empty($userinfo1) && $rate1 > 0) {
            $money1     = $money*$rate1;
            $updata[]   = [
            'uid'=>$userinfo1['uid'],
            'create_time'=>time(),
            'money'=>$money1,
            'uid1'=>$uid
            ];

            //增加累计收益金额
            $data                  = [];
            $data['account']       = $userinfo1['account']+$money1;
            $data['profit_all']    = $userinfo1['profit_all']+$money1;
            $userModel->updateById($userinfo1['id'],$data);
            $userModel->delDetailDataCacheByUid($userinfo1['uid']);

            //写日志
            model('user_account_log')->addAccountLog($userinfo1['uid'],$money1,'分销返佣',1,5);

            //二级分销ID
            $uid2       = get_invitation_uid($userinfo1['invitation_code']);
            $userinfo2  = $userModel->getOneByUid($uid2);
            $userinfo2  = !empty($userinfo2) ? $userinfo2->toArray() : [];
        }

        //二级分成
        $userinfo3      = [];
        if (!empty($userinfo2) && $rate2 > 0) {
            $money2     = $money*$rate2;
            $updata[]   = [
            'uid'=>$userinfo2['uid'],
            'create_time'=>time(),
            'money'=>$money2,
            'uid1'=>$uid
            ];

            //增加累计收益金额
            $data                  = [];
            $data['account']       = $userinfo2['account']+$money2;
            $data['profit_all']    = $userinfo2['profit_all']+$money2;
            $userModel->updateById($userinfo2['id'],$data);
            $userModel->delDetailDataCacheByUid($userinfo2['uid']);

            //写日志
            model('user_account_log')->addAccountLog($userinfo2['uid'],$money2,'分销返佣',1,5);

            //三级分销ID
            $uid3       = get_invitation_uid($userinfo2['invitation_code']);
            $userinfo3  = $userModel->getOneByUid($uid3);
            $userinfo3  = !empty($userinfo3) ? $userinfo3->toArray() : [];
        }

        //三级分成
        if (!empty($userinfo3) && $rate3 > 0) {
            $money3     = $money*$rate3;
            $updata[]   = [
            'uid'=>$userinfo3['uid'],
            'create_time'=>time(),
            'money'=>$money3,
            'uid1'=>$uid
            ];

            //增加累计收益金额
            $data                  = [];
            $data['account']       = $userinfo3['account']+$money3;
            $data['profit_all']    = $userinfo3['profit_all']+$money3;
            $userModel->updateById($userinfo3['id'],$data);
            $userModel->delDetailDataCacheByUid($userinfo3['uid']);

            //写日志
            model('user_account_log')->addAccountLog($userinfo3['uid'],$money3,'分销返佣',1,5);
        }

        if (!empty($updata)) {
            model('user_distribution')->saveAll($updata);
        }

        return true;
    }

    private function getlottery($parame)
    {   
        $cacheKey           = 'getlottery_key_3s';
        $request_time       = cache($cacheKey);
        if (!empty($request_time) && $request_time > time()){
            echo "请求频率过快，请求newly间隔0.4843秒小于3秒";exit;
        }

        $code               = isset($parame['code']) ? $parame['code'] : '';
        $data               = [];
        $backdata['rows']   = 0;
        $backdata['code']   = $code;
        $backdata['info']   = '接口调用频率为3秒钟';
        $backdata['data']   = $data;
        if (!in_array($code,['ffssc','3fssc'])) {
            echo json_encode($backdata);exit;
        }

        if ($code == 'ffssc') {
            $dbModel   = model('lottery_ssc1');
        }

        if ($code == '3fssc') {
            $dbModel   = model('lottery_ssc3');
        }

        $list               = $dbModel->getListByLimitTime();
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                unset($list[$key]['id']);
            }
        }

        $backdata['rows']   = count($list);
        $backdata['data']   = $list;
        cache($cacheKey,time()+3);
        echo json_encode($backdata);exit;
    }

    private function format_nums($nums,$lottery_rule)
    {   
        if (strlen($nums) <= 0) return $nums;

        $temp       = [];
        if (in_array($lottery_rule,['88-7-1','88-7-2','88-7-3','88-7-4','88-7-5','88-7-6','88-7-7','88-7-8','88-7-9','88-7-10'])) {
            $temp    = [1=>'龙',2=>'虎',3=>'和'];
        }elseif (in_array($lottery_rule,['88-8-1','88-8-2','88-8-3','88-8-4','88-8-5','88-8-6','88-8-7'])) {
            $temp    = [1=>'大',2=>'小',3=>'单',4=>'双'];
        }

        if (!empty($temp)) {
            $tnum1  = explode(',',$nums);
            $tnum2  = [];
            foreach ($tnum1 as $key => $value) $tnum2[]    = $temp[$value];
            $nums   = implode(',',$tnum2);
        }

        wr(['ssss1',$nums,$lottery_rule]);
        return $nums;
    }
}
