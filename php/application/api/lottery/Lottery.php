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

    //获取最近
    public function getLotteryList($parame=[])
    {
        $lottery_table      = '';
        if (isset($this->lotteryConfig['lottery_tag'][$this->lotteryid])) {
            
            $lottery_table  = $this->lotteryConfig['lottery_tag'][$this->lotteryid];
        }else{
            return [];
        }

        $dbModel                    = model($lottery_table);
        
        /*定义数据模型参数*/
        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = $lottery_table;

        //主表名称，可以为空，默认为main
        $modelParame['MainAlias']   = 'main';

        //主表待查询字段，可以为空，默认全字段
        $modelParame['MainField']   = [];

        //排序定义
        $modelParame['order']       = 'main.id desc';       
        
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
            case 90:  $limit_time      = 3;     break;
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
                $select_code    = $value['select_code'];

                $isWin          = $this->winningPrize($opencode,$opentimestamp,$rules,$select_code);
                /*if ($rules == '99-15-1') {
                    return false;
                }*/
                //中奖 计算中奖金额
                $odds           = '';
                if ($isWin[0] > 0 && !empty($isWin[1]))
                {
                    $lotteryRule   = $ruleModle->getLotterRule($rules);
                    //计算赔率
                    $odds          = $this->calculatingOdds($value,$isWin,$lotteryRule);
                }else{

                    //没中奖 如果有代理 代理拿用户投注金额百分比
                    $rate          = config('system_config.agent_fen_rate');
                    $amoney        = !empty($rate) ? $rate*$value['money'] : 0;
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
                    }

                    //用户没中奖执行三级分销
                    $this->distribution($value['money'],$value['uid']);
                }

                //更改订单信息
                $updataOrder                    = [];
                $updataOrder['status']          = 3;
                $updataOrder['win_bets']        = $isWin[0];
                $updataOrder['expect']          = $lotteryInfo['expect'];
                $updataOrder['opencode']        = $opencode;
                $updataOrder['odds']            = $odds;
                $updataOrder['opentimestamp']   = $opentimestamp;
                $updataOrder['win_code']        = json_encode($isWin[1]);
                $updataOrder['iswin']           = $isWin[0] > 0 ? 1 : 0;

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
        $aid             = $value['agent_id'];
        $odds            = $lotteryRule['odds'];
        $odds_rebate     = $lotteryRule['odds_rebate'];
        $tag             = $lotteryRule['tag'];
        $pid             = $lotteryRule['pid'];
        $money           = 0;
        $umoney          = 0;

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
                    $amoney  = 0;
                }else{
                    $moneyAndOdds   = pk10OddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $amoney         = $moneyAndOdds[0];
                }
                
                $umoney      = ($money-$amoney)*1;
                break;
            case 99://六合彩
                $moneyAndOdds       = hk6OddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,0,[],$value);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $amoney  = 0;
                }else{
                    $moneyAndOdds   = hk6OddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds,$value);
                    $amoney         = $moneyAndOdds[0];
                }

                $umoney      = ($money-$amoney)*1;
                break;
            case 102://快3
                $moneyAndOdds       = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $amoney  = 0;
                }else{
                    $moneyAndOdds   = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $amoney         = $moneyAndOdds[0];
                }
                
                $umoney      = ($money-$amoney)*1;
                break;
            case 108://11选5
                $moneyAndOdds       = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $amoney  = 0;
                }else{
                    $moneyAndOdds   = oneOddsMoney($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $amoney         = $moneyAndOdds[0];
                }
                
                $umoney      = ($money-$amoney)*1;
                break;
            case 115://PC蛋蛋
                $moneyAndOdds       = manyOddsMoneyPC($tag,$rebate,$lotteryRule,$value['price'],$isWin);
                $money              = $moneyAndOdds[0];

                if ($aid <= 0 || empty($agentOdds)) {
                    $amoney  = 0;
                }else{
                    $moneyAndOdds   = manyOddsMoneyPC($tag,$rebate,$lotteryRule,$value['price'],$isWin,$aid,$agentOdds);
                    $amoney         = $moneyAndOdds[0];
                }
                
                $umoney      = ($money-$amoney)*1;
                break;
            default: return true;break;
        }

        if ($money <= 0 || $umoney <= 0)  return true;

        if (in_array($pid,[96,99,102,108,115]))
        {
            //代理存在 需要分给代理一部分佣金 (时时彩不考虑代理)
            if ($aid > 0 && $amoney > 0)
            {   
                $agentinfo         = $userModel->getOneByUid($aid);
                $data              = [];
                $data['account']   = $agentinfo['account']+$amoney;
                $userModel->updateById($agentinfo['id'],$data);
                $userModel->delDetailDataCacheByUid($aid);

                //写日志
                model('user_account_log')->addAccountLog($aid,$amoney,'代理返佣',1,5);
            }
        }

        $userinfo              = $userModel->getOneByUid($value['uid']);
        $data                  = [];
        $data['account']       = $userinfo['account']+$umoney;
        $userModel->updateById($userinfo['id'],$data);
        $userModel->delDetailDataCacheByUid($value['uid']);
        
        //写日志
        model('user_account_log')->addAccountLog($value['uid'],$umoney,'彩票中奖',1,4);

        $odds                   = isset($moneyAndOdds[1]) ? $moneyAndOdds[1] : '';
        return $odds;
    }

    public function winningPrize($opencode,$opentimestamp,$rules,$select_code)
    {
        if (empty($opencode) || $opentimestamp >= time() || empty($rules) || empty($select_code))
        return [0,[]];
        
        $LotteryWin     = new \app\api\lottery\LotteryWin();
        //用户选择的号码组合
        $select_code        = json_decode($select_code,true);
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
                return $LotteryWin->win_douniu($opencode,$select_code,4);break;
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
            /*case '99-11-1'://合肖-合肖-合肖
                return $LotteryWin->win_k3($opencode,$select_code,1);break;*/
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

    public function updateData()
    {
      switch ($this->lotteryid) {

        case 89:
            //分分时时彩  09:30-23:30
            $time_start1      = $this->format_lottery_limit('00:00:00');
            $time_end1        = $this->format_lottery_limit('23:59:59');

            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_ffssc';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            /*if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('09:20:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }*/

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("分分时彩下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('分分时时彩开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 90:
            //三分时时彩  09:30-23:30
            $time_start1      = $this->format_lottery_limit('00:00:00');
            $time_end1        = $this->format_lottery_limit('23:59:59');

            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_sfssc';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            /*if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('09:20:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }*/

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("三分时时彩下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('三分时时彩开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        //重庆时时彩
        case 92:
            //重庆时时彩售卖规则 00-02 10-22 22-00
            $time_start1      = $this->format_lottery_limit('00:00:00');
            $time_end1        = $this->format_lottery_limit('02:00:00');

            $time_start2      = $this->format_lottery_limit('10:00:00');
            $time_end2        = $this->format_lottery_limit('22:00:00');

            $time_start3      = $this->format_lottery_limit('22:00:00');
            $time_end3        = $this->format_lottery_limit('23:59:59')+1;

            //开奖间隔
            $limit_time       = $this->getLotteryTime();

            $table_name       = 'lottery_cqssc';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('09:50:00')) {
                cache($cacheDataKey,$time_start2 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime > $time_end1 && $this->nowTime < $time_start2) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("重庆时时彩下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('重庆时时彩开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
          break;
        case 93:
            //新疆时时彩 00-02 10:10-23:59
            $time_start1      = $this->format_lottery_limit('00:00:00');
            $time_end1        = $this->format_lottery_limit('02:00:00');

            $time_start2      = $this->format_lottery_limit('10:10:00');
            $time_end2        = $this->format_lottery_limit('23:59:59')+1;
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_xjssc';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('10:00:00')) {
                cache($cacheDataKey,$time_start2 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime > $time_end1 && $this->nowTime < $time_start2) return false;
            
            $opentimestamp    = cache($cacheDataKey);
            wr("新疆时时彩下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('新疆时时彩开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 94:
            //黑龙江时时彩  09:30-23:30
            $time_start1      = $this->format_lottery_limit('09:30:00');
            $time_end1        = $this->format_lottery_limit('23:30:00');

            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_hljssc';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('09:20:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("黑龙江时时彩下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('黑龙江时时彩开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 95:
            //天津时时彩 09:10-22:55
            $time_start1      = $this->format_lottery_limit('09:10:00');
            $time_end1        = $this->format_lottery_limit('22:55:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_tjssc';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:4:00') && $this->nowTime < $this->format_lottery_limit('09:00:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("天津时时彩下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('天津时时彩开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 97:
            //北京PK拾 09:02-23:57
            $time_start1      = $this->format_lottery_limit('09:02:00');
            $time_end1        = $this->format_lottery_limit('23:57:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_bjpk10';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('08:52:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("北京PK拾下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('北京PK拾开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 100:
            //香港六合彩 不定期开奖 系统设置每天 21:35-22:00查询开奖信息
            $time_start1      = $this->format_lottery_limit('00:00:00');
            $time_end1        = $this->format_lottery_limit('24:00:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_hk6';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;
            
            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('21:25:00')) {
                cache($cacheDataKey,$this->format_lottery_limit('21:36:00') + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("香港六合彩下期预计开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('香港六合彩开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 103:
            //安徽快三 08:50-22:00 80期 10分钟一开
            $time_start1      = $this->format_lottery_limit('08:50:00');
            $time_end1        = $this->format_lottery_limit('22:00:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_ahk3';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('08:40:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("安徽快三下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('安徽快三开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 104:
            //吉林快三 08:30-21:30 87期 9分钟一开
            $time_start1      = $this->format_lottery_limit('08:30:00');
            $time_end1        = $this->format_lottery_limit('21:30:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_jlk3';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('08:20:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("吉林快三下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('吉林快三开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 105:

            //广西快三 09:38-22:28 78期 10分钟一开
            $time_start1      = $this->format_lottery_limit('09:20:00');
            $time_end1        = $this->format_lottery_limit('22:40:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_gxk3';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('09:28:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("广西快三下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('广西快三开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 106:
            //江苏快三 08:40-22:10 82期 10分钟一开
            $time_start1      = $this->format_lottery_limit('08:40:00');
            $time_end1        = $this->format_lottery_limit('22:10:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_jsk3';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('08:30:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("江苏快三下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('江苏快三开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 107:
            //湖北快三 09:10-22:00 78期 10分钟一开
            $time_start1      = $this->format_lottery_limit('09:10:00');
            $time_end1        = $this->format_lottery_limit('22:00:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_hubk3';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('09:00:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("湖北快三下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('湖北快三开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 109:
            //山东11选5 08:31-22:11 78期 10分钟一开
            $time_start1      = $this->format_lottery_limit('08:31:00');
            $time_end1        = $this->format_lottery_limit('22:11:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_sd11x5';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('08:21:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("山东11选5下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('山东11选5开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 110:
            //广东11选5 09:11-23:01 84期 10分钟一开
            $time_start1      = $this->format_lottery_limit('09:11:00');
            $time_end1        = $this->format_lottery_limit('23:01:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_gd11x5';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('09:01:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("广东11选5下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('广东11选5开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 111:
            //上海11选5 09:00-23:50 90期 10分钟一开
            $time_start1      = $this->format_lottery_limit('09:00:00');
            $time_end1        = $this->format_lottery_limit('23:50:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_sh11x5';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('04:40:00') && $this->nowTime < $this->format_lottery_limit('08:50:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("上海11选5下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('上海11选5开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 112:
            //江苏11选5 08:37-22:07 82期 10分钟一开
            $time_start1      = $this->format_lottery_limit('08:37:00');
            $time_end1        = $this->format_lottery_limit('22:07:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_js11x5';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('08:27:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("江苏11选5下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('江苏11选5开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 113:
            //湖北11选5 08:35-21:56 81期 10分钟一开
            $time_start1      = $this->format_lottery_limit('08:35:00');
            $time_end1        = $this->format_lottery_limit('21:56:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_hub11x5';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('04:40:00') && $this->nowTime < $this->format_lottery_limit('08:25:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("湖北11选5下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('湖北11选5开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 114:
            //广西11选5 09:02-23:52 90期 10分钟一开
            $time_start1      = $this->format_lottery_limit('09:02:00');
            $time_end1        = $this->format_lottery_limit('23:52:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_gx11x5';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('04:40:00') && $this->nowTime < $this->format_lottery_limit('08:52:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("广西11选5下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('广西11选5开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        case 116:
            //北京28 09:05-23:55 179期 5分钟一开
            $time_start1      = $this->format_lottery_limit('09:05:00');
            $time_end1        = $this->format_lottery_limit('23:55:00');
            $limit_time       = $this->getLotteryTime();
            $table_name       = 'lottery_bjkl8';
            $cacheDataKey     = 'updateData_'.$table_name.'_opentimestamp_' . $this->lotteryid;

            //设置第二天首开时间
            if ($this->nowTime>$this->format_lottery_limit('02:40:00') && $this->nowTime < $this->format_lottery_limit('08:55:00')) {
                cache($cacheDataKey,$time_start1 + 60*1);
            }

            //不在预售时间范围内 数据不用更新
            if ($this->nowTime < $time_start1 || $this->nowTime > $time_end1) return false;

            $opentimestamp    = cache($cacheDataKey);
            wr("北京28下期开奖时间：".date('Y-m-d H:i:s',$opentimestamp)."\n");
            //未到开奖时间数据不更新
            if (!empty($opentimestamp) && $opentimestamp > $this->nowTime)  return false;
            wr('北京28开始开奖');
            $this->saveLottery(model($table_name),$limit_time,$cacheDataKey);
            break;
        default:
          return true;
          break;
      }

      return true;
    }

    private function saveLottery($dbmodel,$limit_time,$cacheDataKey)
    {
        //这里做一个数据请求的延迟保护
        $delayKey       = "delay_saveLottery_3s_".$this->lotteryid;
        $delay          = cache($delayKey);
        if (!empty($delay) && $delay > $this->nowTime) {
            wr($this->lotteryid."：延迟3秒开奖" );return false;
        }

        cache($delayKey,$this->nowTime+3);

        //开始开奖
        wr("start===========" .date('Y-m-d H:i:s'));
        //获取最近一条数据入库
        $data         = $this->getData();
        if (!empty($data) && isset($data[0])) {

            //缓存时间间隔
            $nextOpenTime             = $data[0]['opentimestamp'] + 60*$limit_time;
            cache($cacheDataKey,$nextOpenTime);

            $opdata                   = $data[0];
            $expect                   = $opdata['expect'];
            $info                     = $dbmodel->getLotteryInfoByExpect($expect);
            $lastItem                 = [92=>120,93=>96,95=>84,103=>80,104=>87,105=>78,106=>82,107=>78,109=>78,110=>84,111=>90,112=>82,113=>81,114=>90];
            $item                     = intval(substr($expect,-3));
            $opencode2                = '';

            //这里判断是不是最后一期
            switch ($this->lotteryid) {
                case 94:
                    //黑龙江期数设置
                    $nextExpect       = ((intval($expect)+1) >= 1000000)?(intval($expect)+1):'0'.(intval($expect)+1);
                    $nextItem         = intval($expect)+1;
                    $term_number      = intval($expect);
                    break;
                case 97:
                    //北京PK拾
                    $nextExpect       = intval($expect)+1;
                    $nextItem         = intval($expect)+1;
                    $term_number      = intval($expect);
                    break;
                case 100:
                    //香港六合彩
                    $term_number      = substr($expect,-4);
                    $openyear1        = date('Y',$opdata['opentimestamp']);
                    $openyear2        = date('Y',$opdata['opentimestamp']+3600*24*2);

                    if ($openyear1 != $openyear2) {
                        $nextExpect           = substr(date('Ymd'),0,4) . '001';
                        $nextItem             = substr($nextExpect,-4);
                    }else{
                        $nextExpect           = substr(date('Ymd'),0,3) . (substr($expect,-4)+1);
                        $nextItem             = substr($expect,-4)+1;
                    }
                    break;
                case 116:
                    //北京28需要特殊处理
                    $term_number        = substr($expect,-4);
                    $nextItem           = substr($expect,-4)+1;
                    $nextExpect         = intval($expect)+1;
                    $opencode2          = $opdata['opencode'];
                    $opdata['opencode'] = $this->getbj28code($opencode2);
                    # code...
                    break;
                default:
                    $term_number          = substr($expect,-4);

                    if ($this->lotteryid >= 109 && $this->lotteryid <= 114) {
                        //$temp_expect1   = str_replace(substr($expect,0,8), substr($expect,0,8).'0', $expect);
                        $temp_expect2   = intval(substr($expect,-2))+1;
                        $temp_expect2   = $temp_expect2 > 9 ? $temp_expect2 : '0'.$temp_expect2;

                        $term_number    = intval("10".substr($term_number,-2));

                        if (isset($lastItem[$this->lotteryid]) && $lastItem[$this->lotteryid]<= $temp_expect2) {
                            $nextExpect           = substr($expect,0,8) . '01';
                            $nextItem             = '1001';
                        }else{

                            $nextExpect           = substr($expect,0,8) . $temp_expect2;
                            $nextItem             = $term_number+1;
                        }
                    }else{

                        if (isset($lastItem[$this->lotteryid]) && $lastItem[$this->lotteryid]<= $item) {
                            //最后一期 快三
                            if (in_array($this->lotteryid,[103,104,105,106,107])) {
                                $nextExpect           = substr(date('Ymd',time()+6*3600),0,8) . '001';
                            }else{
                                $nextExpect           = substr(date('Ymd'),0,8) . '001';
                            }

                            $nextItem             = substr($nextExpect,-4);
                        }else{
                            $tempNum              = intval(substr($expect,-4))+1;
                            if ($tempNum > 0 && $tempNum < 10) {
                                $tempNum          = '000'.$tempNum;
                            }elseif($tempNum >10 && $tempNum < 100) {
                                $tempNum          = '00'.$tempNum;
                            }elseif ($tempNum >= 100 && $tempNum < 1000) {
                                $tempNum          = '0'.$tempNum;
                            }
                            
                            $nnn   = $this->lotteryid == 89 ? 8 : 7;

                            $nextExpect           = substr(date('Ymd'),0,$nnn) . $tempNum;
                            $nextItem             = $tempNum;
                        }
                    }
                    
                    break;
            }

            $delay_time   = [89=>10,90=>30,92=>60,93=>60,94=>60,95=>60,97=>60,98=>60,100=>60,101=>60,103=>60,104=>60,105=>60,106=>60,107=>60,109=>60,110=>60,111=>60,112=>60,113=>60,114=>60,116=>60];
            $delay        = isset($delay_time[$this->lotteryid]) ? $delay_time[$this->lotteryid] : 0;

            if (empty($info) || $info['status'] == 2) {

                //删除预存未开奖数据
                $dbmodel->delLotteryInfoByExpect($expect);
                $updata = [
                    [
                    'status'=>1,
                    'lotterid'=>$this->lotteryid,
                    'expect'=>$opdata['expect'],
                    'opencode'=>$opdata['opencode'],
                    'opentime'=>date('Y-m-d H:i:s',strtotime($opdata['opentime'])+$delay),
                    'opentimestamp'=>$opdata['opentimestamp']+$delay,
                    'term_number'=>$term_number,
                    'create_time'=>$this->nowTime,
                    'opencode2'=>$opencode2,
                    ],
                    [
                    'status'=>2,
                    'expect'=>$nextExpect,
                    'lotterid'=>$this->lotteryid,
                    'opentime'=>date('Y-m-d H:i:s' ,$nextOpenTime+$delay),
                    'opentimestamp'=>$nextOpenTime+$delay,
                    'term_number'=>$nextItem,
                    'create_time'=>$this->nowTime
                    ]
                ];

                $dbmodel->saveLotteryInfoAll($updata);

                //推送开奖信息
                $this->jpushOpenLotterInfo();
            }

            wr("over===========" .date('Y-m-d H:i:s') ."\n");
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
}