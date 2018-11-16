<?php
/**
 * 彩票数据
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\lottery;

use think\Controller;
use think\facade\Lang;

class Lottery extends Base
{   
    private $nowTime;
    private $lotteryConfig;
    public function __construct($lotteryid=0)
    {
      parent::__construct($lotteryid);
      $this->nowTime            = time();
      $this->lotteryConfig      = config('lottery.');
    }

    //获取开奖信息
    public function getLotteryInfo($parame=[])
    {
        if (!isset($this->lotteryConfig['lottery_tag'][$this->lotteryid])) return [];
        if (!isset($parame['expect'])) return [];

        $lottery_table    = $this->lotteryConfig['lottery_tag'][$this->lotteryid];
        $dbModel          = model($lottery_table);

        $info             = $dbModel->getLotteryInfoByExpect($parame['expect']);
        $info             = !empty($info) ? $info->toArray() : [];      
        
        return $info;
    }

    //获取最近一期开奖数据
    public function getLotteryInfoLatelyOpen()
    {
        if (isset($this->lotteryConfig['lottery_tag'][$this->lotteryid]))
        {
            $lottery_table  = $this->lotteryConfig['lottery_tag'][$this->lotteryid];
            return model($lottery_table)->getLotteryInfoLatelyOpen();
        }

        return [];
    }

    public function getNearInfoExpect()
    {
        $lottery_table      = '';
        if (isset($this->lotteryConfig['lottery_tag'][$this->lotteryid])) {
            
            $lottery_table  = $this->lotteryConfig['lottery_tag'][$this->lotteryid];
        }else{
            return [];
        }

        $dbModel       = model($lottery_table);

        $map           = [];
        $map[]         = ['opencode','neq',''];

        $open_id       = $dbModel->where($map)->order('opentimestamp desc')->limit(1)->value('id');
        
        $delay         = model('category')->where('id','=',$this->lotteryid)->value('delay');
        $delay         = !empty($delay) ? intval($delay) : 0;

        $map           = [];
        $map[]         = ['opentimestamp','gt',$this->nowTime - $delay];
        $map[]         = ['id','gt',$open_id];
        $map[]         = ['opencode','eq',''];

        $noOpenInfo                 = $dbModel->where($map)->order('opentimestamp asc')->find();

        return !empty($noOpenInfo) ? $noOpenInfo->toArray() : [];
    }
    //获取最近
    public function getLotteryList($parame=[])
    {
        $lottery_table      = '';
        if (isset($this->lotteryConfig['lottery_tag'][$this->lotteryid])) {
            
            $lottery_table  = $this->lotteryConfig['lottery_tag'][$this->lotteryid];
        }else{
            return [];
        }

        $delay                      = model('category')->where('id','=',$this->lotteryid)->value('delay');
        $delay                      = !empty($delay) ? intval($delay) : 0;

        $dbModel                    = model($lottery_table);
        
        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = $lottery_table;

        //主表名称，可以为空，默认为main
        $modelParame['MainAlias']   = 'main';

        //主表待查询字段，可以为空，默认全字段
        $modelParame['MainField']   = [];

        //接口数据
        $parame['optime']           = $this->nowTime - $delay;
        $modelParame['apiParame']   = $parame;

        //检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
        $modelParame['whereFun']    = 'formatWhereDefault2';

        //排序定义
        $modelParame['order']       = 'main.opentimestamp desc';       
        
        //数据分页步长定义
        $modelParame['limit']       = isset($parame['limit']) ? intval($parame['limit']) : 10;

        //数据分页页数定义
        $modelParame['page']        = (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

        //列表数据
        $lists                      = $dbModel->getPageList($modelParame);

        //数据返回
        return (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];
    }

    //获取开奖时间间隔
    public function getLotteryTime()
    {
        $limit_time       = 10;
        switch ($this->lotteryid) {
            case 89:  $limit_time      = 1;     break;
            case 90:  $limit_time      = 5;     break;
            case 92:
                $time_start1      = $this->format_lottery_limit('00:00:00');
                $time_end1        = $this->format_lottery_limit('02:00:00');
                $time_start3      = $this->format_lottery_limit('22:00:00');
                $time_end3        = $this->format_lottery_limit('23:59:59')+1;
                if ( ($this->nowTime >= $time_start1 && $this->nowTime <= $time_end1) || ($this->nowTime >= $time_start3 && $this->nowTime <= $time_end3) ) {
                    $limit_time       = 5;
                }
                break;
            case 97:  $limit_time      = 5;     break;
            case 100: $limit_time      = 60*24; break;
            case 104: $limit_time      = 9;     break;
            case 116: $limit_time      = 5;     break;
            default:  $limit_time      = 10;    break;
        }

        return $limit_time;
    }

    //开奖
    public function openPrize()
    {
        $orderModle      = model("lottery_order");
        $ruleModle       = model("lottery_rule");
        $orderList       = $orderModle->getLotteryOrderList();
        if (!empty($orderList)) {
            foreach ($orderList as $key => $value) {

                //获取表名
                $lottery_table      = '';
                if (isset($this->lotteryConfig['lottery_tag'][$value['lottery_id']])) {
                    $lottery_table  = $this->lotteryConfig['lottery_tag'][$value['lottery_id']];
                }else{
                    continue;
                }

                $lotteryModel   = model($lottery_table);
                $lotteryInfo    = $lotteryModel->getLotteryInfoByExpect($value['expect']);
                $lotteryInfo    = !empty($lotteryInfo) ? $lotteryInfo : [];
                if (empty($lotteryInfo) || empty($lotteryInfo['opencode']) || $lotteryInfo['opentimestamp'] >= time()){
                    continue;
                }

                //防止多次执行
                $cacheKey       = 'lottery_order_id_create_time_'.$value['id'].$value['create_time'];
                $cacheVal       = $value['id'].$value['create_time'];
                $iscache        = cache($cacheKey);
                if (!empty($iscache) && $iscache == $cacheVal) continue;
                cache($cacheKey,$cacheVal);

                //执行中奖判断
                $opencode       = $lotteryInfo['opencode'];
                $opentimestamp  = $lotteryInfo['opentimestamp'];
                $rules          = $value['rules'];
                $select_code    = get_select_code($value['select_code_id']);

                $isWin          = $this->winningPrize($opencode,$opentimestamp,$rules,$select_code);
                /*if ($rules == '99-15-1') {
                    return false;
                }*/
                //中奖 计算中奖金额
                $odds           = '';
                $win_umoney     = 0;
                $win_amoney     = 0;

                if ($isWin[0] > 0 && !empty($isWin[1]))
                {
                    $lotteryRule   = $ruleModle->getLotterRule($rules);
                    //计算赔率
                    $winData          = $this->calculatingOdds($value,$isWin,$lotteryRule);

                    $odds             = isset($winData[0]) ? $winData[0] : '';
                    $win_money        = isset($winData[1]) ? $winData[1] : 0;
                    $win_umoney       = isset($winData[2]) ? $winData[2] : 0;
                    $win_amoney       = isset($winData[3]) ? $winData[3] : 0;
                }else{

                    //没中奖 如果有代理 代理拿用户投注金额百分比
                    $rate          = config('system_config.agent_fen_rate');
                    $amoney        = !empty($rate) ? ($rate / 100)*$value['money'] : 0;
                    $aid           = !empty($value['agent_id']) ? $value['agent_id'] : 0;
                    
                    if ($aid > 0 && $amoney > 0)
                    {
                        $userModel         = model('user_detail');
                        $agentinfo         = $userModel->getOneByUid($aid);
                        $data              = [];
                        $data['account']   = $agentinfo['account']+$amoney;
                        $userModel->updateById($agentinfo['id'],$data);
                        $userModel->delDetailDataCacheByUid($aid);

                        //写日志
                        model('user_account_log')->addAccountLog($aid,$amoney,'代理返佣',1,5);

                        $updata    = [];
                        $updata[]  = ['uid'=>$aid,'create_time'=>time(),'money'=>$amoney,'uid1'=>$value['uid']];
                        model('user_distribution')->saveAll($updata);
                    }

                    //用户没中奖执行三级分销
                    $this->distribution($value['money'],$value['uid']);
                }

                //更改订单信息
                $updataOrder                    = [];
                $updataOrder['status']          = 3;
                $updataOrder['win_bets']        = $isWin[0];
                $updataOrder['win_umoney']      = $win_umoney;
                $updataOrder['win_amoney']      = $win_amoney;
                $updataOrder['expect']          = $lotteryInfo['expect'];
                $updataOrder['opencode']        = $opencode;
                $updataOrder['odds']            = $odds;
                $updataOrder['opentimestamp']   = $opentimestamp;
                $updataOrder['win_code']        = json_encode($isWin[1]);
                $updataOrder['iswin']           = $isWin[0] > 0 ? 1 : 0;

                del_select_code($value['select_code_id']);

                $orderModle->updateById($value['id'],$updataOrder);
            }
        }else{
            wr(".........无开奖信息.........\n\n");
        }

        wr(".........待开奖订单:".count($orderList).".........\n\n");
        return true;
    }

    public function calculatingOdds($value,$isWin,$lotteryRule)
    {
        $userModel       = model('user_detail');
        $oddsModel       = model('lottery_odds');
        $rebate          = $value['rebate'];
        $aid             = !empty($value['agent_id']) ? $value['agent_id'] : 0;
        $odds            = $lotteryRule['odds'];
        $odds_rebate     = $lotteryRule['odds_rebate'];
        $tag             = $lotteryRule['tag'];
        $pid             = $lotteryRule['pid'];
        $money           = 0;
        $umoney          = 0;
        $amoney          = 0;

        $agentOdds       = $oddsModel->getLotteryAgentOddsByUid($aid,$tag);
        
        //时时彩
        switch ($pid) {
            case 88://时时彩
                $moneyAndOdds       = sscOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];
                $umoney             = $money;
                break;
            case 96://PK拾
                $moneyAndOdds       = pk10OddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];
                if ($aid <= 0 || empty($agentOdds)) {
                    $umoney         = $money;
                }else{
                    $moneyAndOdds   = pk10OddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $umoney         = $moneyAndOdds[0];
                    $amoney         = ($money-$umoney)*1;
                }
                
                break;
            case 99://六合彩
                $moneyAndOdds       = hk6OddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,0,[],$value);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $umoney         = $money;
                }else{
                    $moneyAndOdds   = hk6OddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds,$value);
                    $umoney         = $moneyAndOdds[0];
                    $amoney         = ($money-$umoney)*1;
                }
                break;
            case 102://快3
                $moneyAndOdds       = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $umoney         = $money;
                }else{
                    $moneyAndOdds   = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $umoney         = $moneyAndOdds[0];
                    $amoney         = ($money-$umoney)*1;
                }
                break;
            case 108://11选5
                $moneyAndOdds       = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $umoney         = $money;
                }else{
                    $moneyAndOdds   = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $umoney         = $moneyAndOdds[0];
                    $amoney         = ($money-$umoney)*1;
                }
                break;
            case 115://PC蛋蛋
                $moneyAndOdds       = manyOddsMoneyPC($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $umoney         = $money;
                }else{
                    $moneyAndOdds   = manyOddsMoneyPC($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $umoney         = $moneyAndOdds[0];
                    $amoney         = ($money-$umoney)*1;
                }
                break;
            default: return [];break;
        }

        if ($money <= 0 || $umoney <= 0)  return [];

        if (in_array($pid,[96,99,102,108,115]))
        {
            //代理存在 需要分给代理一部分佣金 (时时彩不考虑代理)
            if ($aid > 0 && $amoney > 0)
            {   
                $agentinfo              = $userModel->getOneByUid($aid);
                $data                   = [];
                $data['account']        = $agentinfo['account']+$amoney;
                $data['cash_money']     = $agentinfo['cash_money']+$amoney;
                $userModel->updateById($agentinfo['id'],$data);
                $userModel->delDetailDataCacheByUid($aid);

                //写日志
                model('user_account_log')->addAccountLog($aid,$amoney,'代理赔率差佣金',1,5);

                $updata    = [];
                $updata[]  = ['uid'=>$aid,'create_time'=>time(),'money'=>$amoney,'uid1'=>$value['uid']];
                model('user_distribution')->saveAll($updata);
            }
        }

        $userinfo              = $userModel->getOneByUid($value['uid']);
        $data                  = [];
        $data['account']       = $userinfo['account']+$umoney;
        $data['cash_money']    = $userinfo['cash_money']+$umoney;
        $userModel->updateById($userinfo['id'],$data);
        $userModel->delDetailDataCacheByUid($value['uid']);
        
        //写日志
        model('user_account_log')->addAccountLog($value['uid'],$umoney,'彩票中奖',1,4);

        $odds                   = isset($moneyAndOdds[1]) ? $moneyAndOdds[1] : '';
        return [$odds,$money,$umoney,$amoney];
    }

    public function winningPrize($opencode,$opentimestamp,$rules,$select_code)
    {
        if (empty($opencode) || $opentimestamp >= time() || empty($rules) || empty($select_code))
        return [0,[]];
        
        $LotteryWin     = new \app\api\lottery\LotteryWin();
        //用户选择的号码组合
        //$select_code        = json_decode($select_code,true);
        if (empty($select_code)) return [0,[]];

        wr("\n\n==================".$rules."===================\n\n");
        switch ($rules) {
            case '88-1-1'://五星-直选-复试
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,5);break;
            case '88-1-2': //五星-直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,5);break;
            case '88-1-3'://五星-直选-组合
                return $LotteryWin->win_ssc_zuhe($opencode,$select_code,5,1);break;
            case '88-1-4'://五星-组选-组选120
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,5);break;
            case '88-1-5'://五星-组选-组选60
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,5);break;
            case '88-1-6'://五星-组选-组选30
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,5);break;
            case '88-1-7'://五星-组选-组选20
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,5);break;
            case '88-1-8'://五星-组选-组选10
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,5);break;
            case '88-1-9'://五星-组选-组选5
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,5);break;
            case '88-2-1'://二星-后二直选-复式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,2);break;
            case '88-2-2'://二星-后二直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,2);break;
            case '88-2-3'://二星-后二直选-直选和值
                return $LotteryWin->win_ssc_zxhz($opencode,$select_code,2);break;
            case '88-2-4'://二星-后二直选-直选跨度
                return $LotteryWin->win_ssc_zxkd($opencode,$select_code,2);break;
            case '88-2-5'://二星-后二直选-和值尾数
                return $LotteryWin->win_ssc_hzws($opencode,$select_code,2);break;
            case '88-2-6'://二星-后二组选-复式
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,2);break;
            case '88-2-7'://二星-后二组选-单式
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,2);break;
            case '88-2-8'://二星-前二直选-复式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,2,1);break;
            case '88-2-9'://二星-前二直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,2,1);break;
            case '88-2-10'://二星-前二直选-直选和值
                return $LotteryWin->win_ssc_zxhz($opencode,$select_code,2,1);break;
            case '88-2-11'://二星-前二直选-直选跨度
                return $LotteryWin->win_ssc_zxkd($opencode,$select_code,2,1);break;
            case '88-2-12'://二星-前二直选-和值尾数
                return $LotteryWin->win_ssc_hzws($opencode,$select_code,2,1);break;
            case '88-2-13'://二星-前二组选-复式
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,2,1);break;
            case '88-2-14'://二星-前二组选-单式
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,2,1);break;
            case '88-3-1'://定位胆-定位胆-定位胆
                return $LotteryWin->win_location_gall($opencode,$select_code);break;
            case '88-4-1'://不定位-三星不定位-后三一码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,3,0,1);break;
            case '88-4-2'://不定位-三星不定位-中三一码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,3,2,1);break;
            case '88-4-3'://不定位-三星不定位-前三一码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,3,1,1);break;
            case '88-4-4'://不定位-三星不定位-后三二码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,3,0,2);break;
            case '88-4-5'://不定位-三星不定位-中三二码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,3,2,2);break;
            case '88-4-6'://不定位-三星不定位-前三二码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,3,1,2);break;
            case '88-4-7'://不定位-四星不定位-前四一码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,4,1,1);break;
            case '88-4-8'://不定位-四星不定位-后四一码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,4,0,1);break;
            case '88-4-9'://不定位-四星不定位-前四二码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,4,1,2);break;
            case '88-4-10'://不定位-四星不定位-后四二码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,4,0,2);break;
            case '88-4-11'://不定位-四星不定位-前四三码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,4,1,3);break;
            case '88-4-12'://不定位-四星不定位-后四三码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,4,0,3);break;
            case '88-4-13'://不定位-五星不定位-五星一码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,5,0,1);break;
            case '88-4-14'://不定位-五星不定位-五星二码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,5,0,2);break;
            case '88-4-15'://不定位-五星不定位-五星三码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,5,0,3);break;
            case '88-4-16'://不定位-五星不定位-五星四码
                return $LotteryWin->win_notlocation_gall($opencode,$select_code,5,0,4);break;
            case '88-5-1'://任选-任二-复式
                return $LotteryWin->win_rx_sxyz($opencode,$select_code,2);break;
            case '88-5-2'://任选-任二-单式
                return $LotteryWin->win_rx_sxyz($opencode,$select_code,2);break;
            case '88-5-3'://任选-任二-组选
                return $LotteryWin->wx_rx_bxsx($opencode,$select_code,2);break;
            case '88-5-4'://任选-任三-复式
                return $LotteryWin->win_rx_sxyz($opencode,$select_code,3);break;
            case '88-5-5'://任选-任三-单式
                return $LotteryWin->win_rx_sxyz($opencode,$select_code,3);break;
            case '88-5-6'://任选-任三-组三
                return $LotteryWin->win_rx_zu3($opencode,$select_code,3);break;
            case '88-5-7'://任选-任三-组六
                return $LotteryWin->wx_rx_bxsx($opencode,$select_code,3);break;
            case '88-5-8'://任选-任三-混合组选
                return $LotteryWin->wx_rx_bxsx($opencode,$select_code,3);break;
            case '88-5-9'://任选-任四-复式
                return $LotteryWin->win_rx_sxyz($opencode,$select_code,4);break;
            case '88-5-10'://任选-任四-单式
                return $LotteryWin->win_rx_sxyz($opencode,$select_code,4);break;
            case '88-6-1'://趣味-特殊-一帆风顺
                return $LotteryWin->win_quwei($opencode,$select_code,1);break;
            case '88-6-2'://趣味-特殊-好事成双
                return $LotteryWin->win_quwei($opencode,$select_code,2);break;
            case '88-6-3'://趣味-特殊-三星报喜
                return $LotteryWin->win_quwei($opencode,$select_code,3);break;
            case '88-6-4'://趣味-特殊-四季发财
                return $LotteryWin->win_quwei($opencode,$select_code,4);break;
            case '88-7-1'://龙虎-龙虎-万千
                return $LotteryWin->win_longhu($opencode,$select_code,1);break;
            case '88-7-2'://龙虎-龙虎-万百
                return $LotteryWin->win_longhu($opencode,$select_code,2);break;
            case '88-7-3'://龙虎-龙虎-万十
                return $LotteryWin->win_longhu($opencode,$select_code,3);break;
            case '88-7-4'://龙虎-龙虎-万个
                return $LotteryWin->win_longhu($opencode,$select_code,4);break;
            case '88-7-5'://龙虎-龙虎-千百
                return $LotteryWin->win_longhu($opencode,$select_code,5);break;
            case '88-7-6'://龙虎-龙虎-千十
                return $LotteryWin->win_longhu($opencode,$select_code,6);break;
            case '88-7-7'://龙虎-龙虎-千个
                return $LotteryWin->win_longhu($opencode,$select_code,7);break;
            case '88-7-8'://龙虎-龙虎-百十
                return $LotteryWin->win_longhu($opencode,$select_code,8);break;
            case '88-7-9'://龙虎-龙虎-百个
                return $LotteryWin->win_longhu($opencode,$select_code,9);break;
            case '88-7-10'://龙虎-龙虎-十个
                return $LotteryWin->win_longhu($opencode,$select_code,10);break;
            case '88-8-1'://大小单双-总和-总和
                return $LotteryWin->win_daxiaodanshuang($opencode,$select_code,1);break;
            case '88-8-2'://大小单双-定位-万位
                return $LotteryWin->win_daxiaodanshuang($opencode,$select_code,2);break;
            case '88-8-3'://大小单双-定位-千位
                return $LotteryWin->win_daxiaodanshuang($opencode,$select_code,3);break;
            case '88-8-4'://大小单双-定位-百位
                return $LotteryWin->win_daxiaodanshuang($opencode,$select_code,4);break;
            case '88-8-5'://大小单双-定位-十位
                return $LotteryWin->win_daxiaodanshuang($opencode,$select_code,5);break;
            case '88-8-6'://大小单双-定位-个位
                return $LotteryWin->win_daxiaodanshuang($opencode,$select_code,6);break;
            case '88-8-7'://大小单双-串关-串关
                return $LotteryWin->win_daxiaodanshuang($opencode,$select_code,7);break;
            case '88-9-1'://特殊号-特殊号-前三
                return $LotteryWin->win_teshuhao($opencode,$select_code,1);break;
            case '88-9-2'://特殊号-特殊号-中三
                return $LotteryWin->win_teshuhao($opencode,$select_code,2);break;
            case '88-9-3'://特殊号-特殊号-后三
                return $LotteryWin->win_teshuhao($opencode,$select_code,3);break;
            case '88-10-1'://斗牛-斗牛-斗牛
                return $LotteryWin->win_douniu($opencode,$select_code);break;
            case '88-11-1'://四星-后四直选-复式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,4);break;
            case '88-11-2'://四星-后四直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,4);break;
            case '88-11-3'://四星-后四直选-组合
                return $LotteryWin->win_ssc_zuhe($opencode,$select_code,4,2);break;
            case '88-11-4'://四星-后四组选-组选24
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,0);break;
            case '88-11-5'://四星-后四组选-组选12
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,0);break;
            case '88-11-6'://四星-后四组选-组选6
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,0,1);break;
            case '88-11-7'://四星-后四组选-组选4
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,0);break;
            case '88-11-8'://四星-前四直选-复式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,4,1);break;
            case '88-11-9'://四星-前四直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,4,1);break;
            case '88-11-10'://四星-前四直选-组合
                return $LotteryWin->win_ssc_zuhe($opencode,$select_code,4,3);break;
            case '88-11-11'://四星-前四组选-组选24
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,1);break;
            case '88-11-12'://四星-前四组选-组选12
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,1);break;
            case '88-11-13'://四星-前四组选-组选6
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,1);break;
            case '88-11-14'://四星-前四组选-组选4
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,4,1,1);break;
            case '88-12-1'://三星-后三直选-复式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,3);break;
            case '88-12-2'://三星-后三直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,3);break;
            case '88-12-3'://三星-后三直选-直选和值
                return $LotteryWin->win_ssc_zxhz($opencode,$select_code,3);break;
            case '88-12-4'://三星-后三直选-直选跨度
                return $LotteryWin->win_ssc_zxkd($opencode,$select_code,3);break;
            case '88-12-5'://三星-后三直选-和值尾数
                return $LotteryWin->win_ssc_hzws($opencode,$select_code,3);break;
            case '88-12-6'://三星-后三组选-组三
                return $LotteryWin->win_star3_zu3($opencode,$select_code,1);break;
            case '88-12-7'://三星-后三组选-组六
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,3);break;
            case '88-12-8'://三星-后三组选-混合组选
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,3);break;
            case '88-12-9'://三星-中三直选-复式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,3,2);break;
            case '88-12-10'://三星-中三直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,3,2);break;
            case '88-12-11'://三星-中三直选-直选和值
                return $LotteryWin->win_ssc_zxhz($opencode,$select_code,3,2);break;
            case '88-12-12'://三星-中三直选-直选跨度
                return $LotteryWin->win_ssc_zxkd($opencode,$select_code,3,2);break;
            case '88-12-13'://三星-中三直选-和值尾数
                return $LotteryWin->win_ssc_hzws($opencode,$select_code,3,2);break;
            case '88-12-14'://三星-中三组选-组三
                return $LotteryWin->win_star3_zu3($opencode,$select_code,2);break;
            case '88-12-15'://三星-中三组选-组六
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,3,2);break;
            case '88-12-16'://三星-中三组选-混合组选
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,3,2);break;
            case '88-12-17'://三星-前三直选-复式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,3,1);break;
            case '88-12-18'://三星-前三直选-单式
                return $LotteryWin->win_ssc_sxyz($opencode,$select_code,3,1);break;
            case '88-12-19'://三星-前三直选-直选和值
                return $LotteryWin->win_ssc_zxhz($opencode,$select_code,3,1);break;
            case '88-12-20'://三星-前三直选-直选跨度
                return $LotteryWin->win_ssc_zxkd($opencode,$select_code,3,1);break;
            case '88-12-21'://三星-前三直选-和值尾数
                return $LotteryWin->win_ssc_hzws($opencode,$select_code,3,1);break;
            case '88-12-22'://三星-前三组选-组三
                return $LotteryWin->win_star3_zu3($opencode,$select_code,3);break;
            case '88-12-23'://三星-前三组选-组六
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,3,1);break;
            case '88-12-24'://三星-前三组选-混合组选
                return $LotteryWin->win_ssc_zuhe_bxsx($opencode,$select_code,3,1);break;
            //PK拾
            case '96-1-1'://前一-前一-前一
                return $LotteryWin->win_11x5($opencode,$select_code,1,1);break;
            case '96-2-1'://前二-前二-前二复式
                return $LotteryWin->win_11x5($opencode,$select_code,1,2);break;
            case '96-2-2'://前二-前二-前二单式
                return $LotteryWin->win_11x5($opencode,$select_code,1,2);break;
            case '96-3-1'://前三-前三-前三复式
                return $LotteryWin->win_11x5($opencode,$select_code,1,3);break;
            case '96-3-2'://前三-前三-前三单式
                return $LotteryWin->win_11x5($opencode,$select_code,1,3);break;
            case '96-4-1'://定位胆-定位胆-第1~5名
                return $LotteryWin->win_11x5($opencode,$select_code,4);break;
            case '96-4-2'://定位胆-定位胆-第6~10名
                return $LotteryWin->win_11x5($opencode,$select_code,4);break;
            case '96-5-1'://冠亚和-冠亚和-和值
                return $LotteryWin->win_pk10($opencode,$select_code,1);break;
            case '96-6-1'://龙虎-龙虎-冠军
                return $LotteryWin->win_pk10($opencode,$select_code,2,0);break;
            case '96-6-2'://龙虎-龙虎-亚军
                return $LotteryWin->win_pk10($opencode,$select_code,2,1);break;
            case '96-6-3'://龙虎-龙虎-季军
                return $LotteryWin->win_pk10($opencode,$select_code,2,2);break;
            case '96-6-4'://龙虎-龙虎-第四名
                return $LotteryWin->win_pk10($opencode,$select_code,2,3);break;
            case '96-6-5'://龙虎-龙虎-第五名
                return $LotteryWin->win_pk10($opencode,$select_code,2,4);break;
            case '96-7-1'://五行-五行-冠军
                return $LotteryWin->win_pk10($opencode,$select_code,3,0);break;
            case '96-7-2'://五行-五行-亚军
                return $LotteryWin->win_pk10($opencode,$select_code,3,1);break;
            case '96-7-3'://五行-五行-季军
                return $LotteryWin->win_pk10($opencode,$select_code,3,2);break;
            case '96-8-1'://大小单双-大小-冠军
                return $LotteryWin->win_pk10($opencode,$select_code,4,0);break;
            case '96-8-2'://大小单双-大小-亚军
                return $LotteryWin->win_pk10($opencode,$select_code,4,1);break;
            case '96-8-3'://大小单双-大小-季军
                return $LotteryWin->win_pk10($opencode,$select_code,4,2);break;
            case '96-8-4'://大小单双-单双-冠军
                return $LotteryWin->win_pk10($opencode,$select_code,5,0);break;
            case '96-8-5'://大小单双-单双-亚军
                return $LotteryWin->win_pk10($opencode,$select_code,5,1);break;
            case '96-8-6'://大小单双-单双-季军
                return $LotteryWin->win_pk10($opencode,$select_code,5,2);break;
            case '96-8-7'://大小单双-冠亚和-大小单双
                return $LotteryWin->win_pk10($opencode,$select_code,6);break;
            //六合彩
            case '99-1-1'://两面-两面-两面
                return $LotteryWin->win_hk6($opencode,$select_code,1);break;
            case '99-2-1'://特码-特码-特码
                return $LotteryWin->win_hk6($opencode,$select_code,2);break;
            case '99-3-1'://正码-正码-正码
                return $LotteryWin->win_hk6($opencode,$select_code,3);break;
            case '99-4-1'://正码特-正码特-正一特
                return $LotteryWin->win_hk6($opencode,$select_code,4,0);break;
            case '99-4-2'://正码特-正码特-正二特
                return $LotteryWin->win_hk6($opencode,$select_code,4,1);break;
            case '99-4-3'://正码特-正码特-正三特
                return $LotteryWin->win_hk6($opencode,$select_code,4,2);break;
            case '99-4-4'://正码特-正码特-正四特
                return $LotteryWin->win_hk6($opencode,$select_code,4,3);break;
            case '99-4-5'://正码特-正码特-正五特
                return $LotteryWin->win_hk6($opencode,$select_code,4,4);break;
            case '99-4-6'://正码特-正码特-正六特
                return $LotteryWin->win_hk6($opencode,$select_code,4,5);break;
            case '99-5-1'://正码1-6-正码1-6-正码一
                return $LotteryWin->win_hk6($opencode,$select_code,5,0);break;
            case '99-5-2'://正码1-6-正码1-6-正码二
                return $LotteryWin->win_hk6($opencode,$select_code,5,1);break;
            case '99-5-3'://正码1-6-正码1-6-正码三
                return $LotteryWin->win_hk6($opencode,$select_code,5,2);break;
            case '99-5-4'://正码1-6-正码1-6-正码四
                return $LotteryWin->win_hk6($opencode,$select_code,5,3);break;
            case '99-5-5'://正码1-6-正码1-6-正码五
                return $LotteryWin->win_hk6($opencode,$select_code,5,4);break;
            case '99-5-6'://正码1-6-正码1-6-正码六
                return $LotteryWin->win_hk6($opencode,$select_code,5,5);break;
            case '99-6-1'://正码过关-正码过关-正码过关
                return $LotteryWin->win_hk6($opencode,$select_code,6);break;
            case '99-7-1'://连码-连码-四全中
                return $LotteryWin->win_hk6($opencode,$select_code,7,4);break;
            case '99-7-2'://连码-连码-三全中
                return $LotteryWin->win_hk6($opencode,$select_code,7,3);break;
            case '99-7-3'://连码-连码-三中二
                return $LotteryWin->win_hk6($opencode,$select_code,7,2);break;
            case '99-7-4'://连码-连码-二全中
                return $LotteryWin->win_hk6($opencode,$select_code,7,2);break;
            case '99-7-5'://连码-连码-二中特
                return $LotteryWin->win_hk6($opencode,$select_code,8);break;
            case '99-7-6'://连码-连码-特串
                return $LotteryWin->win_hk6($opencode,$select_code,8,1);break;

            case '99-8-1'://连肖连尾-连肖连尾-二连肖
                return $LotteryWin->win_hk6($opencode,$select_code,9,2);break;
            case '99-8-2'://连肖连尾-连肖连尾-三连肖
                return $LotteryWin->win_hk6($opencode,$select_code,9,3);break;
            case '99-8-3'://连肖连尾-连肖连尾-四连肖
                return $LotteryWin->win_hk6($opencode,$select_code,9,4);break;
            case '99-8-4'://连肖连尾-连肖连尾-五连肖
                return $LotteryWin->win_hk6($opencode,$select_code,9,5);break;
            case '99-8-5'://肖连尾-连肖连尾-二连尾
                return $LotteryWin->win_hk6($opencode,$select_code,10,2);break;
            case '99-8-6'://连肖连尾-连肖连尾-三连尾
                return $LotteryWin->win_hk6($opencode,$select_code,10,3);break;
            case '99-8-7'://连肖连尾-连肖连尾-四连尾
                return $LotteryWin->win_hk6($opencode,$select_code,10,4);break;
            case '99-8-8'://连肖连尾-连肖连尾-五连尾
                return $LotteryWin->win_hk6($opencode,$select_code,10,5);break;
            case '99-9-1'://自选不中-自选不中-五不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,5);break;
            case '99-9-2'://自选不中-自选不中-六不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,6);break;
            case '99-9-3'://自选不中-自选不中-七不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,7);break;
            case '99-9-4'://自选不中-自选不中-八不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,8);break;
            case '99-9-5'://自选不中-自选不中-九不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,9);break;
            case '99-9-6'://自选不中-自选不中-十不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,10);break;
            case '99-9-7'://自选不中-自选不中-十一不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,11);break;
            case '99-9-8'://自选不中-自选不中-十二不中
                return $LotteryWin->win_hk6($opencode,$select_code,11,12);break;
            case '99-10-1'://生肖-生肖-正肖
                return $LotteryWin->win_hk6($opencode,$select_code,12,1);break;
            case '99-10-2'://生肖-生肖-特肖
                return $LotteryWin->win_hk6($opencode,$select_code,12,2);break;
            case '99-10-3'://生肖-生肖-一肖
                return $LotteryWin->win_hk6($opencode,$select_code,12,3);break;
            case '99-10-4'://生肖-生肖-总肖
                return $LotteryWin->win_hk6($opencode,$select_code,13);break;
            case '99-11-1'://合肖-合肖-合肖
                return $LotteryWin->win_hk6($opencode,$select_code,14);break;
            case '99-12-1'://色波-色波-三色波
                return $LotteryWin->win_hk6($opencode,$select_code,15);break;
            case '99-12-2'://色波-色波-半波
                return $LotteryWin->win_hk6($opencode,$select_code,15);break;
            case '99-12-3'://色波-色波-半半波
                return $LotteryWin->win_hk6($opencode,$select_code,15);break;
            case '99-12-4'://色波-色波-七色波
                return $LotteryWin->win_hk6($opencode,$select_code,16);break;
            case '99-13-1'://尾数-尾数-头尾数
                return $LotteryWin->win_hk6($opencode,$select_code,17,1);break;
            case '99-13-2'://尾数-尾数-正特尾数
                return $LotteryWin->win_hk6($opencode,$select_code,17,2);break;
            case '99-14-1'://七码五行-七码五行-七码
                return $LotteryWin->win_hk6($opencode,$select_code,18);break;
            case '99-14-2'://七码五行-七码五行-五行
                return $LotteryWin->win_hk6($opencode,$select_code,19);break;
            case '99-15-1'://中一-中一-五中一
                return $LotteryWin->win_hk6($opencode,$select_code,20,1);break;
            case '99-15-2'://中一-中一-六中一
                return $LotteryWin->win_hk6($opencode,$select_code,20,1);break;
            case '99-15-3'://中一-中一-七中一
                return $LotteryWin->win_hk6($opencode,$select_code,20,1);break;
            case '99-15-4'://中一-中一-八中一
                return $LotteryWin->win_hk6($opencode,$select_code,20,1);break;
            case '99-15-5'://中一-中一-九中一
                return $LotteryWin->win_hk6($opencode,$select_code,20,1);break;
            case '99-15-6'://中一-中一-十中一
                return $LotteryWin->win_hk6($opencode,$select_code,20,1);break;

            //快三
            case '102-1-1'://二不同号-二不同号-标准选号
                return $LotteryWin->win_k3($opencode,$select_code,1);break;
            case '102-1-2'://二不同号-二不同号-手动选号
                return $LotteryWin->win_k3($opencode,$select_code,2);break;
            case '102-1-3'://二不同号-二不同号-胆拖选号
                return $LotteryWin->win_k3($opencode,$select_code,1);break;
            case '102-2-1'://二同号-二同号单选-标准选号
                return $LotteryWin->win_k3($opencode,$select_code,4);break;
            case '102-2-2'://二同号-二同号单选-手动选号
                return $LotteryWin->win_k3($opencode,$select_code,5);break;
            case '102-2-3'://二同号-二同号复选-二同号复选
                return $LotteryWin->win_k3($opencode,$select_code,6);break;
            case '102-3-1'://三不同号-三不同号-标准选号
                return $LotteryWin->win_k3($opencode,$select_code,7);break;
            case '102-3-2'://三不同号-三不同号-手动选号
                return $LotteryWin->win_k3($opencode,$select_code,5);break;
            case '102-4-1'://三同号-三同号单选-三同号单选
                return $LotteryWin->win_k3($opencode,$select_code,9);break;
            case '102-4-2'://三同号-三同号通选-三同号通选
                return $LotteryWin->win_k3($opencode,$select_code,10);break;
            case '102-5-1'://三连号-三连号通选-三连号通选
                return $LotteryWin->win_k3($opencode,$select_code,10);break;
            
            //11选5
            case '108-1-1'://三码-前三直选-复式
                return $LotteryWin->win_11x5($opencode,$select_code,1,3);break;
            case '108-1-2'://三码-前三直选-单式
                return $LotteryWin->win_11x5($opencode,$select_code,1,3);break;
            case '108-1-3'://三码-前三组选-复式
                return $LotteryWin->win_11x5($opencode,$select_code,2,3);break;
            case '108-1-4'://三码-前三组选-单式
                return $LotteryWin->win_11x5($opencode,$select_code,2,3);break;
            case '108-2-1'://二码-前二直选-复式
                return $LotteryWin->win_11x5($opencode,$select_code,1,2);break;
            case '108-2-2'://二码-前二直选-单式
                return $LotteryWin->win_11x5($opencode,$select_code,1,2);break;
            case '108-2-3'://二码-前二组选-复式
                return $LotteryWin->win_11x5($opencode,$select_code,2,2);break;
            case '108-2-4'://二码-前二组选-单式
                return $LotteryWin->win_11x5($opencode,$select_code,2,2);break;
            case '108-3-1'://不定胆-不定胆-前三位
                return $LotteryWin->win_11x5($opencode,$select_code,3,3);break;
            case '108-4-1'://定位胆-定位胆-定位胆
                return $LotteryWin->win_11x5($opencode,$select_code,4);break;
            case '108-5-1'://任选-任选复式-一中一
                return $LotteryWin->win_11x5($opencode,$select_code,5,1);break;
            case '108-5-2'://任选-任选复式-二中二
                return $LotteryWin->win_11x5($opencode,$select_code,5,2);break;
            case '108-5-3'://任选-任选复式-三中三
                return $LotteryWin->win_11x5($opencode,$select_code,5,3);break;
            case '108-5-4'://任选-任选复式-四中四
                return $LotteryWin->win_11x5($opencode,$select_code,5,4);break;
            case '108-5-5'://任选-任选复式-五中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,5);break;
            case '108-5-6'://任选-任选复式-六中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,6);break;
            case '108-5-7'://任选-任选复式-七中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,7);break;
            case '108-5-8'://任选-任选复式-八中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,8);break;
            case '108-5-9'://任选-任选单式-一中一
                return $LotteryWin->win_11x5($opencode,$select_code,5,1);break;
            case '108-5-10'://任选-任选单式-二中二
                return $LotteryWin->win_11x5($opencode,$select_code,5,2);break;
            case '108-5-11'://任选-任选单式-三中三
                return $LotteryWin->win_11x5($opencode,$select_code,5,3);break;
            case '108-5-12'://任选-任选单式-四中四
                return $LotteryWin->win_11x5($opencode,$select_code,5,4);break;
            case '108-5-13'://任选-任选单式-五中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,5);break;
            case '108-5-14'://任选-任选单式-六中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,6);break;
            case '108-5-15'://任选-任选单式-七中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,7);break;
            case '108-5-16'://任选-任选单式-八中五
                return $LotteryWin->win_11x5($opencode,$select_code,5,8);break;

            //PC蛋蛋
            case '115-1-1'://特码-特码-特码
                return $LotteryWin->win_bjkl8($opencode,$select_code,1);break;
            case '115-2-1'://混合-混合-混合
                return $LotteryWin->win_bjkl8($opencode,$select_code,2);break;
            case '115-3-1'://波色-波色-波色
                return $LotteryWin->win_bjkl8($opencode,$select_code,3);break;
            case '115-4-1'://豹子-豹子-豹子
                return $LotteryWin->win_bjkl8($opencode,$select_code,4);break;
            case '115-5-1'://特码包三-特码包三-特码包三
                return $LotteryWin->win_bjkl8($opencode,$select_code,5);break;
            default: return [0,[]];break;
        }
        wr("\n\n==================".$rules."===================\n\n");
    }

    public function updateLottery()
    {
        //每天03开始准备第二天数据
        $start_time = [
            89=>'00:00:01',
            90=>'00:02:02',
            92=>'00:00:03',
            93=>'10:00:04',
            94=>'08:42:10',
            95=>'09:01:02',
            97=>'09:02:02',
            103=>'08:40:04',
            104=>'08:19:00',
            105=>'09:27:00',
            106=>'08:29:00',
            107=>'09:01:08',
            109=>'08:25:30',
            110=>'09:01:00',
            111=>'08:50:00',
            112=>'08:27:00',
            113=>'08:25:00',
            114=>'08:52:00',
            116=>'09:00:00',
        ];

        $lottery_ids            = array_flip($start_time);
        sort($lottery_ids);

        $lockModel                  = model('lottery_lock');
        $lock                       = $lockModel->getOneById(1);
        if(empty($lock)){
            $updata                 = [];
            $updata['stime']        = $this->format_lottery_limit('00:01:00');
            $updata['etime']        = $this->format_lottery_limit('00:10:00');
            $updata['lottery_ids']  = json_encode($lottery_ids);
            $lock                   = $lockModel->addData($updata);
        }
        wr("系统定期开奖记录检测中.....................\n");
        if (!($lock['stime'] <= $this->nowTime && $lock['etime'] >= $this->nowTime)) {
            wr("系统检测更新未到开始时间\n");
            return;
        }

        $ids                        = json_decode($lock['lottery_ids'],true);

        if (!empty($ids)) {
            sort($ids);
        }

        if (isset($ids[0]) && $ids[0] > 0) {
            $this->lotteryid        = $ids[0];

            unset($ids[0]);

            $updata                 = [];
            $updata['lottery_ids']  = json_encode($ids);
            $lockModel->updateById(1,$updata);
        }else{
            $updata                 = [];
            $updata['stime']        = $this->format_lottery_limit('00:05:00') + 86400;
            $updata['etime']        = $this->format_lottery_limit('00:10:00') + 86400;
            $updata['lottery_ids']  = json_encode($lottery_ids);
            $lockModel->updateById(1,$updata);
            wr("系统检测更新完成\n");
            return;
        }

        //每一期时间间隔
        $updata                 = [];
        $lotteryTag             = config('lottery.lottery_tag');
        if (!isset($lotteryTag[$this->lotteryid]) ){
            wr("系统检测到彩种【" . $this->lotteryid . "】不存在\n");
            return true;
        }

        //每一期时间间隔
        $updata                 = [];
        $lastItem               = config('lottery.lottery_expect');
        if (!(isset($lastItem[$this->lotteryid]) && $lastItem[$this->lotteryid] > 0)){
            wr("系统检测到彩种【" . $this->lotteryid . "】时间间隔不存在\n");
            return true;
        }

        $limit_time             = $this->getLotteryTime() * 60;
        $dbModel                = model($lotteryTag[$this->lotteryid]);

        //删除3天前的数据
        if ($this->lotteryid != 100) {
            $del_time           = $this->format_lottery_limit('00:00:00') - 86400*1 - 3600;
            $count              = $dbModel->where('create_time','elt',$del_time)->delete();
            wr("系统检测到删除彩种【" . $this->lotteryid . "】" . date('Y-m-d H:i:s',$del_time). "之前的" . $count . "个数据\n");
        }

        $delay                  = model('category')->where('id','=',$this->lotteryid)->value('delay');
        $delay                  = !empty($delay) ? intval($delay) : 0;
        
        $stime                  = isset($start_time[$this->lotteryid]) ? $start_time[$this->lotteryid] : '00:00:00';

        $days                   = 86400*0;
        $first_expect_time      = $this->format_lottery_limit($stime) + $days;
        $first_expect_time1     = $this->format_lottery_limit('22:00:00') + $days + $delay;
        $first_expect_time2     = $this->format_lottery_limit('09:51:00') + $days + $delay;
        $first_expect_time3     = $this->format_lottery_limit('00:00:00') + $days;

        //查找是否正常生成完数据
        $code                   = md5($this->format_lottery_limit($stime).'-'.$this->lotteryid);
        $count                  = $dbModel->where('code','=',$code)->count('id');

        wr("\n系统开始更新【" . $this->lotteryid . "】的数据\n第一期原始时间为：" . date('Y-m-d H:i:s',$this->format_lottery_limit($stime)) . 
            "\n第一期实际时间为：" . date('Y-m-d H:i:s',$first_expect_time) ."\n当前数据标识为：" . $code . "\n当前已有数据：" . $count . "\n");

        if ($count != $lastItem[$this->lotteryid]) {
            $dbModel->where('code','=',$code)->delete();
            wr("系统检测到删除彩种【" . $this->lotteryid . "】标识为" . $code. "的" . $count . "个数据\n");
        }else{
            wr("系统检测到彩种【" . $this->lotteryid . "】的数据完整 无需生成\n");
            return true;
        }

        $delay_expect_time      = $first_expect_time + $delay;

        for ($i=1; $i <= $lastItem[$this->lotteryid]; $i++) {
            switch ($this->lotteryid) {
                case 89:
                    $opentimestamp          = $delay_expect_time + $i*$limit_time;
                    $expect                 = date('Ymd',$first_expect_time) . substr('0000' . $i, -4);
                    $opentime               = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number            = substr($expect, -4);
                    break;
                case 90:
                    $opentimestamp          = $delay_expect_time + $i*$limit_time;
                    $expect                 = date('Ymd',$first_expect_time) . substr('000' . $i, -3);
                    $opentime               = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number            = substr($expect, -4);
                    break;
                case 92:
                    if($i <= 23 || $i>= 97){
                        $limit_time              = 5*60;
                        if ($i <= 23) {
                            $opentimestamp       = $delay_expect_time + $i*$limit_time;
                        }else{
                            $opentimestamp       = $first_expect_time1 + ($i - 96)*$limit_time;
                        }
                    }else{
                        $limit_time             = 10*60;
                        $opentimestamp          = $first_expect_time2 + ($i - 23)*$limit_time;
                    }

                    $expect             = date('Ymd',$first_expect_time) . substr('000' . $i, -3);
                    $opentime           = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number        = substr($expect, -4);
                    break;
                case 93:
                    $opentimestamp          = $delay_expect_time + $i*$limit_time;
                    $expect                 = date('Ymd',$first_expect_time) . substr('000' . $i, -3);
                    $opentime               = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number            = substr($expect, -4);
                    break;
                case 94:
                    $initial_expect         = 281096;
                    $initial_time           = strtotime('2018-11-09 00:00:00');
                    $expect                 = $initial_expect + ($first_expect_time3-$initial_time)/86400 * $lastItem[$this->lotteryid];

                    $opentimestamp   = $delay_expect_time + $i*$limit_time;
                    $expect          = $expect > 9999999 ? $expect : substr('0000000' . ($i+$expect), -7);
                    $opentime        = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number     = intval($expect);
                    break;
                case 95:
                    $opentimestamp          = $delay_expect_time + $i*$limit_time;
                    $expect                 = date('Ymd',$first_expect_time) . substr('000' . $i, -3);
                    $opentime               = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number            = substr($expect, -4);
                    break;
                case 97:
                    $initial_expect         = 713818;
                    $initial_time           = strtotime('2018-11-09 00:00:00');
                    $expect                 = $initial_expect + ($first_expect_time3-$initial_time)/86400 * $lastItem[$this->lotteryid];

                    $opentimestamp   = $delay_expect_time + $i*$limit_time;
                    $expect          = intval($i+$expect);
                    $opentime        = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number     = intval($expect);
                    break;
                case 99:
                    # code...
                    break;
                case 103:
                case 104:
                case 105:
                case 106:
                case 107:
                    
                    $opentimestamp          = $delay_expect_time + $i*$limit_time;
                    $expect                 = date('Ymd',$first_expect_time) . substr('000' . $i, -3);
                    $opentime               = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number            = substr($expect, -4);
                    break;
                case 109:
                case 110:
                case 111:
                case 112:
                case 113:
                case 114:
                    $opentimestamp          = $delay_expect_time + $i*$limit_time;
                    $expect                 = date('Ymd',$first_expect_time) . substr('00' . $i, -2);
                    $opentime               = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number            = substr($expect, -4);
                    break;
                case 116:
                    $initial_expect         = 919795;
                    $initial_time           = strtotime('2018-11-09 00:00:00');
                    $expect                 = $initial_expect + ($first_expect_time3-$initial_time)/86400 * $lastItem[$this->lotteryid];

                    $opentimestamp   = $delay_expect_time + $i*$limit_time;
                    $expect          = intval($i+$expect);
                    $opentime        = date('Y-m-d H:i:s' ,$opentimestamp - $delay);
                    $term_number     = intval($expect);
                    break;
                default: echo $this->lotteryid;exit;break;
            }

            $updata[]   = [
                'status'        =>2,
                'expect'        =>$expect,
                'lotterid'      =>$this->lotteryid,
                'opentime'      =>$opentime,
                'opentimestamp' =>$opentimestamp,
                'term_number'   =>$term_number,
                'create_time'   =>$this->nowTime,
                'code'          =>$code,
            ];
        }

        $dbModel->saveLotteryInfoAll($updata);
        wr("系统检测到彩种【" . $this->lotteryid . "】成功生成" . count($updata) . "个数据,预期数据" . $lastItem[$this->lotteryid] . "\n");
        return true;
    }

    public function updateData()
    {
        $this->saveLottery();
    }

    private function saveLottery()
    {
        //这里做一个数据请求的延迟保护
        $delayKey       = "delay_saveLottery_3s_".$this->lotteryid;
        $delay          = cache($delayKey);
        if (!empty($delay) && $delay > $this->nowTime) {
            return false;
        }

        cache($delayKey,$this->nowTime+3);

        $lotteryTag             = config('lottery.lottery_tag');
        if (!isset($lotteryTag[$this->lotteryid]) ) return true;

        $dbmodel                = model($lotteryTag[$this->lotteryid]);

        //获取最近一条数据入库
        $data                   = $this->getData();

        if (!empty($data) && isset($data[0]))
        {
            $opdata                   = $data[0];
            $expect                   = $opdata['expect'];
            $info                     = $dbmodel->getLotteryInfoByExpect($expect);
            $opencode2                = '';
            
            if ($this->lotteryid == 116) {
                $opencode2            = $opdata['opencode'];
                $opdata['opencode']   = $this->getbj28code($opencode2);
            }

            if (!empty($info) && $info['status'] == 2 && $this->lotteryid != 100 ) {

                //删除预存未开奖数据
                $updata = [
                    'status'=>1,
                    'expect'=>$opdata['expect'],
                    'opencode'=>$opdata['opencode'],
                    'opentime'=>$opdata['opentime'],
                    'opentimestamp'=>$opdata['opentimestamp'],
                    'opencode2'=>$opencode2
                ];

                if ($this->lotteryid != 116) {
                    unset($updata['opencode2']);
                }

                $dbmodel->where('expect','=',$opdata['expect'])->update($updata);

                //推送开奖信息
                $this->jpushOpenLotterInfo();
            }

            if ($this->lotteryid == 100)
            {
                $delay            = model('category')->where('id','=',$this->lotteryid)->value('delay');
                $delay            = !empty($delay) ? intval($delay) : 0;

                //香港六合彩
                $term_number      = substr($expect,-4);
                $openyear1        = date('Y',$opdata['opentimestamp']);
                $openyear2        = date('Y',$opdata['opentimestamp']+3600*24*2);
                $nextOpenTime     = $opdata['opentimestamp'] + 86400;

                if ($openyear1 != $openyear2) {
                    $nextExpect           = substr(date('Ymd'),0,4) . '001';
                    $nextItem             = substr($nextExpect,-4);
                }else{
                    $nextExpect           = substr(date('Ymd'),0,3) . (substr($expect,-4)+1);
                    $nextItem             = substr($expect,-4)+1;
                }

                if (empty($info) || $info['status'] == 2) {
                    //删除预存未开奖数据
                    $dbmodel->delLotteryInfoByExpect($expect);
                    $updata = [
                        [
                        'status'        =>1,
                        'expect'        =>$opdata['expect'],
                        'lotterid'      =>$this->lotteryid,
                        'opencode'      =>$opdata['opencode'],
                        'opentime'      =>date('Y-m-d H:i:s',strtotime($opdata['opentime'])),
                        'opentimestamp' =>$opdata['opentimestamp'],
                        'term_number'   =>$term_number,
                        'create_time'   =>$this->nowTime,
                        ],
                        [
                        'status'        =>2,
                        'expect'        =>$nextExpect,
                        'lotterid'      =>$this->lotteryid,
                        'opencode'      =>'',
                        'opentime'      =>date('Y-m-d H:i:s' ,$nextOpenTime),
                        'opentimestamp' =>$nextOpenTime + $delay,
                        'term_number'   =>$nextItem,
                        'create_time'   =>$this->nowTime
                        ]
                    ];

                    $dbmodel->saveLotteryInfoAll($updata);
                }

                //如果是六合彩 需要特殊处理 更新开奖时间
                $hk6_info            = model('lottery_hk6')->getLotteryInfoByExpect($nextExpect);
                if (!empty($hk6_info) && $this->nowTime >= $hk6_info['opentimestamp'] + (5*60))
                {
                    //更新下期开奖时间为 21:35
                    $opentimestamp              = $hk6_info['opentimestamp'] + 86400;
                    $opentime                   = strtotime($hk6_info['opentime']) + 86400;

                    $updata                     = [];
                    $updata['opentime']         = date('Y-m-d H:i:s' ,$opentime);
                    $updata['opentimestamp']    = $opentime + $delay;
                    model('lottery_hk6')->where('id','=',$hk6_info['id'])->update($updata);
                }
            }

            return true;
        }

      return false;
    }

    public function jpushOpenLotterInfo()
    {
        return true;
    }

    private function getbj28code($code='')
    {
        if (!empty($code))
        {
            $num1 = explode(',', substr($code,0,17));
            $num2 = explode(',',substr($code,18,17));
            $num3 = explode(',',substr($code,36,17));

            $nn1 = 0;
            $nn2 = 0;
            $nn3 = 0;
            for ($i=0; $i < 6; $i++) { 
                $nn1 += $num1[$i];
                $nn2 += $num2[$i];
                $nn3 += $num3[$i];
            }
            $nn1    = substr($nn1,-1);
            $nn2    = substr($nn2,-1);
            $nn3    = substr($nn3,-1);
            $nn4    = intval($nn1) + intval($nn2) + intval($nn3);

            return implode(',', [$nn1,$nn2,$nn3,$nn4]);
        }

        return '';
    }

    //三级分成
    private function distribution($money=0,$uid=0)
    {
        if ($money <= 0 || $uid <= 0) return false;

        $userModel      = model('user_detail');
        $userinfo       = $userModel->getOneByUid($uid);

        if (empty($userinfo) || empty($userinfo['invitation_code']))  return false;
        $invitation_code    = $userinfo['invitation_code'];

        $rate1          = config('system_config.fen_first_rate');
        $rate2          = config('system_config.fen_second_rate');
        $rate3          = config('system_config.fen_third_rate');
        $rate1          = !empty($rate1) ? ($rate1*1)/100 : 0;
        $rate2          = !empty($rate2) ? ($rate2*1)/100 : 0;
        $rate3          = !empty($rate3) ? ($rate3*1)/100 : 0;

        //待分成金额
        $money          = !empty($money) ? $money*1 : 0;
        $updata         = [];

        //一级分销ID
        $uid1           = get_invitation_uid($invitation_code);
        $userinfo1      = $userModel->getOneByUid($uid1);
        $userinfo1      = !empty($userinfo1) ? $userinfo1->toArray() : [];

        //一级分成
        $userinfo2      = [];
        if (!empty($userinfo1) && $rate1 > 0) {

            //确定二级分销用户
            $uid2       = get_invitation_uid($userinfo1['invitation_code']);
            $userinfo2  = $userModel->getOneByUid($uid2);
            $userinfo2  = !empty($userinfo2) ? $userinfo2->toArray() : [];

            //需要判断是否是代理角色 如果是代理角色不需要参与三级分销 但是需要获取用户信息
            $agent_id       = model('user_group_access')->checkGroupByUidAndGid($userinfo1['uid'],3);

            if (!$agent_id)
            {
                $money1     = $money*$rate1;
                $updata[]   = ['uid'=>$userinfo1['uid'],'create_time'=>time(),'money'=>$money1,'uid1'=>$uid];

                //增加累计收益金额
                $data                  = [];
                $data['account']       = $userinfo1['account']+$money1;
                $data['profit_all']    = $userinfo1['profit_all']+$money1;
                $userModel->updateById($userinfo1['id'],$data);
                $userModel->delDetailDataCacheByUid($userinfo1['uid']);

                //写日志
                model('user_account_log')->addAccountLog($userinfo1['uid'],$money1,'一级返佣',1,5);
            }
        }

        //二级分成
        $userinfo3      = [];
        if (!empty($userinfo2) && $rate2 > 0) {

            //确定三级分销用户
            $uid3       = get_invitation_uid($userinfo2['invitation_code']);
            $userinfo3  = $userModel->getOneByUid($uid3);
            $userinfo3  = !empty($userinfo3) ? $userinfo3->toArray() : [];

            //需要判断是否是代理角色 如果是代理角色不需要参与三级分销 但是需要获取用户信息
            $agent_id       = model('user_group_access')->checkGroupByUidAndGid($userinfo2['uid'],3);
            if (!$agent_id)
            {
                $money2     = $money*$rate2;
                $updata[]   = ['uid'=>$userinfo2['uid'],'create_time'=>time(),'money'=>$money2,'uid1'=>$uid];

                //增加累计收益金额
                $data                  = [];
                $data['account']       = $userinfo2['account']+$money2;
                $data['profit_all']    = $userinfo2['profit_all']+$money2;
                $userModel->updateById($userinfo2['id'],$data);
                $userModel->delDetailDataCacheByUid($userinfo2['uid']);

                //写日志
                model('user_account_log')->addAccountLog($userinfo2['uid'],$money2,'二级返佣',1,5);
            }
        }

        //三级分成
        if (!empty($userinfo3) && $rate3 > 0) {

            //需要判断是否是代理角色 如果是代理角色不需要参与三级分销 但是需要获取用户信息
            $agent_id       = model('user_group_access')->checkGroupByUidAndGid($userinfo3['uid'],3);
            if (!$agent_id)
            {
                $money3     = $money*$rate3;
                $updata[]   = ['uid'=>$userinfo3['uid'],'create_time'=>time(),'money'=>$money3,'uid1'=>$uid];

                //增加累计收益金额
                $data                  = [];
                $data['account']       = $userinfo3['account']+$money3;
                $data['profit_all']    = $userinfo3['profit_all']+$money3;
                $userModel->updateById($userinfo3['id'],$data);
                $userModel->delDetailDataCacheByUid($userinfo3['uid']);

                //写日志
                model('user_account_log')->addAccountLog($userinfo3['uid'],$money3,'三级返佣',1,5);
            }
        }

        if (!empty($updata)) {
            model('user_distribution')->saveAll($updata);
        }

        return true;
    }

    public function updataLotteryOpenTime($delay = 0)
    {
        if (isset($this->lotteryConfig['lottery_tag'][$this->lotteryid])) {
            $delay            = intval($delay);

            $lottery_table    = $this->lotteryConfig['lottery_tag'][$this->lotteryid];
            $dbModel          = model($lottery_table);

            $map              = [];
            $map[]            = ['opentimestamp','egt',$this->nowTime];
            $map[]            = ['opencode','eq',''];

            $lists            = $dbModel->where($map)->field(['id','opentime','opentimestamp'])->select();
            $lists            = !empty($lists) ? $lists->toArray() : [];
            foreach ($lists as $value) {
                $opentime   = strtotime($value['opentime']) + $delay;
                $dbModel->updateById($value['id'],['opentimestamp'=>$opentime]);
            }
        }
    }
}