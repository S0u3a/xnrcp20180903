<?php
/**
 * 彩票数字排列组合
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\lottery;

class PermutationCombination extends Base
{   
    // 阶乘  
    public function factorial($n) {  
        return array_product(range(1, $n));  
    }  
      
    // 排列数  
    public function A($n, $m) {
        return $this->factorial($n)/$this->factorial($n-$m);  
    }  
      
    // 组合数  
    public function C($n, $m) {  
        return $this->A($n, $m)/$this->factorial($m);  
    }  
      
    // 排列  
    public function arrangement($a, $m) {  
        $r = array();  
        
        $a = is_string($a) ? explode(',',$a) : $a;
        $n = count($a);  
        if ($m <= 0 || $m > $n) {  
            return $r;  
        }  
      
        for ($i=0; $i<$n; $i++) {  
            $b = $a;  
            $t = array_splice($b, $i, 1);  
            if ($m == 1) {  
                $r[] = $t;  
            } else {  
                $c = $this->arrangement($b, $m-1);  
                foreach ($c as $v) {  
                    $r[] = array_merge($t, $v);  
                }  
            }  
        }  
      
        return $r;  
    }  
      
    // 组合  
    public function combination($a, $m) {  
        $r = array();  
        
        $a = is_string($a) ? explode(',',$a) : $a;
        $n = count($a);  
        if ($m <= 0 || $m > $n) {  
            return $r;  
        }  
      
        for ($i=0; $i<$n; $i++) {  
            $t = array($a[$i]);  
            if ($m == 1) {  
                $r[] = $t;  
            } else {  
                $b = array_slice($a, $i+1);  
                $c = $this->combination($b, $m-1);  
                foreach ($c as $v) {  
                    $r[] = array_merge($t, $v);  
                }
            }  
        }  
      
        return $r;  
    } 
    
    public function formatNumber($num5,$num4,$num3,$num2,$num1)
    {
        $num5   = strlen($num5) > 0 ? explode(',',$num5) : [];
        $num4   = strlen($num4) > 0 ? explode(',',$num4) : [];
        $num3   = strlen($num3) > 0 ? explode(',',$num3) : [];
        $num2   = strlen($num2) > 0 ? explode(',',$num2) : [];
        $num1   = strlen($num1) > 0 ? explode(',',$num1) : [];

        $n1 = $n2 = $n3 = $n4 = $n5 = [];

        foreach($num5 as $v5) $n5[$v5] = $v5;
        foreach($num4 as $v4) $n4[$v4] = $v4;
        foreach($num3 as $v3) $n3[$v3] = $v3;
        foreach($num2 as $v2) $n2[$v2] = $v2;
        foreach($num1 as $v1) $n1[$v1] = $v1;

        return [$n5,$n4,$n3,$n2,$n1];
    }

    private function duplicate_removal_game($arr=[])
    {
        if (!empty($arr))
        {
            foreach ($arr as $key => $value)
            {
                $temp = [];

                for ($i=0; $i < strlen($value); $i++) $temp[] = $value[$i];

                sort($temp);

                $value      = implode('',$temp);
                $arr[$key]  = $value;
            }
        }
        
        return $arr;
    }

    public function single_form($num5='',$n=0,$type=0)
    {
        if (empty($num5) || $n <= 0)  return [0,[]];
        
        //有几个分割就是几注
        $C          = explode(',',$num5);

        if ($type == 2 || $type == 3) {
            $C = $this->duplicate_removal_game($C);
        }

        //去重
        $C          = array_flip(array_flip($C));

        $nn2        = ['00','11','22','33','44','55','66','77','88','99'];
        $nn3        = ['000','111','222','333','444','555','666','777','888','999'];

        foreach ($C as $kc=>$vc) {
            if (intval('1' . $vc) < pow(10,$n) || intval('1' . $vc) > pow(10,$n)*2) unset($C[$kc]);
            if ($type == 2 && in_array($vc,$nn2)) unset($C[$kc]);
            if ($type == 3 && in_array($vc,$nn3)) unset($C[$kc]);
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    //任意选-复式
    public function star_rx_fs($num5,$num4,$num3,$num2,$num1,$star=0)
    {
        if ($star <= 1)  return [0,[]];

        $num    = [];
        $num[5] = $num5;
        $num[4] = $num4;
        $num[3] = $num3;
        $num[2] = $num2;
        $num[1] = $num1;

        //万千百十个 两两组合
        $wqbsg = $this->star_zuxuan('5,4,3,2,1',$star);

        $CN     = 0;
        $C      = [];
        if ($wqbsg[0] > 0 && !empty($wqbsg[1])) {
            foreach ($wqbsg[1] as $key => $value) {

                $ckey           = implode('-',$value);
                $n              = 0;
                for ($i=0; $i < $star; $i++) { 
                    if (!empty(strlen($num[$value[$i]]) > 0)) $n ++;
                }
                if ($n != $star) continue;

                for ($i=0; $i < 5; $i++){
                    $value[$i] = isset($value[$i]) ? $value[$i] : 1;
                }

                $fs             = $this->star_zhix_fs($num[$value[0]],$num[$value[1]],$num[$value[2]],$num[$value[3]],$num[$value[4]],$star);
                $CN             += $fs[0];
                $C[$ckey]       = $fs[1];
            }
        }

        return [$CN,$C];
    }

    //任意选-单式
    public function rx_single_form($num5='',$num4='',$star=0,$type=0)
    {
        if (empty($num5) || empty($num4) || $star <= 0)  return [0,[]];

        //位置组合
        $wqbsg          = $this->star_zuxuan($num5,$star);
        $single_form    = $this->single_form($num4,$star,$type);
        $CN             = 0;
        $C              = [];

        $CN             = $wqbsg[0] * $single_form[0];

        if ($CN > 0){
            foreach ($wqbsg[1] as $key => $value) {
                $value2         = implode('-',$value);
                $C[$value2]     = $single_form[1];
            }
        }

        return [$CN,$C];
    }

    //龙虎和
    public function xuan_long_hu_he($num5='')
    {
        if (empty($num5))  return [0,[]];
        $num5       = explode(',',$num5);
        $num5       = array_flip(array_flip($num5));

        $lhh        = [3,2,1];//龙虎和
        foreach ($num5 as $key => $value) {
            if (!in_array($value,$lhh)) unset($num5[$key]);
        }

        $num5   = implode(',',$num5);
        return $this->star_zuxuan($num5,1);
    }

    //大小单双
    public function xuan_da_xiao_dan_shuang($num5='')
    {
        if (empty($num5))  return [0,[]];
        $num5       = explode(',',$num5);
        $num5       = array_flip(array_flip($num5));

        $lhh        = [4,3,2,1];//大小单双
        foreach ($num5 as $key => $value) {
            if (!in_array($value,$lhh)) unset($num5[$key]);
        }

        $num5   = implode(',',$num5);
        return $this->star_zuxuan($num5,1);
    }

    //特殊号 豹子,顺子，对子，半顺,杂六
    public function xuan_te_shu_hao($num5='')
    {
        if (empty($num5))  return [0,[]];
        $num5       = explode(',',$num5);
        $num5       = array_flip(array_flip($num5));

        $lhh        = [5,4,3,2,1];//豹子,顺子，对子，半顺,杂六
        foreach ($num5 as $key => $value) {
            if (!in_array($value,$lhh)) unset($num5[$key]);
        }

        $num5   = implode(',',$num5);
        return $this->star_zuxuan($num5,1);
    }

    //牛牛
    public function xuan_niuniu($num5='')
    {
        if (empty($num5))  return [0,[]];
        $num5       = explode(',',$num5);
        $num5       = array_flip(array_flip($num5));

        $lhh        = [10,9,8,7,6,5,4,3,2,1,0];//牛牛,牛九,牛八,牛七,牛六,牛五,牛四,牛三,牛二,牛一,无牛
        foreach ($num5 as $key => $value) {
            if (!in_array($value,$lhh)) unset($num5[$key]);
        }

        $num5   = implode(',',$num5);
        return $this->star_zuxuan($num5,1);
    }

    public function xuan_da_xiao_dan_shuang_cg($num5="",$num4="",$num3="",$num2="",$num1="")
    {
        if (strlen($num5)<=0||strlen($num4)<=0||strlen($num3)<=0||strlen($num2)<=0||strlen($num1)<=0)
        return [0,[]];
    
        $nn5 = $this->xuan_da_xiao_dan_shuang($num5);
        $nn4 = $this->xuan_da_xiao_dan_shuang($num4);
        $nn3 = $this->xuan_da_xiao_dan_shuang($num3);
        $nn2 = $this->xuan_da_xiao_dan_shuang($num2);
        $nn1 = $this->xuan_da_xiao_dan_shuang($num1);

        $CN5 = $nn5[0] > 0 ? $nn5[0] : 1;
        $CN4 = $nn4[0] > 0 ? $nn4[0] : 1;
        $CN3 = $nn3[0] > 0 ? $nn3[0] : 1;
        $CN2 = $nn2[0] > 0 ? $nn2[0] : 1;
        $CN1 = $nn1[0] > 0 ? $nn1[0] : 1;

        $CN  = $CN5*$CN4*$CN3*$CN2*$CN1;
        $C   = [];

        $C   = [$num5,$num4,$num3,$num2,$num1];

        return [$CN,$C];
    }

    //任意选 - 组选
    public function rx_star_zuxuan($num5='',$num4='',$star=0,$posnum=0)
    {
        if (strlen($num5)<=0 || strlen($num4)<=0 || $star <= 0 || $posnum <= 0)  return [0,[]];

        //位置组合
        $wqbsg          = $this->star_zuxuan($num5,$posnum);

        if ($star == 2 && $posnum == 3) {
            $star_zuxuan    = $this->star_pailie($num4,$star);
        }else{
            $star_zuxuan    = $this->star_zuxuan($num4,$star);
        }
        
        $CN             = 0;
        $C              = [];

        $CN             = $wqbsg[0] * $star_zuxuan[0];

        if ($CN > 0){
            foreach ($wqbsg[1] as $key => $value) {
                $value2         = implode('-',$value);
                $C[$value2]     = $star_zuxuan[1];
            }
        }

        return [$CN,$C];
    }

    //直选-复式
    public function star_zhix_fs($num5,$num4,$num3,$num2,$num1,$star=0)
    {   
        if ($star <= 1)  return [0,[]];

        $nums           = $this->formatNumber($num5,$num4,$num3,$num2,$num1);
        $CN             = 1;
        for ($i=0; $i < $star; $i++) { 
            $CN         *= count($nums[$i]);
        }

        $C              = [];
        if($CN > 0){
            switch ($star) {
                case 2:
                    foreach($nums[0] as $v0){//十位
                        foreach($nums[1] as $v1) $C[] = [$v0,$v1];//个位
                    }
                    break;
                case 3:
                    foreach($nums[0] as $v0){//百位
                        foreach($nums[1] as $v1){//十位
                            foreach($nums[2] as $v2) $C[] = [$v0,$v1,$v2];//个位
                        }
                    }
                    break;
                case 4:
                    foreach($nums[0] as $v0){//千位
                        foreach($nums[1] as $v1){//百位
                            foreach($nums[2] as $v2){//十位
                                foreach($nums[3] as $v3) $C[] = [$v0,$v1,$v2,$v3];//个位
                            }
                        }
                    }
                    break;
                case 5:
                    foreach($nums[0] as $v0){//万位
                        foreach($nums[1] as $v1){//千位
                            foreach($nums[2] as $v2){//百位
                                foreach($nums[3] as $v3){//十位
                                    foreach($nums[4] as $v4) $C[] = [$v0,$v1,$v2,$v3,$v4];//个位
                                }
                            }
                        }
                    }
                    break;
                default:return [0,[]];break;
            }
            
        }

        return [$CN,$C];
    }

    //排列
    public function star_pailie($num5 = '',$num=0)
    {
        if (strlen($num5)<=0 || $num <= 0)  return [0,[]];

        $num5       = explode(',',$num5);
        //去重
        $num5       = array_flip(array_flip($num5));
        $num5       = implode(',',$num5);

        $C          = $this->arrangement($num5,$num);
        $CN         = count($C);
        return [$CN,$C];
    }

    //组选 组合方式
    public function star_zuxuan($num5 = '',$num=0)
    {
        if (strlen($num5)<=0 || $num <= 0)  return [0,[]];

        $num5       = explode(',',$num5);
        //去重
        $num5       = array_flip(array_flip($num5));
        $num5       = implode(',',$num5);

        $C          = $this->combination($num5,$num);
        $CN         = count($C);
        return [$CN,$C];
    }

    //五星-组选60 组合方式
    public function star_zuxuan2($num1='',$num2='',$num=0)
    {   
        if (empty($num1) || empty($num2) || $num <= 1) return [0,[]];

        $num3       = explode(',',$num1);
        $num3       = array_flip(array_flip($num3));
        $num4       = explode(',',$num2);
        $num4       = array_flip(array_flip($num4));

        if (count($num3) < 1 || count($num4) < $num) return [0,[]];

        $num5       = $this->combination($num4,$num);

        $C          = [];
        foreach ($num3 as $v3) {

            foreach ($num5 as $k5=>$v5) {

                 if (in_array(intval($v3), $v5)) continue;

                 $C[]    = array_merge([$v3,$v3],$v5);
            }
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    //五星-组选30 组合方式
    public function fiveStar_x30($num1='',$num2='')
    {   
        if (empty($num1) || empty($num2)) return [0,[]];

        $num3       = explode(',',$num1);
        $num3       = array_flip(array_flip($num3));
        $num4       = explode(',',$num2);
        $num4       = array_flip(array_flip($num4));

        if (count($num3) < 2 || count($num4) < 1) return [];

        $num5       = $this->combination($num3,2);

        $C          = [];
        foreach ($num4 as $v4) {

            foreach ($num5 as $k5=>$v5) {

                 if (in_array(intval($v4), $v5)) continue;

                 $temp = [];
                 foreach ($v5 as $kk5 => $vv5) {
                     $temp[] = $vv5;
                     $temp[] = $vv5;
                 }

                 $temp[] = $v4;
                 $C[]    = $temp;
            }
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    //五星-组选20 组合方式
    public function star_zuxuan3($num1='',$num2='',$num=0)
    {   
        if (empty($num1) || empty($num2) || $num <= 0) return [0,[]];

        $num3       = explode(',',$num1);
        $num3       = array_flip(array_flip($num3));
        $num4       = explode(',',$num2);
        $num4       = array_flip(array_flip($num4));

        if (count($num3) < 1 || count($num4) < 2) return [0,[]];

        $num5       = $this->combination($num4,$num);

        $C          = [];
        foreach ($num3 as $v3) {

            foreach ($num5 as $k5=>$v5) {

                 if (in_array(intval($v3), $v5)) continue;

                 $C[]    = array_merge([$v3,$v3,$v3],$v5);
            }
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    //五星-组选10 组合方式
    public function fiveStar_x10($num1='',$num2='')
    {   
        if (empty($num1) || empty($num2)) return [0,[]];

        $num3       = explode(',',$num1);
        $num3       = array_flip(array_flip($num3));
        $num4       = explode(',',$num2);
        $num4       = array_flip(array_flip($num4));

        if (count($num3) < 1 || count($num4) < 1) return [0,[]];

        $C          = [];
        foreach ($num3 as $v3) {

            foreach ($num4 as $v4) {

                 if ($v3 == $v4) continue;

                 $C[]    = [$v3,$v3,$v3,$v4,$v4];
            }
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    //五星-组选5 组合方式
    public function fiveStar_x5($num1='',$num2='')
    {   
        if (empty($num1) || empty($num2)) return [0,[]];

        $num3       = explode(',',$num1);
        $num3       = array_flip(array_flip($num3));
        $num4       = explode(',',$num2);
        $num4       = array_flip(array_flip($num4));

        if (count($num3) < 1 || count($num4) < 1) return [0,[]];

        $C          = [];
        foreach ($num3 as $v3) {

            foreach ($num4 as $v4) {

                 if ($v3 == $v4) continue;

                 $C[]    = [$v3,$v3,$v3,$v3,$v4];
            }
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    //四星-直选-复试
    public function fourStar_zhix_fs($num5,$num4,$num3,$num2,$num1)
    {
        $nums           = $this->formatNumber($num5,$num4,$num3,$num2,$num1);
        $CN             = count($nums[1])*count($nums[2])*count($nums[3])*count($nums[4]);
        $C              = [];

        if($CN > 0){
            foreach($nums[1] as $v1){//千位
                foreach($nums[2] as $v2){//百位
                    foreach($nums[3] as $v3){//十位
                        foreach($nums[4] as $v4) $C[] = [$v1,$v2,$v3,$v4];//个位
                    }
                }
            }
        }

        return [$CN,$C];
    }

    //二星-直选 复试
    public function twoStar_zhix_fs($num1='',$num2='')
    {
        if (empty($num1) || empty($num2)) return [0,[]];

        $num3       = explode(',',$num1);
        $num3       = array_flip(array_flip($num3));
        $num4       = explode(',',$num2);
        $num4       = array_flip(array_flip($num4));

        if (count($num3) < 1 || count($num4) < 3) return [0,[]];

        $C          = [];
        foreach ($num3 as $v3) {

            foreach ($num4 as $v4){

                $C[] = [$v3,$v4];
            }
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    //二星-直选 和值
    public function zhix_hz($num1='',$num=0)
    {
        if ($num1 == '' || $num <= 0) return [0,[]];

        $C          = explode(',',$num1);
        $C          = array_flip(array_flip($C));

        $CN         = 0;
        $hz[2]      = [1,2,3,4,5,6,7,8,9,10,9,8,7,6,5,4,3,2,1];
        $hz[3]      = [1,3,6,10,15,21,28,36,45,55,63,69,73,75,75,73,69,63,55,45,36,28,21,15,10,6,3,1];

        $hz         = $hz[$num];
        foreach ($C as $v2) {
            if (isset($hz[$v2])) $CN += $hz[$v2];
        }

        return [$CN,$C];
    }

    //二星-直选 跨度
    public function zhix_kd($num1='',$num=0)
    {
        if (strlen($num1) <= 0 || $num <= 0) return [0,[]];

        $C      = explode(',',$num1);
        $C      = array_flip(array_flip($C));

        $CN     = 0;
        $kd[2]  = [10,18,16,14,12,10,8,6,4,2];
        $kd[3]  = [10,54,96,126,144,150,144,126,96,54];

        $kd     = $kd[$num];
        foreach ($C as $v2) {
            if (isset($kd[$v2])) $CN += $kd[$v2];
        }

        return [$CN,$C];
    }

    //二星-直选 和值尾数
    public function zhix_hzws($num1='')
    {
        if (strlen($num1) <= 0) return [0,[]];

        $C      = explode(',',$num1);
        $C      = array_flip(array_flip($C));

        $CN     = 0;
        $hz     = [1,1,1,1,1,1,1,1,1,1];
        foreach ($C as $v2) {
            if (isset($hz[$v2])) $CN += $hz[$v2];
        }

        return [$CN,$C];
    }

    //二星-组选 复试
    public function twoStar_zux_fs($num1='',$num2='')
    {
        if (strlen($num1) <= 0) return [0,[]];

        $num2       = explode(',',$num1);
        if (count($num2) < 2) return [0,[]];

        $C          = $this->combination($num2,2);
        $CN         = count($C);
        return [$CN,$C];
    }

    //定位胆
    public function locationGall($num5='',$num4='',$num3='',$num2='',$num1='')
    {
        $C      = $this->formatNumber($num5,$num4,$num3,$num2,$num1);
        $CN     = count($C[0])+count($C[1])+count($C[2])+count($C[3])+count($C[4]);
        return [$CN,$C];
    }

    public function notLocationGall($num5 = '',$num=0)
    {
        if (strlen($num5) <= 0 || $num <= 0) return [0,[]];

        $C       = $this->combination($num5,$num);
        $CN      = count($C);
        return [$CN,$C];
    }


    public function star_zuxuan_96($num5='',$num4='',$num3='',$num2='',$num1='',$star=0)
    {
        $CN     = 0;
        $C      = [];

        switch ($star) {
            case 2:
                if (empty($num5) || empty($num4)) return [0,[]];
                $num5   = explode(',',$num5);
                $num4   = explode(',',$num4);
                $num5   = array_flip(array_flip($num5));
                $num4   = array_flip(array_flip($num4));
                if (!empty($num5) && !empty($num4))
                {
                    foreach ($num5 as $k5 => $v5) {
                        foreach ($num4 as $k4 => $v4) {
                            if ($v5 == $v4) continue;

                            $C[]    = [$v5,$v4];
                        }
                    }
                }

            break;
            case 3:
                if (empty($num5) || empty($num4) || empty($num3)) return [0,[]];
                $num5   = explode(',',$num5);
                $num4   = explode(',',$num4);
                $num3   = explode(',',$num3);
                $num5   = array_flip(array_flip($num5));
                $num4   = array_flip(array_flip($num4));
                $num3   = array_flip(array_flip($num3));
                if (!empty($num5) && !empty($num4) && !empty($num3))
                {
                    foreach ($num5 as $k5 => $v5) {
                        foreach ($num4 as $k4 => $v4) {
                            foreach ($num3 as $k3 => $v3) {
                                if ($v5 == $v4 || $v5 == $v3 || $v4 == $v3) continue;

                                $C[]    = [$v5,$v4,$v3];
                            }
                        }
                    }
                }

            break;
            
            default:return [0,[]];break;
        }

        $CN      = count($C);
        return [$CN,$C];
    }

    public function single_form_96($num5='',$n=0,$type=0)
    {
        if (strlen($num5) <= 0 || $n <= 0)  return [0,[]];
        
        //有几个分割就是几注
        $C          = explode(',',$num5);

        foreach ($C as $key => $value) {
            $value      = preg_replace('/[ ]/', '', $value);
            if (strlen($value) !== $n*2)  unset($C[$key]);

            if ($n == 2 ) {
                $n1 = intval(substr($value,0,2));
                $n2 = intval(substr($value,2,4));
                if ($n1 <= 0 || $n1 > 10 || $n2 <= 0 || $n2 > 10 ) unset($C[$key]);
            }

            $temp = [];
            for($i=0;$i<$n;$i++){
                $n1 = intval(substr($value,$i*2,2));
                if ($n1 <= 0 || $n1 > 10 || in_array($n1,$temp)){
                    unset($C[$key]);break;
                }else{
                    $temp[] = $n1;
                }
            }
        }

        //去重
        $C          = array_flip(array_flip($C));

        $CN         = count($C);
        return [$CN,$C];
    }

    public function single_form_k3($num5='',$n=0)
    {
        if (strlen($num5) <= 0 || $n <= 0)  return [0,[]];
        
        //有几个分割就是几注
        $C          = explode(',',$num5);

        //去重
        $C          = array_flip(array_flip($C));

        $n2         = [11,22,33,44,55,66];
        $temp       = [];

        if (!empty($C))
        {
            foreach ($C as $key => $value)
            {
                $n1     = intval($value);
                if (in_array($n1,$n2) || $n1 > 66 || $n1 <= 11) unset($C[$key]);

                if ($n == 2)
                {
                    if (in_array($n1,$temp)) unset($C[$key]);
                    $temp[]  = $n1;
                    $temp[]  = strrev($n1);
                }
            }
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    public function tuandan_k3($num5='',$num4='')
    {
        if (empty($num5) || empty($num4))  return [0,[]];

        $num5          = explode(',',$num5);
        $num4          = explode(',',$num4);

        if (count($num4) <= 0 || count($num5) <= 0 || count($num5) >1) return [0,[]];

        $CN     = 0;
        $C      = [];

        foreach ($num4 as $key => $value)
        {
            if ($value == $num5[0]) continue;

            $C[]    = [$num5[0],$value];
        }

        $CN         = count($C);
        return [$CN,$C];
    }

    public function ertonghao_k3($num5='',$num4='',$type=0)
    {
        if (empty($num5) || $type <= 0)  return [0,[]];

        $nn     = [11,22,33,44,55,66];
        $nn3    = [111,222,333,444,555,666];

        if ($type == 1) {
            $num5          = explode(',',$num5);
            $num4          = explode(',',$num4);

            if (!in_array($num5[0],$nn)) return [0,[]];
 
            foreach ($num4 as $nv) {
                if (!in_array($nv,[1,2,3,4,5,6]) || $nv == ($num5[0]/11)) return [0,[]];
            }

            if (count($num5) !== 1) return [0,[]];
            if (count($num4) <= 0 || count($num4) >= 6 ) return [0,[]];


            if (count($num4) <= 0||count($num5)<= 0||count($num5) >1||!in_array($num5[0],$nn)) return [0,[]];

            $CN     = 0;
            $C      = [];

            foreach ($num4 as $key => $value)
            {
                if (intval($value.$value) == $num5[0]) continue;

                $C[]    = [$num5[0],$value];
            }

            $CN         = count($C);wr([$CN,$C]);
            return [$CN,$C];
        }

        if ($type == 2) {

            $C      = explode(',',$num5);
            $CN     = 0;

            foreach ($C as $key => $value)
            {   
                if (strlen($value) !== 3 || in_array($value,$nn3)){
                    unset($C[$key]); continue;
                }

                $n1     = intval(substr($value,0,2));
                $n2     = intval(substr($value,1,2));
                $n3     = intval(substr($value,0,1)).intval(substr($value,-1));

                if (!in_array($n1,$nn) && !in_array($n2,$nn) && !in_array($n3,$nn)){
                    unset($C[$key]); continue;
                }
            }

            $CN         = count($C);
            return [$CN,$C];
        }

        if ($type == 3) {

            $C      = explode(',',$num5);
            $CN     = 0;

            foreach ($C as $key => $value)
            {   
                if (strlen($value) !== 3 || in_array($value,$nn3)){
                    unset($C[$key]); continue;
                }

                $n1     = intval(substr($value,0,2));
                $n2     = intval(substr($value,1,2));
                $n3     = intval(substr($value,0,1)).intval(substr($value,-1));

                if (in_array($n1,$nn) || in_array($n2,$nn) || in_array($n3,$nn)){
                    unset($C[$key]); continue;
                }
            }

            $CN         = count($C);
            return [$CN,$C];
        }
    }

    public function tx_k3($type=0)
    {   
        $C[0]          = [111,222,333,444,555,666];
        $C[1]          = [123,234,345,456];
        $CN            = 1;
        return [$CN,$C[$type]];
    }

    public function single_form_108($num5='',$star=0)
    {
        if (empty($num5) || $star <= 0)  return [0,[]];

        $C              = explode(',',$num5);
        //去重
        $C              = array_flip(array_flip($C));

        $temp           = [];

        if (!empty($C)) {
            foreach ($C as $kc => $vc) {
                if (strlen($vc) !== $star*2 || in_array(intval($vc),$temp)) {
                    unset($C[$kc]); continue;
                }

                $n1     = intval($vc);
                $n2     = strrev(intval($vc));
                $temp[] = $n1;
                $temp[] = $n2;
            }
        }

        $CN             = count($C);
        return [$CN,$C];
    }

    public function pc_dd_b3($num5='')
    {
        if (empty($num5))  return [0,[]];
        $C              = explode(',',$num5);
        $CN             = count($C);
        if ($CN !== 3) return [0,[]];

        return [1,$num5];
    }

    public function zmgg_hc6($num5='')
    {
        if (empty($num5))  return [0,[]];
        $C        = explode(',',$num5);
        $CN       = 0;
        $op       = ['单','双','大','小','合单','合双','合大','合小','尾大','尾','红波','绿波','蓝波'];
        foreach ($C as $key => $value) {
            if ( !empty($value) && in_array($value,$op)) $CN ++;
        }

        if ($CN < 2) return [0,[]];
        return [1,$num5];
    }

    public function hk6_hx($num5='')
    {
        if (empty($num5))  return [0,[]];
        $C              = explode(',',$num5);
        $CN             = count($C);
        if ($CN < 2 || $CN > 11) return [0,[]];

        return [1,$num5];
    }
}