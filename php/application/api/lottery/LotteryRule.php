<?php
/**
 * 彩票玩法
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\lottery;

use think\Controller;
use think\facade\Lang;

class LotteryRule extends Base
{   
    private $nowTime;
    private $lotteryConfig;
    private $error              = [];
    public function __construct($lotteryid=0)
    {
      parent::__construct($lotteryid);
      $this->nowTime            = time();
      $this->lotteryConfig      = config('lottery.');
    }

    private function setError($error=[])
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    private function checkRules($rules='')
    {
        if ( empty($rules) || !is_string($rules)) return false;

        $rules      = explode('-',$rules);
        if (count($rules) !== 3) return false;

        return true;
    }

    //计算彩票注数
    public function getLotteryBetNumber($rules,$num5,$num4,$num3,$num2,$num1)
    {   
        $this->setError(['Code' => '200004', 'Msg'=>lang('200004')]);
        if (!$this->checkRules($rules)) {
            $this->setError(['Code' => '200002', 'Msg'=>lang('200002')]);return 0;
        }

        $PermutationCombination = new \app\api\lottery\PermutationCombination();
        switch ($rules) {
            //时时彩-五星
            case '88-1-1'://复式
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,5);break;
            case '88-1-2'://单式
                $permutation    = $PermutationCombination->single_form($num5,5);break;
            case '88-1-3'://组合
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,5);
                if ($permutation[0] > 0){
                    $permutation[0] = $permutation[0]*5;
                }
                return $permutation;break;
            case '88-1-4'://组选120
                $permutation    = $PermutationCombination->star_zuxuan($num5,5);break;
            case '88-1-5'://组选60
                $permutation    = $PermutationCombination->star_zuxuan2($num5,$num4,3);break;
            case '88-1-6'://组选30
                $permutation    = $PermutationCombination->fiveStar_x30($num5,$num4);break;
            case '88-1-7'://组选20
                $permutation    = $PermutationCombination->star_zuxuan3($num5,$num4,2);break;
            case '88-1-8'://组选10
                $permutation    = $PermutationCombination->fiveStar_x10($num5,$num4);break;
            case '88-1-9'://组选5
                $permutation    = $PermutationCombination->fiveStar_x5($num5,$num4);break;

            //时时彩-后二星-直选
            case '88-2-1'://复试
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,2);break;
            case '88-2-2'://单试
                $permutation    = $PermutationCombination->single_form($num5,2);break;
            case '88-2-3'://和值
                $permutation    = $PermutationCombination->zhix_hz($num5,2);break;
            case '88-2-4'://跨度
                $permutation    = $PermutationCombination->zhix_kd($num5,2);break;
            case '88-2-5'://和值尾数
                $permutation    = $PermutationCombination->zhix_hzws($num5);break;
            //时时彩-后二星-组选
            case '88-2-6'://复试
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-2-7'://单试
                $permutation    = $PermutationCombination->single_form($num5,1);break;

            //时时彩-前二星-直选
            case '88-2-8'://复试
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,2);break;
            case '88-2-9'://单试
                $permutation    = $PermutationCombination->single_form($num5,2);break;
            case '88-2-10'://和值
                $permutation    = $PermutationCombination->zhix_hz($num5,2);break;
            case '88-2-11'://跨度
                $permutation    = $PermutationCombination->zhix_kd($num5,2);break;
            case '88-2-12'://和值尾数
                $permutation    = $PermutationCombination->zhix_hzws($num5);break;
            
            //时时彩-前二星-组选
            case '88-2-13'://复试
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-2-14'://单试
                $permutation    = $PermutationCombination->single_form($num5,2);break;

            //时时彩-定位胆-定位胆
            case '88-3-1':
                $permutation    = $PermutationCombination->locationGall($num5,$num4,$num3,$num2,$num1);break;

            //时时彩-不定位
            case '88-4-1'://三星-后三一码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-4-2'://三星-中三一码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-4-3'://三星-前三一码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-4-4'://三星-后三二码
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-4-5'://三星-后三二码
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-4-6'://三星-后三二码
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-4-7'://四星-前四一码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-4-8'://四星-后四一码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-4-9'://四星-前四二码
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-4-10'://四星-后四二码
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-4-11'://四星-前四三码
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '88-4-12'://四星-后四三码
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '88-4-13'://五星-一码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-4-14'://五星-二码
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-4-15'://五星-三码
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '88-4-16'://五星-四码
                $permutation    = $PermutationCombination->star_zuxuan($num5,4);break;

            //时时彩-任选
            case '88-5-1'://二星-复试
                $permutation    = $PermutationCombination->star_rx_fs($num5,$num4,$num3,$num2,$num1,2);break;
            case '88-5-2'://二星-单式
                $permutation    = $PermutationCombination->rx_single_form($num5,$num4,2,1);break;
            case '88-5-3'://二星-组合
                $permutation    = $PermutationCombination->rx_star_zuxuan($num5,$num4,2,2);break;
            case '88-5-4'://三星-复试
                $permutation    = $PermutationCombination->star_rx_fs($num5,$num4,$num3,$num2,$num1,3);break;
            case '88-5-5'://三星-单式
                $permutation    = $PermutationCombination->rx_single_form($num5,$num4,3);break;
            case '88-5-6'://三星-组合三
                $permutation    = $PermutationCombination->rx_star_zuxuan($num5,$num4,2,3);break;
            case '88-5-7'://三星-组合六
                $permutation    = $PermutationCombination->rx_star_zuxuan($num5,$num4,3,3);break;
            case '88-5-8'://三星-混合组
                $permutation    = $PermutationCombination->rx_single_form($num5,$num4,3,3);break;
            case '88-5-9'://四星-复试
                $permutation    = $PermutationCombination->star_rx_fs($num5,$num4,$num3,$num2,$num1,4);break;
            case '88-5-10'://四星-单式
                $permutation    = $PermutationCombination->rx_single_form($num5,$num4,4,1);break;

            //时时彩 趣味
            case '88-6-1'://一帆风顺
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-6-2'://好事成双
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-6-3'://三星报喜
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '88-6-4'://四季发财
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;

            //时时彩 龙虎和
            case '88-7-1'://万千
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-2'://万百
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-3'://万十
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-4'://万个
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-5'://千百
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-6'://千十
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-7'://千个
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-8'://百十
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-9'://百个
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;
            case '88-7-10'://十个
                $permutation    = $PermutationCombination->xuan_long_hu_he($num5);break;

            //时时彩 大小单双
            case '88-8-1'://总和
                $permutation    = $PermutationCombination->xuan_da_xiao_dan_shuang($num5);break;
            case '88-8-2'://万位
                $permutation    = $PermutationCombination->xuan_da_xiao_dan_shuang($num5);break;
            case '88-8-3'://千位
                $permutation    = $PermutationCombination->xuan_da_xiao_dan_shuang($num5);break;
            case '88-8-4'://百位
                $permutation    = $PermutationCombination->xuan_da_xiao_dan_shuang($num5);break;
            case '88-8-5'://十位
                $permutation    = $PermutationCombination->xuan_da_xiao_dan_shuang($num5);break;
            case '88-8-6'://个位
                $permutation    = $PermutationCombination->xuan_da_xiao_dan_shuang($num5);break;
            case '88-8-7'://串关
                $permutation    = $PermutationCombination->xuan_da_xiao_dan_shuang_cg($num5,$num4,$num3,$num2,$num1);break;

            //时时彩 特殊号
            case '88-9-1'://前三
                $permutation    = $PermutationCombination->xuan_te_shu_hao($num5);break;
            case '88-9-2'://中三
                $permutation    = $PermutationCombination->xuan_te_shu_hao($num5);break;
            case '88-9-3'://后三
                $permutation    = $PermutationCombination->xuan_te_shu_hao($num5);break;


            //时时彩 牛牛
            case '88-10-1':
                $permutation    = $PermutationCombination->xuan_niuniu($num5);break;


            //时时彩-后四星-直选
            case '88-11-1'://复式
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,4);break;
            case '88-11-2'://单式
                $permutation    = $PermutationCombination->single_form($num5,4);break;
            case '88-11-3'://组合star_zuxuan
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,4);
                if ($permutation[0] > 0){
                    $permutation[0] = $permutation[0]*4;
                }
                return $permutation;break;
            //时时彩-后四星-组选
            case '88-11-4'://组选24
                $permutation    = $PermutationCombination->star_zuxuan($num5,4);break;
            case '88-11-5'://组选12
                $permutation    = $PermutationCombination->star_zuxuan2($num5,$num4,2);break;
            case '88-11-6'://组选6
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-11-7'://组选4
                $permutation    = $PermutationCombination->star_zuxuan3($num5,$num4,1);break;
            //时时彩-前四星-直选
            case '88-11-8'://复式
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,4);break;
            case '88-11-9'://单式
                $permutation    = $PermutationCombination->single_form($num5,4);break;
            case '88-11-10'://组合star_zuxuan
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,4);
                if ($permutation[0] > 0){
                    $permutation[0] = $permutation[0]*4;
                }
                return $permutation;break;
            //时时彩-前四星-组选
            case '88-11-11'://组选24
                $permutation    = $PermutationCombination->star_zuxuan($num5,4);break;
            case '88-11-12'://组选12
                $permutation    = $PermutationCombination->star_zuxuan2($num5,$num4,2);break;
            case '88-11-13'://组选6
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '88-11-14'://组选4
                $permutation    = $PermutationCombination->star_zuxuan3($num5,$num4,1);break;

            //时时彩-后三星-直选
            case '88-12-1'://复式
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,3);break;
            case '88-12-2'://单式
                $permutation    = $PermutationCombination->single_form($num5,3);break;
            case '88-12-3'://和值
                $permutation    = $PermutationCombination->zhix_hz($num5,3);break;
            case '88-12-4'://跨度
                $permutation    = $PermutationCombination->zhix_kd($num5,3);break;
            case '88-12-5'://和值尾数
                $permutation    = $PermutationCombination->zhix_hzws($num5);break;
            //时时彩-后三星-组选
            case '88-12-6'://组选三
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);
                if ($permutation[0] > 0){
                    $permutation[0] = $permutation[0]*2;
                }
                return $permutation;break;
            case '88-12-7'://组选六
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '88-12-8'://混合组
                $permutation    = $PermutationCombination->single_form($num5,3,2);break;
            //时时彩-中三星-直选
            case '88-12-9'://复式
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,3);break;
            case '88-12-10'://单式
                $permutation    = $PermutationCombination->single_form($num5,3);break;
            case '88-12-11'://和值
                $permutation    = $PermutationCombination->zhix_hz($num5,3);break;
            case '88-12-12'://跨度
                $permutation    = $PermutationCombination->zhix_kd($num5,3);break;
            case '88-12-13'://和值尾数
                $permutation    = $PermutationCombination->zhix_hzws($num5);break;
            //时时彩-中三星-组选
            case '88-12-14'://组选三
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);
                if ($permutation[0] > 0){
                    $permutation[0] = $permutation[0]*2;
                }
                return $permutation;break;
            case '88-12-15'://组选六
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '88-12-16'://混合组
                $permutation    = $PermutationCombination->single_form($num5,3,2);break;
            //时时彩-前三星-直选
            case '88-12-17'://复式
                $permutation = $PermutationCombination->star_zhix_fs($num5,$num4,$num3,$num2,$num1,3);break;
            case '88-12-18'://单式
                $permutation    = $PermutationCombination->single_form($num5,3);break;
            case '88-12-19'://和值
                $permutation    = $PermutationCombination->zhix_hz($num5,3);break;
            case '88-12-20'://跨度
                $permutation    = $PermutationCombination->zhix_kd($num5,3);break;
            case '88-12-21'://和值尾数
                $permutation    = $PermutationCombination->zhix_hzws($num5);break;
            //时时彩-前三星-组选
            case '88-12-22'://组选三
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);
                if ($permutation[0] > 0){
                    $permutation[0] = $permutation[0]*2;
                }
                return $permutation;break;
            case '88-12-23'://组选六
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '88-12-24'://混合组
                $permutation    = $PermutationCombination->single_form($num5,3,2);break;

            //PK拾 
            case '96-1-1': //前一-前一-前一
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-2-1': //前二-前二-前二复式
                $permutation    = $PermutationCombination->star_zuxuan_96($num5,$num4,$num3,$num2,$num1,2);break;
            case '96-2-2': //前二-前二-前二单式
                $permutation    = $PermutationCombination->single_form_96($num5,2);break;
            case '96-3-1': //前三-前三-前三复式
                $permutation    = $PermutationCombination->star_zuxuan_96($num5,$num4,$num3,$num2,$num1,3);break;
            case '96-3-2': //前三-前三-前三单式
                $permutation    = $PermutationCombination->single_form_96($num5,3);break;
            case '96-4-1': //定位胆-定位胆-第1~5名
                $permutation    = $PermutationCombination->locationGall($num5,$num4,$num3,$num2,$num1);break;
            case '96-4-2': //定位胆-定位胆-第6~10名
                $permutation    = $PermutationCombination->locationGall($num5,$num4,$num3,$num2,$num1);break;
            case '96-5-1': //冠亚和-冠亚和-和值
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-6-1': //龙虎-龙虎-冠军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-6-2': //龙虎-龙虎-亚军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-6-3': //龙虎-龙虎-季军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-6-4': //龙虎-龙虎-第四名
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-6-5': //龙虎-龙虎-第五名
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-7-1': //五行-五行-冠军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-7-2': //五行-五行-亚军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-7-3': //五行-五行-季军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-8-1': //大小单双-大小-冠军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-8-2': //大小单双-大小-亚军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-8-3': //大小单双-大小-季军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-8-4': //大小单双-单双-冠军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-8-5': //大小单双-单双-亚军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-8-6': //大小单双-单双-季军
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '96-8-7': //大小单双-冠亚和-大小单双
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            //六合彩
            case '99-1-1'://两面-两面-两面
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-2-1'://特码-特码-特码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-3-1'://正码-正码-正码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-4-1'://正码特-正码特-正一特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-4-2'://正码特-正码特-正二特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-4-3'://正码特-正码特-正三特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-4-4'://正码特-正码特-正四特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-4-5'://正码特-正码特-正五特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-4-6'://正码特-正码特-正六特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-5-1'://正码1~6-正码1~6-正一特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-5-2'://正码1~6-正码1~6-正二特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-5-3'://正码1~6-正码1~6-正三特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-5-4'://正码1~6-正码1~6-正四特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-5-5'://正码1~6-正码1~6-正五特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-5-6'://正码1~6-正码1~6-正六特
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-6-1'://正码过关-正码过关-正码过关
                $permutation    = $PermutationCombination->zmgg_hc6($num5);break;
            case '99-7-1'://连码-连码-四全中
                $permutation    = $PermutationCombination->star_zuxuan($num5,4,2);break;
            case '99-7-2'://连码-正码过关-三全中
                $permutation    = $PermutationCombination->star_zuxuan($num5,3,2);break;
            case '99-7-3'://连码-连码-三中二
                $permutation    = $PermutationCombination->star_zuxuan($num5,3,2);break;
            case '99-7-4'://连码-连码-二全中
                $permutation    = $PermutationCombination->star_zuxuan($num5,2,2);break;
            case '99-7-5'://连码-连码-二中特
                $permutation    = $PermutationCombination->star_zuxuan($num5,2,2);break;
            case '99-7-6'://连码-连码-特串
                $permutation    = $PermutationCombination->star_zuxuan($num5,2,2);break;

            case '99-8-1'://连肖连尾-连肖连尾-二连肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,2,1);break;
            case '99-8-2'://连肖连尾-连肖连尾-三连肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,3,1);break;
            case '99-8-3'://连肖连尾-连肖连尾-四连肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,4,1);break;
            case '99-8-4'://连肖连尾-连肖连尾-五连肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,5,1);break;
            case '99-8-5'://连肖连尾-连肖连尾-二连尾
                $permutation    = $PermutationCombination->star_zuxuan($num5,2,1);break;
            case '99-8-6'://连肖连尾-连肖连尾-三连尾
                $permutation    = $PermutationCombination->star_zuxuan($num5,3,1);break;
            case '99-8-7'://连肖连尾-连肖连尾-四连尾
                $permutation    = $PermutationCombination->star_zuxuan($num5,4,1);break;
            case '99-8-8'://连肖连尾-连肖连尾-五连尾
                $permutation    = $PermutationCombination->star_zuxuan($num5,5,1);break;

            case '99-9-1'://自选不中-自选不中-五不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,5,2);break;
            case '99-9-2'://自选不中-自选不中-六不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,6,2);break;
            case '99-9-3'://自选不中-自选不中-七不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,7,2);break;
            case '99-9-4'://自选不中-自选不中-八不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,8,3);break;
            case '99-9-5'://自选不中-自选不中-九不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,9,4);break;
            case '99-9-6'://自选不中-自选不中-十不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,10,5);break;
            case '99-9-7'://自选不中-自选不中-十一不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,11,5);break;
            case '99-9-8'://自选不中-自选不中-十二不中
                $permutation    = $PermutationCombination->star_zuxuan($num5,12,6);break;
            case '99-10-1'://生肖-生肖-正肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-10-2'://生肖-生肖-特肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-10-3'://生肖-生肖-一肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-10-4'://生肖-生肖-总肖
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            /*合肖*/
            case '99-11-1'://生肖-生肖-总肖
                $permutation    = $PermutationCombination->hk6_hx($num5);break;
            /*色波*/
            case '99-12-1'://色波-色波-三色波
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-12-2'://色波-色波-半波
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-12-3'://色波-色波-半半波
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-12-4'://尾数-尾数-七色波
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-13-1'://尾数-尾数-头尾数
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-13-2'://尾数-尾数-正特尾数
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-14-1'://七码五行-七码五行-七码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-14-2'://七码五行-七码五行-五行
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '99-15-1'://中一-中一-五中一
                $permutation    = $PermutationCombination->star_zuxuan($num5,5,2);break;
            case '99-15-2'://中一-中一-六中一
                $permutation    = $PermutationCombination->star_zuxuan($num5,6,2);break;
            case '99-15-3'://中一-中一-七中一
                $permutation    = $PermutationCombination->star_zuxuan($num5,7,2);break;
            case '99-15-4'://中一-中一-八中一
                $permutation    = $PermutationCombination->star_zuxuan($num5,8,3);break;
            case '99-15-5'://中一-中一-九中一
                $permutation    = $PermutationCombination->star_zuxuan($num5,9,4);break;
            case '99-15-6'://中一-中一-十中一
                $permutation    = $PermutationCombination->star_zuxuan($num5,10,5);break;

            //快三
            case '102-1-1'://二不同号-二不同号-标准选号
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '102-1-2'://二不同号-二不同号-手动选号
                $permutation    = $PermutationCombination->single_form_k3($num5,2);break;
            case '102-1-3'://二不同号-二不同号-手动选号
                $permutation    = $PermutationCombination->tuandan_k3($num5,$num4,1);break;
            case '102-2-1'://二同号-二同号单选-标准选号
                $permutation    = $PermutationCombination->ertonghao_k3($num5,$num4,1);break;
            case '102-2-2'://二同号-二同号单选-手动选号
                $permutation    = $PermutationCombination->ertonghao_k3($num5,$num4,2);break;
            case '102-2-3'://二同号-二同号复选-二同号复选
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '102-3-1'://三不同号-三不同号-标准选号
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '102-3-2'://三不同号-三不同号-手动选号
                $permutation    = $PermutationCombination->ertonghao_k3($num5,$num4,3);break;
            case '102-4-1'://三同号-三同号单选-三同号单选
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '102-4-2'://三同号-三同号通选-三同号通选
                $permutation    = $PermutationCombination->tx_k3(0);break;
            case '102-5-1'://三连号-三连号通选-三连号通选
                $permutation    = $PermutationCombination->tx_k3(1);break;

            //11选5
            case '108-1-1'://三码-前三直选-复式
                $permutation    = $PermutationCombination->star_zuxuan_96($num5,$num4,$num3,$num2,$num1,3);break;
            case '108-1-2': //三码-前三直选-单式
                $permutation    = $PermutationCombination->single_form_96($num5,3);break;
            case '108-1-3': //三码-前三组选-复式
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '108-1-4': //三码-前三组选-单式
                $permutation    = $PermutationCombination->single_form_96($num5,3);break;
            case '108-2-1'://二码-二码直选-复式
                $permutation    = $PermutationCombination->star_zuxuan_96($num5,$num4,$num3,$num2,$num1,2);break;
            case '108-2-2': //二码-二码直选-单式
                $permutation    = $PermutationCombination->single_form_96($num5,2);break;
            case '108-2-3': //二码-二码组选-复式
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '108-2-4': //二码-二码组选-单式
                $permutation    = $PermutationCombination->single_form_96($num5,2);break;
            case '108-3-1': //不定胆-不定胆-前三位
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '108-4-1': //定位胆-定位胆-定位胆
                $permutation = $PermutationCombination->locationGall($num5,$num4,$num3,$num2,$num1);break;
            case '108-5-1': //任选-任选复式-一中一
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '108-5-2': //任选-任选复式-二中二
                $permutation    = $PermutationCombination->star_zuxuan($num5,2);break;
            case '108-5-3': //任选-任选复式-三中三
                $permutation    = $PermutationCombination->star_zuxuan($num5,3);break;
            case '108-5-4': //任选-任选复式-四中四
                $permutation    = $PermutationCombination->star_zuxuan($num5,4);break;
            case '108-5-5': //任选-任选复式-五中五
                $permutation    = $PermutationCombination->star_zuxuan($num5,5);break;
            case '108-5-6': //任选-任选复式-六中五
                $permutation    = $PermutationCombination->star_zuxuan($num5,6);break;
            case '108-5-7': //任选-任选复式-七中五
                $permutation    = $PermutationCombination->star_zuxuan($num5,7);break;
            case '108-5-8': //任选-任选复式-八中五
                $permutation    = $PermutationCombination->star_zuxuan($num5,8);break;
            case '108-5-9': //任选-任选单式-一中一
                $permutation    = $PermutationCombination->single_form_108($num5,1);break;
            case '108-5-10': //任选-任选单式-二中二
                $permutation    = $PermutationCombination->single_form_108($num5,2);break;
            case '108-5-11': //任选-任选单式-三中三
                $permutation    = $PermutationCombination->single_form_108($num5,3);break;
            case '108-5-12': //任选-任选单式-四中四
                $permutation    = $PermutationCombination->single_form_108($num5,4);break;
            case '108-5-13': //任选-任选单式-五中五
                $permutation    = $PermutationCombination->single_form_108($num5,5);break;
            case '108-5-14': //任选-任选单式-六中五
                $permutation    = $PermutationCombination->single_form_108($num5,6);break;
            case '108-5-15': //任选-任选单式-七中五
                $permutation    = $PermutationCombination->single_form_108($num5,7);break;
            case '108-5-16': //任选-任选单式-八中五
                $permutation    = $PermutationCombination->single_form_108($num5,8);break;
            //PC蛋蛋
            case '115-1-1': //特码-特码-特码
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '115-2-1': //混合-混合-混合
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '115-3-1': //波色-波色-波色
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '115-4-1': //豹子-豹子-豹子
                $permutation    = $PermutationCombination->star_zuxuan($num5,1);break;
            case '115-5-1': //特码包三-特码包三-特码包三
                $permutation    = $PermutationCombination->pc_dd_b3($num5);break;
            default:$this->setError(['Code' => '200002', 'Msg'=>lang('200002')]);return 0;break;
        }

        return $permutation;
    }

    //计算赔率
    public function getLotteryOdds($rules,$num5,$num4,$num3,$num2,$num1)
    {
        
    }

    //计算是否中奖
    public function getLotteryWinning($rules)
    {
        $this->setError(['Code' => '200004', 'Msg'=>lang('200004')]);
        if (!$this->checkRules($rules)) {
            $this->setError(['Code' => '200002', 'Msg'=>lang('200002')]);return 0;
        }
    }
}