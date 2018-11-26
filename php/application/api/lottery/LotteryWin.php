<?php
/**
 * 彩票数字排列组合
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\lottery;

class LotteryWin extends Base
{   
    //时时彩 顺序一致
    public function win_ssc_sxyz($opencode='',$select_code=[],$star=0,$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = str_replace(',','',$opencode);
        switch ($pos) {
            case 0: $opencode           = substr($opencode,(0-$star));break;
            case 1: $opencode           = substr($opencode,0,$star);break;
            case 2: $opencode           = substr($opencode,1,$star);break;
                break;
            default: return [0,[]];break;
        }

        $wincode            = [];
        $win                = 0;
        foreach ($select_code as $key => $value) {

            $code           = !is_array($value) ? $value : implode(',',$value);
            $code           = str_replace(',','',$code);
            $code           = substr($code,(0-$star));
            if ($opencode == $code){
                $win++;
                $wincode[$code] = $code;
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //时时彩 不限顺序
    public function win_ssc_zuhe_bxsx($opencode='',$select_code=[],$star=0,$pos=0,$type=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];

        $opencode1          = $opencode;//1,2,3,4,5
        $subnum0            = [5=>-9,4=>-7,3=>-5,2=>-3];
        $subnum1            = [5=>9,4=>7,3=>5,2=>3];
        switch ($pos) {
            case 0: $opencode           = substr($opencode,$subnum0[$star]);break;
            case 1: $opencode           = substr($opencode,0,$subnum1[$star]);break;
            case 2: $opencode           = substr($opencode,2,$subnum1[$star]);break;
            default: return [0,[]];break;
        }

        $opencode           = explode(',',$opencode);
        sort($opencode);
        $opencode           = implode(',',$opencode);
        
        $wincode            = [];
        $win                = 0;
        foreach ($select_code as $key => $value)
        {
            if (!is_array($value))
            {
                $temp           = [];
                for ($i=0; $i < strlen($value); $i++) { 
                    $temp[] = substr($value,$i,1);
                }

                $oldVal     = $value;
                $value      = $temp;
            }
            else{
                $oldVal         = implode(',',$value);
            }

            //四星-后四组选-组选6
            if ($type == 1) {
                $value[2] = $value[0];
                $value[3] = $value[1];
            }
            
            sort($value);
            $value          = implode(',',$value);

            if ($value === $opencode)
            {
                $win++;
                $wincode[$oldVal] = $oldVal;
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //和值
    public function win_ssc_zxhz($opencode='',$select_code=[],$star=0,$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];

        $opencode1          = $opencode;
        $opencode           = str_replace(',','',$opencode);
        switch ($pos) {
            case 0: $opencode           = substr($opencode,(0-$star));break;
            case 1: $opencode           = substr($opencode,0,$star);break;
            case 2: $opencode           = substr($opencode,1,$star);break;
                break;
            default: return [0,[]];break;
        }

        $hz[2]     = [1,2,3,4,5,6,7,8,9,10,9,8,7,6,5,4,3,2,1];
        $hz[3]     = [1,3,6,10,15,21,28,36,45,55,63,69,73,75,75,73,69,63,55,45,36,28,21,15,10,6,3,1];

        $num                = 0;
        for ($i=0; $i < $star; $i++) { 
            $num += substr($opencode,$i,1);
        }

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            if ($value === $num) {
                //$win += $hz[$star][$value];
                $win ++;
                $wincode[$value] = $value;
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //跨度
    public function win_ssc_zxkd($opencode='',$select_code=[],$star=0,$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];
        
        $opencode1          = $opencode;
        $opencode           = str_replace(',','',$opencode);
        switch ($pos) {
            case 0: $opencode           = substr($opencode,(0-$star));break;
            case 1: $opencode           = substr($opencode,0,$star);break;
            case 2: $opencode           = substr($opencode,1,$star);break;
                break;
            default: return [0,[]];break;
        }

        $temp                = [];
        for ($i=0; $i < $star; $i++) { 
            $temp[] = substr($opencode,$i,1);
        }
        sort($temp);

        $num                = $temp[count($temp)-1] - $temp[0];
        $wincode            = [];
        $win                = 0;

        $kd[2]  = [10,18,16,14,12,10,8,6,4,2];
        $kd[3]  = [10,54,96,126,144,150,144,126,96,54];

        foreach ($select_code as $key => $value)
        {
            if ($value === $num) {
                //$win += $kd[$star][$value];
                $win ++;
                $wincode[$value] = $value;
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //定胆
    public function win_location_gall($opencode='',$select_code=[])
    {
        if (empty($opencode) || empty($select_code)) return [0,[]];

        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            if (!empty($value))
            {   $opcode     = intval($opencode[$key]);
                foreach ($value as $vv)
                {
                    if (intval($vv) === $opcode ) {
                        $win++;
                        $wincode[$key] = intval($vv);
                    }
                }
            }

        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //和值尾数
    public function win_ssc_hzws($opencode='',$select_code=[],$star=0,$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];
        
        $opencode1          = $opencode;
        $opencode           = str_replace(',','',$opencode);
        switch ($pos) {
            case 0: $opencode           = substr($opencode,(0-$star));break;
            case 1: $opencode           = substr($opencode,0,$star);break;
            case 2: $opencode           = substr($opencode,1,$star);break;
                break;
            default: return [0,[]];break;
        }

        $num                = 0;
        for ($i=0; $i < $star; $i++) { 
            $num += substr($opencode,$i,1);
        }

        //尾数
        $ws                 = intval(substr($num,-1));

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            if ($value === $ws) {
                $win++;
                $wincode[$value] = $value;
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //时时彩 组合 
    public function win_ssc_zuhe($opencode='',$select_code=[],$star=0,$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0 || $pos <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        switch ($pos) {
            case 1: $opencode  = $opencode;break;
            case 2: $opencode  = [$opencode[1],$opencode[2],$opencode[3],$opencode[4]];break;
            case 3: $opencode  = [$opencode[0],$opencode[1],$opencode[2],$opencode[3]];break;
            case 4: $opencode  = [$opencode[2],$opencode[3],$opencode[4]];break;
            case 5: $opencode  = [$opencode[1],$opencode[2],$opencode[3]];break;
            case 6: $opencode  = [$opencode[0],$opencode[1],$opencode[2]];break;
            default: return [0,[]];break;
        }

        $wincode            = [];
        $win                = 0;
        $star               = $star-1;
        foreach ($select_code as $key => $value) {
            for ($i=$star; $i >= 0; $i--) {
                if (isset($value[$i]) && isset($opencode[$i])) {
                    if ($value[$i] !== $opencode[$i]) break;
                    $code   = implode(',',$value);
                    $win++;
                    $wincode[$code] = ($star-$i+1);//中几星
                }else{
                    break;
                }
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //时时彩 不定位
    public function win_notlocation_gall($opencode='',$select_code=[],$star=0,$pos=0,$num=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0 || $num<= 0) return [0,[]];
        $opencode1          = $opencode;

        $subnum0            = [5=>-9,4=>-7,3=>-5,2=>-3];
        $subnum1            = [5=>9,4=>7,3=>5,2=>3];

        switch ($pos) {
            case 0: $opencode           = substr($opencode,$subnum0[$star]);break;
            case 1: $opencode           = substr($opencode,0,$subnum1[$star]);break;
            case 2: $opencode           = substr($opencode,2,5);break;
                break;
            default: return [0,[]];break;
        }

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            $counts          = count($value);
            if ($counts >0)
            { 
                $temp        = [];
                foreach ($value as $kk => $vv)
                {
                    if (substr_count('#'.$opencode, $vv) >= 1) {
                        $temp[]     = $vv;
                    }
                }

                if (count($temp) == $num)
                {
                    $value           = implode(',',$value);
                    $win++;
                    $wincode[$value] = $value;
                }
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //任选 顺序一致
    public function win_rx_sxyz($opencode='',$select_code=[],$star=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        $num[5]             = $opencode[0];
        $num[4]             = $opencode[1];
        $num[3]             = $opencode[2];
        $num[2]             = $opencode[3];
        $num[1]             = $opencode[4];

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            $nc             = explode('-',$key);
            if (count($nc) !== $star || empty($value))  continue;//星数不一致或者选号为空

            foreach ($value as $kk => $vv)
            {
                if (!is_array($vv))
                {
                    //不是数组说明是单式
                    $nn       = [];
                    for ($i=0; $i < strlen($vv); $i++) { 
                        $nn[] = substr($vv,$i,1);
                    }
                    $vv         = $nn;
                }

                $temp        = [];
                for ($i=0; $i <count($nc) ; $i++)
                { 
                    if ($num[$nc[$i]] == $vv[$i])
                    {
                        $temp[]         = $vv[$i];
                    }
                }

                if (count($temp) === count($nc))
                {
                    $win++;
                    $wincode[] = implode(',',$temp);
                }
            }

        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }





    //任选 不限顺序
    public function wx_rx_bxsx($opencode='',$select_code=[],$star=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        $num[5]             = $opencode[0];
        $num[4]             = $opencode[1];
        $num[3]             = $opencode[2];
        $num[2]             = $opencode[3];
        $num[1]             = $opencode[4];

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            $nc             = explode('-',$key);
            if (count($nc) !== $star || empty($value))  continue;//星数不一致或者选号为空

            $temp        = [];
            for ($i=0; $i <count($nc) ; $i++)
            { 
                $temp[]         = $num[$nc[$i]];
            }
            sort($temp);

            $temp = implode(',',$temp);

            foreach ($value as $kk => $vv)
            {   
                if (!is_array($vv))
                {
                    $tt           = [];
                    for ($i=0; $i < strlen($vv); $i++) { 
                        $tt[] = substr($vv,$i,1);
                    }

                    $oldVal     = $vv;
                    $vv         = $tt;
                }
                else{
                    $oldVal         = implode(',',$vv);
                }

                sort($vv);
                $vv             = implode(',',$vv);
                if ($vv === $temp)
                {
                    $win++;
                    $wincode[] = $oldVal;
                }
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //任选 组三
    public function win_rx_zu3($opencode='',$select_code=[],$star=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        $num[5]             = $opencode[0];
        $num[4]             = $opencode[1];
        $num[3]             = $opencode[2];
        $num[2]             = $opencode[3];
        $num[1]             = $opencode[4];

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            $nc             = explode('-',$key);//确定是几位数
            if (count($nc) !== $star || empty($value))  continue;//星数不一致或者选号为空

            //获取对应位数的数字
            $temp        = [];
            for ($i=0; $i <count($nc) ; $i++)
            { 
                $temp[]         = $num[$nc[$i]];
            }
            sort($temp);

            //判断得出的三位数是否是ABB型 不是直接跳出
            $is_effective = 0;  //是否符合开奖条件[0:不符合  1:符合]
            if($temp[0] == $temp[1] && $temp[0] != $temp[2]) $is_effective++;
            if($temp[0] == $temp[2] && $temp[0] != $temp[1]) $is_effective++;
            if($temp[1] == $temp[2] && $temp[0] != $temp[1]) $is_effective++;
            if ($is_effective <= 0) continue;

            $temp = implode(',',$temp);

            //去重
            $tempvalue      = [];
            foreach ($value as $tkk => $tvv){
                sort($tvv);
                $tempvalue[implode('',$tvv)]    = $tvv;  
            }

            sort($tempvalue);

            foreach ($tempvalue as $kk => $vv)
            {   
                //取出一个号作为重复号
                $vv1                = array_merge($vv,[$vv[0]]);
                $vv2                = array_merge($vv,[$vv[1]]);

                sort($vv1);
                sort($vv2);
                $oldVal         = implode(',',$vv);

                $vv1             = implode(',',$vv1);
                $vv2             = implode(',',$vv2);
                if ($vv1 === $temp || $vv2 === $temp)
                {
                    $win++;
                    $wincode[] = $oldVal;
                }
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //趣味
    public function win_quwei($opencode='',$select_code=[],$star=0)
    {
        if (empty($opencode) || empty($select_code) || $star <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        $wincode            = [];
        $win                = 0;

        // ===修改标记===
        //根据玩法,将开奖号码去重
        /*$opencode = array_unique($opencode);
        $opencode = array_values($opencode);*/

        $opencode           = implode('',$opencode);

        foreach ($select_code as $key => $value)
        {
            $nn       = substr_count($opencode,$value[0]);
            if ($nn >= $star) {
                $win++;
                $wincode[] = $value[0];
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //龙虎
    public function win_longhu($opencode='',$select_code=[],$type=0)
    {
        if (empty($opencode) || empty($select_code) || $type <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        $nn[1]              = [$opencode[0],$opencode[1]];//万千
        $nn[2]              = [$opencode[0],$opencode[2]];//万百
        $nn[3]              = [$opencode[0],$opencode[3]];//万十
        $nn[4]              = [$opencode[0],$opencode[4]];//万个
        $nn[5]              = [$opencode[1],$opencode[2]];//千百
        $nn[6]              = [$opencode[1],$opencode[3]];//千十
        $nn[7]              = [$opencode[1],$opencode[4]];//千个
        $nn[8]              = [$opencode[2],$opencode[3]];//百十
        $nn[9]              = [$opencode[2],$opencode[4]];//百个
        $nn[10]             = [$opencode[3],$opencode[4]];//十个

        
        $wincode            = [];
        $win                = 0;

        $lhhs               = ['龙'=>1,'虎'=>2,'和'=>3];

        foreach ($select_code as $key => $value)
        {
            $lhnum          = $lhhs[$value[0]];
            if ($this->lhh($nn[$type][0],$nn[$type][1]) == $lhnum) {
                $win++;
                $wincode[] = $value[0];
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    public function win_daxiaodanshuang($opencode='',$select_code=[],$type=0)
    {
        if (empty($opencode) || empty($select_code) || $type <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        $nn[1]              = $opencode[0]+$opencode[1]+$opencode[2]+$opencode[3]+$opencode[4];//总和
        $nn[2]              = $opencode[0];//万位
        $nn[3]              = $opencode[1];//千位
        $nn[4]              = $opencode[2];//百位
        $nn[5]              = $opencode[3];//十位
        $nn[6]              = $opencode[4];//个位

        $wincode            = [];
        $win                = 0;

        //串关 特殊处理
        if ($type == 7) {
            foreach ($select_code as $key => $value)
            {
                $select_code[$key]  = empty($value) ? 8 : $value;
            }

            $PermutationCombination = new \app\api\lottery\PermutationCombination();
            $permutation = $PermutationCombination->star_zhix_fs($select_code[0],$select_code[1],$select_code[2],$select_code[3],$select_code[4],5);

            $dxdsarr                   = ['大'=>1,'小'=>2,'单'=>3,'双'=>4];

            if (isset($permutation[1]) && !empty($permutation[1])) {
                foreach ($permutation[1] as $kk => $vv) {
                    $ok     = true;
                    $code   = [];
                    foreach ($vv as $kkk => $vvv) {

                        $code[$kkk]  = /*$vvv == 8 ? '' :*/ $vvv;
                        //if (intval($vvv) == 8) continue;

                        $dxdxNum    = $dxdsarr[$vvv];
                    
                        $dxds       = $this->dxds($nn,$kkk+2);
                        if (!in_array(intval($dxdxNum),$dxds))
                        {
                            $ok = false;
                            break;
                        }
                    }

                    if ($ok) {
                        $win++;
                        $wincode[] = implode(',',$code);
                    }
                }
            }
        }else{
            $dxdsarr   = ['大'=>1,'小'=>2,'单'=>3,'双'=>4];
            foreach ($select_code as $key => $value)
            {
                $dxds       = $this->dxds($nn,$type);
                if (in_array(intval($dxdsarr[$value[0]]),$dxds))
                {
                    $win++;
                    $wincode[] = $value[0];
                }
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    private function lhh($n1,$n2)
    {
        if (intval($n1) > intval($n2))  return 1;
        if (intval($n1) < intval($n2))  return 2;
        if (intval($n1) == intval($n2))  return 3;
    }

    private function dxds($n1=0,$type=0)
    {
        $dx   = 0;
        $ds   = 0;
        $nn1  = isset($n1[$type]) ? intval($n1[$type]) : 0;

        switch ($type) {
            case 1:
                if ($nn1 >= 23 && $nn1 <= 45) $dx = 1;
                if ($nn1 >= 0 && $nn1 <= 22) $dx = 2;
                break;
            default: 
                if ($nn1 >= 5 && $nn1 <= 9) $dx = 1;
                if ($nn1 >= 0 && $nn1 <= 4) $dx = 2;
                break;
        }

        $ds         = $nn1%2 == 0 ? 4 : 3;

        return [$dx,$ds];
    }

    public function win_teshuhao($opencode='',$select_code=[],$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $pos <= 0) return [0,[]];
        $opencode1                 = $opencode;
        $opencode                  = explode(',',$opencode);
        
        switch ($pos) {
            case 1: $tsh           = [$opencode[0],$opencode[1],$opencode[2]];break;
            case 2: $tsh           = [$opencode[1],$opencode[2],$opencode[3]];break;
            case 3: $tsh           = [$opencode[2],$opencode[3],$opencode[4]];break;
            default: return [0,[]];break;
        }

        $wincode            = [];
        $win                = 0;
        $tt                 = $this->tsh($tsh);
        $tshs               = ['豹子'=>1,'顺子'=>2,'对子'=>3,'半顺'=>4,'杂六'=>5];

        foreach ($select_code as $key => $value)
        {
            if ($tt == intval($tshs[$value[0]])) {
                $win++;
                $wincode[] = $value[0];
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    private function tsh($tsh=[])
    {
        $bz = [000,111,222,333,444,555,666,777,888,999];
        $sz = [012,123,234,345,456,567,678,789,190];
        $dz = [00,11,22,33,44,55,66,77,88,99];
        $bs = [01,12,23,34,45,56,67,78,89];

        sort($tsh);
        
        $tsh    = implode("",$tsh);

        if (in_array($tsh,$bz)) return 1;
        if (in_array($tsh,$sz)) return 2;

        $t1     = substr($tsh,0,2);
        $t2     = substr($tsh,1,2);

        if (in_array($t1,$dz) || in_array($t2,$dz)) return 3;
        if (in_array($t1,$bs) || in_array($t2,$bs)) return 4;

        return 5;
    }

    public function win_douniu($opencode='',$select_code=[])
    {
        if (empty($opencode) || empty($select_code)) return [0,[]];
        $opencode1                 = $opencode;
        $opencode                  = explode(',',$opencode);

        $num[0]             = intval($opencode[0]) === 0 ? 10 : intval($opencode[0]);
        $num[1]             = intval($opencode[1]) === 0 ? 10 : intval($opencode[1]);
        $num[2]             = intval($opencode[2]) === 0 ? 10 : intval($opencode[2]);
        $num[3]             = intval($opencode[3]) === 0 ? 10 : intval($opencode[3]);
        $num[4]             = intval($opencode[4]) === 0 ? 10 : intval($opencode[4]);

        $wincode            = [];
        $win                = 0;

        $NiuNiuGameHelper   = new \xnrcms\NiuNiuGameHelper();
        $nn                 = $NiuNiuGameHelper->JudgeCowCow($num);
        $nns                = ['牛牛'=>0,'牛九'=>9,'牛八'=>8,'牛七'=>7,'牛六'=>6,'牛五'=>5,'牛四'=>4,'牛三'=>3,'牛二'=>2,'牛一'=>1,'无牛'=>-1];

        foreach ($select_code as $key => $value)
        {
           if ($nn == intval($nns[$value[0]])) {
                $win++;
                $wincode[] = $value[0];
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //三星 组三
    public function win_star3_zu3($opencode='',$select_code=[],$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $pos <= 0) return [0,[]];
        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);

        switch ($pos) {
            case 1: $opencode      = [$opencode[2],$opencode[3],$opencode[4]];break;
            case 2: $opencode      = [$opencode[1],$opencode[2],$opencode[3]];break;
            case 3: $opencode      = [$opencode[0],$opencode[1],$opencode[2]];break;
            default: return [0,[]];break;
        }

        sort($opencode);
        $opencode           = implode(',',$opencode);

        $wincode            = [];
        $win                = 0;

        foreach ($select_code as $key => $value)
        {
            //取出一个号作为重复号
            $vv1                = array_merge($value,[$value[0]]);
            $vv2                = array_merge($value,[$value[1]]);

            sort($vv1);
            sort($vv2);
            $oldVal             = implode(',',$value);

            $vv1             = implode(',',$vv1);
            $vv2             = implode(',',$vv2);
            if ($vv1 === $opencode || $vv2 === $opencode)
            {
                $win++;
                $wincode[] = $oldVal;
            }
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //快三
    public function win_k3($opencode='',$select_code=[],$type=0)
    {
        if (empty($opencode) || empty($select_code) || $type <= 0) return [0,[]];

        $opencode1          = $opencode;//1,2,3
        $opencode           = explode(',',$opencode);
        $wincode            = [];
        $win                = 0;

        switch ($type) {
            case 1:
                foreach ($select_code as $key => $value)
                {   
                    $n1     = intval($value[0]);
                    $n2     = intval($value[1]);
                    $oldVal = implode(',',$value);
                    if (in_array($n1,$opencode) && in_array($n2,$opencode))
                    {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }
                break;
            case 2:
                foreach ($select_code as $key => $value)
                {   
                    $n1     = substr($value,0,1);
                    $n2     = substr($value,1,1);
                    $oldVal = implode(',',[$n1,$n2]);
                    if (in_array($n1,$opencode) && in_array($n2,$opencode))
                    {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }
                break;
            case 3: return [0,[]];break;

            case 4:
                // ===修改标记===
                sort($opencode);

                foreach ($select_code as $key => $value)
                {   
                    $n1     = substr($value[0],0,1);
                    $n2     = substr($value[0],1,1);
                    $n3     = intval($value[1]);

                    $n4     = [$n1,$n2,$n3];
                    sort($n4);

                    $oldVal = implode(',',[$n1,$n2,$n3]);
                    if (!empty($n4) && $n4 == $opencode)
                    {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }

                break;
            case 5:
                foreach ($select_code as $key => $value)
                {   
                    $n1     = substr($value,0,1);
                    $n2     = substr($value,1,1);
                    $n3     = substr($value,2,1);

                    $oldVal = implode(',',[$n1,$n2,$n3]);
                    if (in_array($n1,$opencode)&&in_array($n2,$opencode)&&in_array($n3,$opencode))
                    {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }
                break;
            case 6:
                foreach ($select_code as $key => $value)
                {   
                    $n1     = substr($value[0],0,1);

                    $oldVal = implode(',',[$n1,$n1,'*']);
                    if (substr_count("#".$opencode1,$n1) == 2)
                    {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }
                break;
            case 7:
                foreach ($select_code as $key => $value)
                {   
                    $n1     = $value[0];
                    $n2     = $value[1];
                    $n3     = $value[2];;

                    $oldVal = implode(',',$value);
                    if (in_array($n1,$opencode)&&in_array($n2,$opencode)&&in_array($n3,$opencode))
                    {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }
                break;
            case 8: return [0,[]];break;
            case 9:
                $code       = [];
                $oldVal     = implode(',',$opencode);
                $opencode   = implode('',$opencode);
                foreach ($select_code as $key => $value)
                {   
                    $code[] = $value[0];
                }
                if (in_array($opencode,$code)) {
                    $win++;
                    $wincode[$oldVal] = $oldVal;
                }
                break;
            case 10:
                $oldVal     = implode(',',$select_code);
                if (in_array(intval(implode('',$opencode)),$select_code)) {
                    $win++;
                    $wincode[$oldVal] = $oldVal;
                }
                break;
            case 11: return [0,[]];break;
            default: return [0,[]];break;
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    public function win_11x5($opencode='',$select_code=[],$type=0,$star=0)
    {
        if (empty($opencode) || empty($select_code) || $type <= 0) return [0,[]];

        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);
        $wincode            = [];
        $win                = 0;

        switch ($type) {
            case 1:
                $num[1]     = $opencode[0];
                $num[2]     = $opencode[0].$opencode[1];
                $num[3]     = $opencode[0].$opencode[1].$opencode[2];
                foreach ($select_code as $key => $value)
                {   
                    if (!is_array($value)) {
                        $scode   = $value;
                    }else{
                        $scode   = implode('',$value);
                    }
                    if ($num[$star] === $scode) {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }

                break;
            case 2:
                $num[2]     = [$opencode[0],$opencode[1]];
                $num[3]     = [$opencode[0],$opencode[1],$opencode[2]];
                sort($num[$star]);

                $opcode     = implode('',$num[$star]);

                foreach ($select_code as $key => $value)
                {   
                    if (!is_array($value)) {
                        $temp    = [];
                        for ($i=0; $i < $star; $i++) { 
                            $temp[] = substr($value,$i*2,2);
                        }
                        $oldVal  = $value;
                        $value   = $temp;
                    }else{
                        $oldVal  = implode('',$value);
                    }

                    sort($value);
                    $scode   = implode('',$value);

                    if ($opcode === $scode) {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }
                break;
            case 3:
                $num[2]     = [$opencode[0],$opencode[1]];
                $num[3]     = [$opencode[0],$opencode[1],$opencode[2]];
                $opcode     = $num[$star];

                foreach ($select_code as $key => $value)
                {
                    $scode   = $value[0];
                    if ( in_array($scode,$opcode) ) {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 4:
                foreach ($select_code as $key => $value)
                {
                    if (!empty($value) && in_array($opencode[$key],$value))
                    {
                        $win++;
                        $wincode[$key] = $opencode[$key];
                    }
                }
                break;
            case 5:
                foreach ($select_code as $key => $value)
                {   
                    if (!is_array($value)) {
                        $temp    = [];
                        for ($i=0; $i < $star; $i++) { 
                            $temp[] = substr($value,$i*2,2);
                        }
                        $oldVal  = $value;
                        $value   = $temp;
                    }

                    $nn     = 0;
                    foreach ($value as $kk => $vv)
                    {
                        if (in_array($vv,$opencode)) $nn++;
                    }

                    $oldVal     = implode(',',$value);
                    if ( ($star >= 6 && $nn >=5) || ($star <=5 && $nn === $star) )
                    {
                        $win++;
                        $wincode[$oldVal] = $oldVal;
                    }
                }
                # code...
                break;
            default:return [0,[]];break;
        }
        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    //北京快乐8
    public function win_bjkl8($opencode='',$select_code=[],$type=0,$star=0)
    {
        if (empty($opencode) || empty($select_code) || $type <= 0) return [0,[]];

        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);
        $wincode            = [];
        $win                = 0;

        switch ($type) {
            case 1:
                foreach ($select_code as $key => $value)
                {
                    $scode      = $value[0]*1;
                    if ($scode === $opencode[3]*1)
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 2:
                foreach ($select_code as $key => $value)
                {
                    $scode      = $value[0];
                    if ( in_array($scode,$this->tm($opencode[3]*1)))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 3:
                foreach ($select_code as $key => $value)
                {
                    $scode      = $value[0];
                    if ( in_array($scode,$this->bs($opencode[3]*1)))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 4:
                foreach ($select_code as $key => $value)
                {
                    $scode      = $value[0];
                    if ( $scode === '豹子' && $opencode[0]*1 === $opencode[1]*1 && $opencode[0]*1 === $opencode[2]*1)
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 5:
                $oldVal         = $select_code;
                $select_code    = explode(',',$select_code);
                if ( in_array($opencode[3],$select_code))
                {
                    $win++;
                    $wincode[$oldVal] = $oldVal;
                }
                break;
            default:return [0,[]];break;
        }
        return [$win,$wincode];
    }

    private function tm($num=0)
    {
        $numSt          = [];
        if ($num >= 22 && $num <= 27)                   $numSt[] =  '极大';
        if ($num >= 0 && $num <= 5)                     $numSt[] =  '极小';
        if ($num >= 14 && $num <= 27)                   $numSt[] =  '大';
        if ($num >= 0 && $num <= 13)                    $numSt[] =  '小';
        if ($num%2 != 0)                                $numSt[] =  '单';
        if ($num%2 == 0)                                $numSt[] =  '双';
        if ($num >= 14 && $num <= 27 && $num%2 == 0)    $numSt[] =  '大双';
        if ($num >= 14 && $num <= 27 && $num%2 != 0)    $numSt[] =  '大单';
        if ($num >= 0 && $num <= 13 && $num%2 == 0)     $numSt[] =  '小双';
        if ($num >= 0 && $num <= 13 && $num%2 != 0)     $numSt[] =  '小单';
        return $numSt;
    }

    private function bs($num=0)
    {
        if (in_array($num,[1,4,7,10,16,19,22,25])) return ['绿波'];
        if (in_array($num,[2,5,8,11,17,20,23,26])) return ['蓝波'];
        if (in_array($num,[3,6,9,12,15,18,21,24])) return ['红波'];
        if (in_array($num,[0,13,14,27])) return ['灰波'];

        return [''];
    }

    public function win_pk10($opencode='',$select_code=[],$type=0,$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $type <= 0) return [0,[]];

        $opencode1          = $opencode;
        $opencode           = explode(',',$opencode);
        $wincode            = [];
        $win                = 0;

        switch ($type) {
            case 1:
                $nn     = $opencode[0]*1 + $opencode[1]*1;
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0]*1;
                    if ( $scode === $nn)
                    {
                        $win++;
                        $wincode[$value[0]] = $value[0];
                    }
                }
                break;
            case 2:
                $posnum[0]      = [$opencode[0],$opencode[9]];
                $posnum[1]      = [$opencode[1],$opencode[8]];
                $posnum[2]      = [$opencode[2],$opencode[7]];
                $posnum[3]      = [$opencode[3],$opencode[6]];
                $posnum[4]      = [$opencode[4],$opencode[5]];
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    if ( $scode === $this->pk10_lh($posnum[$pos][0],$posnum[$pos][1]))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                        break;
                    }
                }
                break;
            case 3:
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    if ( in_array($opencode[$pos],$this->pk10_wx($scode)))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 4:
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    if ( $scode === $this->pk10_dx($opencode[$pos]))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 5:
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    if ( $scode === $this->pk10_ds($opencode[$pos]))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 6:
                $nn     = $opencode[0]*1+$opencode[1]*1;
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    $dx             = [$this->pk10_dx2($nn),$this->pk10_ds($nn)];
                    if (in_array($scode,$dx))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            default:
                # code...
                break;
        }

        //wr([$win,$wincode,$opencode1,$select_code]);
        return [$win,$wincode];
    }

    private function pk10_lh($n1=0,$n2=0)
    {
        return $n1 >= $n2 ? '龙' : '虎';
    }

    private function pk10_dx($n1=0)
    {
        return intval($n1) >= 6  ? '大' : '小';
    }

    private function pk10_dx2($n1=0)
    {
        return intval($n1) >= 12  ? '大' : '小';
    }
    private function pk10_ds($n1=0)
    {
        return intval($n1)%2 == 0  ? '双' : '单';
    }

    private function pk10_wx($str='')
    {
        $wx['金']    = ['01','02'];
        $wx['木']    = ['03','04'];
        $wx['水']    = ['05','06'];
        $wx['火']    = ['07','08'];
        $wx['土']    = ['09','10'];

        return isset($wx[$str]) ? $wx[$str] : [];
    }

    public function win_hk6($opencode='',$select_code=[],$type=0,$pos=0)
    {
        if (empty($opencode) || empty($select_code) || $type <= 0) return [0,[]];

        $opencode1          = $opencode;
        $opencode           = explode('+',$opencode);
        $zm                 = explode(',',$opencode[0]);
        $tm                 = $opencode[1];
        $wincode            = [];
        $win                = 0;

        switch ($type) {
            case 1:
                $lm         = $this->liangmian($zm,$tm);
                //首先判断是否和局
                if (in_array('和局',$lm)){
                    //如果出现和局 只有总单 总双 总大  总小不能和局 
                    foreach ($select_code as $key => $value)
                    {
                        $scode          = $value[0];
                        if (in_array($scode,['总单','总双','总大','总小'])) {
                            if (in_array($scode,$lm))
                            {
                                $win++;
                                $wincode[$scode] = $scode;
                            }
                        }else{
                            $win++;
                            $wincode[$scode] = $scode;
                        }
                        
                        $wincode['和局'] = '和局';
                    }
                }else{
                    //如果没有出现和局 按正常中奖规则计算
                    foreach ($select_code as $key => $value)
                    {
                        $scode          = $value[0];
                        if (in_array($scode,$lm))
                        {
                            $win++;
                            $wincode[$scode] = $scode;
                        }
                    }
                }
                break;
            case 2:
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    if ($scode == $tm)
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 3:
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    if ( in_array($scode,$zm))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 4:
                foreach ($select_code as $key => $value)
                {
                    $scode          = $value[0];
                    if ( $scode == $zm[$pos])
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 5:
                $zmgg         = $this->zmgg($zm[$pos]);

                //如果出现和局 除了红波、绿波、蓝波，其他玩法都是和局，开49，返还投注金额
                if (in_array('和局',$zmgg))
                {
                    $wincode['和局N']     = 0;
                    foreach ($select_code as $key => $value)
                    {
                        $scode          = $value[0];
                        if ( in_array($scode,$zmgg) && in_array($scode,['红波','绿波','蓝波']))
                        {
                            $win++;
                            $wincode[$scode] = $scode;
                        }else{
                            $wincode['和局']  = '和局';
                            $wincode['和局N'] += (in_array($scode,['红波','绿波','蓝波']) ? 0 : 1);
                        }
                    }

                }else{
                    foreach ($select_code as $key => $value)
                    {
                        $scode          = $value[0];
                        if ( in_array($scode,$zmgg))
                        {
                            $win++;
                            $wincode[$scode] = $scode;
                        }
                    }
                }
                break;
            case 6:
                $temp1           = 0;
                $temp2           = 0;
                $scode           = $select_code;
                $select_code     = explode(',',$select_code);

                foreach ($select_code as $key => $value)
                {
                    $zmgg           = $this->zmgg($zm[$key]);
                    if (!empty($value)) $temp1++;
                    if (!empty($value) && in_array($value,$zmgg)) $temp2++;
                }

                if ($temp1 == $temp2) {
                    $win++;
                    $wincode[$scode] = $scode;
                }
                break;
            case 7:
                foreach ($select_code as $key => $value)
                {   
                    $temp       = 0;
                    $n          = count($value);
                    $scode      = implode(',',$value);
                    foreach ($value as $kk => $vv) {
                        if (in_array($vv,$zm)) $temp ++;
                    }

                    if ($temp >= $pos)
                    {
                        $win++;
                        $wincode[$scode] = $scode .'|' . $n.'#' . $temp;
                    }
                }
                break;
            case 8:
                foreach ($select_code as $key => $value)
                {   
                    $temp1      = 0;
                    $temp2      = 0;
                    $n          = count($value);
                    $scode      = implode(',',$value);
                    foreach ($value as $kk => $vv) {
                        if (in_array($vv,$zm)) $temp1 ++;
                        if (in_array($vv,[$tm])) $temp2 ++;
                    }

                    //特串必须一个号码在正码中，一个必须在特码中
                    if ($pos == 1) {

                        $ok     = ($temp1 == 1 && $temp2 == 1) ? true : false;
                        if ($ok) {
                            $win++;
                            $wincode[$scode] = $scode;
                        }
                    }else{

                        //中一个特码 一个正码
                        if ($temp1 == 1 && $temp2 == 1) {
                            $win++;
                            $wincode[$scode] = $scode .'|' . $temp1.'#2';
                        }

                        //中二个正码
                        if ($temp1 == 2 && $temp2 == 0) {
                            $win++;
                            $wincode[$scode] = $scode .'|' . $temp1.'#3';
                        }
                    }
                }
                break;
            case 9:
                $opencode       = array_merge($zm,[$tm]);

                foreach ($select_code as $key => $value)
                {   
                    $temp       = 0;
                    $n          = count($value);
                    $scode      = implode(',',$value);
                    foreach ($value as $kk => $vv) {
                        $sx     = $this->shengxiao($vv);
                        foreach ($opencode as $k1 => $v1) {
                            if (in_array($v1,$sx)){
                                $temp ++;break;
                            }
                        }
                    }

                    if ($temp == $pos)
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 10:
                $opencode       = array_merge($zm,[$tm]);
                $wh             = [];
                foreach ($opencode as $ok => $ov) {
                    $wh[]       = "尾".substr($ov,1,1);
                }

                foreach ($select_code as $key => $value)
                {   
                    $temp       = 0;
                    $scode      = implode(',',$value);
                    foreach ($value as $kk => $vv) {
                        if (in_array($vv,$wh)) $temp ++;
                    }

                    if ($temp == $pos)
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 11:
                $opencode       = array_merge($zm,[$tm]);

                foreach ($select_code as $key => $value)
                {   
                    $temp       = 0;
                    $scode      = implode(',',$value);
                    foreach ($value as $kk => $vv) {
                        if (in_array($vv,$opencode)) break;
                        $temp ++;
                    }

                    if ($temp == $pos)
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 12:
                if ($pos == 1) {
                    $opcode     = $zm;
                }elseif ($pos == 2) {
                    $opcode     = [$tm];
                }elseif ($pos == 3) {
                    $opcode     = array_merge($zm,[$tm]);
                }

                foreach ($select_code as $key => $value)
                {   
                    $temp       = 0;
                    $scode      = $value[0];
                    $sx         = $this->shengxiao($value[0]);
                    foreach ($opcode as $kk => $vv) {
                        if (in_array($vv,$sx)){
                            $temp ++;
                            //如果不是生肖-正肖 跳出
                            if ($pos != 1) break;
                        }
                    }

                    if ($temp >=1)
                    {
                        $win++;
                        $wincode[$scode] = $pos != 1 ? $scode : $scode.'#'.$temp;
                    }
                }
                break;
            case 13:
                $opcode     = array_merge($zm,[$tm]);
                $sx1        = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];
                $sx2        = [];

                foreach ($sx1 as $sv) {
                    $sx     = $this->shengxiao($sv);
                    foreach ($opcode as $ov) {
                        if (in_array($ov,$sx)) $sx2[$sv] = $sv;
                    }
                }

                $sxnum      = count($sx2);
                $sxstr[]    = ($sxnum>=1 && $sxnum<=4) ? '234肖' : $sxnum.'肖';
                $sxstr[]    = $sxnum%2 == 0  ? '总肖双' : '总肖单';
                
                foreach ($select_code as $key => $value)
                {   
                    $scode      = $value[0];
                    if (in_array($scode,$sxstr))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            //14合肖预留
            case 14:
                $sx1        = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];
                $sx2        = '';

                foreach ($sx1 as $sv) {
                    $sx     = $this->shengxiao($sv);
                    if (in_array($tm,$sx)){
                        $sx2 = $sv;
                        //continue;
                    }
                }
   
                $select_code    = !empty($select_code) ? explode(',',$select_code) : [];
                if (in_array($sx2,$select_code)) {
                    $win++;
                    $wincode[$sx2] = $sx2.'#'.implode(',',$select_code);
                }
                break;
            case 15:
                if ($tm == 49) {
                    $win++;
                    $wincode['和局'] = '和局';
                }else{
                    $ds             = $tm%2 == 0  ? '双' : '单';
                    $dx             = (intval($tm) >= 25 && intval($tm) <= 48)  ? '大' : '小';
                    foreach ($select_code as $key => $value)
                    {
                        $scode      = $value[0];
                        $sb1        = msubstr($scode,0,1,'utf-8',false);
                        $sb         = $sb1."波";
                        $op         = [$sb,$sb1.$ds,$sb1.$dx,$sb1.$dx.$ds];

                        if (in_array($tm,$this->shebo($sb)) && in_array($scode,$op))
                        {
                            $win++;
                            $wincode[$scode] = $scode;
                        }
                    }
                }
                break;
            case 16:
                $opcode1         = $zm;
                $opcode2         = [$tm];
                $bs              = ['红波','蓝波','绿波'];
                $bss['红波']     = 0;
                $bss['蓝波']     = 0;
                $bss['绿波']     = 0;

                foreach ($bs as $bsv) {
                    foreach ($opcode1 as $ov) {
                        if (in_array($ov,$this->shebo($bsv))) $bss[$bsv] += 1;
                    }
                    foreach ($opcode2 as $ov) {
                        if (in_array($ov,$this->shebo($bsv))) $bss[$bsv] += 1.5;
                    }
                }

                $op             = '';
                $b1             = ($bss['红波']==$bss['蓝波'] && $bss['红波'] == 3);
                $b2             = ($bss['红波']==$bss['绿波'] && $bss['红波'] == 3);
                $b3             = ($bss['绿波']==$bss['蓝波'] && $bss['绿波'] == 3);
                if ($b1 ||  $b2 ||  $b3) {
                    $op     = '和局';
                }else{
                    foreach ($bss as $bk => $bv) $bss[$bk] = (string)$bv;
                    $bss2       = array_flip($bss);
                    sort($bss);
                    $op         = $bss2[$bss[2]];
                }

                //如果选号里面没有选择和局 则视为不按和局赔付，只退换本金
                $scode1         = [];
                foreach ($select_code as $key => $value)
                {
                    $scode1[]   = $value[0];
                }

                if ($op == '和局' && in_array($op, $scode1)) {
                    $t          = 0;
                    foreach ($select_code as $key => $value)
                    {
                        $scode      = $value[0];

                        if ($op == $scode)
                        {
                            $win++;
                            $wincode[$scode] = $scode;
                        }else{
                            $t ++;
                        }
                    }

                    $win++;
                    $wincode['和局1'] = '和局1#'.$t;
                }else if ($op == '和局' && !in_array($op, $scode1)) {
                    $win++;
                    $wincode['和局1'] = '和局1#'.count($select_code);
                }else{
                    foreach ($select_code as $key => $value)
                    {
                        $scode      = $value[0];

                        if ($op == $scode)
                        {
                            $win++;
                            $wincode[$scode] = $scode;
                        }
                    }
                }
                break;
            case 17:
                $op     = [];
                if ($pos == 1) {
                    $tm1        = substr($tm,0,1);
                    $tm2        = substr($tm,1,1);
                    $op[]       = '头'.$tm1;
                    $op[]       = '尾'.$tm2;
                }else{
                    $zt         =array_merge($zm,[$tm]);
                    foreach ($zt as $zv) $op[]       = '尾'.(substr($zv,1,1));
                }

                foreach ($select_code as $key => $value)
                {
                    $scode      = $value[0];
                    if (in_array($scode,$op))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 18:
                $opcode       = array_merge($zm,[$tm]);
                $dan = $suang = $da = $xiao = 0;
                foreach ($opcode as $ov){
                    intval($ov)%2 == 0  ? $suang++ : $dan ++;
                    (intval($ov) >= 25 && intval($ov) <= 49)  ? $da ++ : $xiao++;
                }

                $op         = [];
                $op[]       = "单" . $dan . "双" . $suang;
                $op[]       = "大" . $da . "小" . $xiao;
                foreach ($select_code as $key => $value)
                {
                    $scode      = $value[0];
                    if (in_array($scode,$op))
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            case 19:
                //$opcode       = array_merge($zm,[$tm]);
                $opcode         = [$tm];
                foreach ($select_code as $key => $value)
                {
                    $scode      = $value[0];
                    $wx         = $this->hkwx($scode);
                    foreach ($opcode as $ov) {
                        if (in_array($ov,$wx))
                        {
                            $win++;
                            $wincode[$scode] = $scode;
                            break;
                        }
                    }
                }
                break;
            case 20:
                $opcode       = array_merge($zm,[$tm]);
                foreach ($select_code as $key => $value)
                {
                    $scode      = implode(',',$value);
                    $temp       = 0;
                    foreach ($value as $ov) {
                        if (in_array($ov,$opcode)) $temp ++;
                    }

                    if ($temp == $pos)
                    {
                        $win++;
                        $wincode[$scode] = $scode;
                    }
                }
                break;
            default: return [0,[]];break;
        }

        return [$win,$wincode];
    }

    private function zmgg($zm)
    {
        $zms    = [];

        //特码
        $zm1    = substr($zm,0,1);
        $zm2    = substr($zm,1,1);
        $zms[]  = intval($zm)%2 == 0  ? '双' : '单';
        $zms[]  = (intval($zm) >= 25 && intval($zm) <= 48)  ? '大' : ($zm == 49 ? '和局' : '小');

        $zmh    = $zm1*1+$zm2*1;
        $zms[]   = $zmh>= 7 ? '合大' : '合小';
        $zms[]   = $zmh%2 == 0  ? '合双' : '合单';
        $zms[]   = intval($zm2)>= 5 && intval($zm2)<=9 ? '尾大' : '尾小';

        //色波
        if (in_array($zm,$this->shebo('红波'))) {
            $zms[]       = '红波';
        }
        if (in_array($zm,$this->shebo('蓝波'))) {
            $zms[]       = '蓝波';
        }
        if (in_array($zm,$this->shebo('绿波'))) {
            $zms[]       = '绿波';
        }

        return $zms;
    }

    private function liangmian($zm,$tm)
    {
        $lm     = [];

        //特码
        $tm1    = substr($tm,0,1);
        $tm2    = substr($tm,1,1);

        $lm[]   = intval($tm)%2 == 0  ? '特双' : '特单';
        $lm[]   = (intval($tm) >= 25 && intval($tm) <= 49)  ? '特大' : '特小';

        $tmh    = $tm1*1+$tm2*1;
        $lm[]   = $tmh>= 7 ? '特合大' : '特合小';
        $lm[]   = $tmh%2 == 0  ? '特合双' : '特合单';
        $lm[]   = intval($tm2)>= 5 && intval($tm2)<=9 ? '特尾大' : '特尾小';

        if ($tm == 49 ) $lm[]   = '和局';

        //正码
        $zmh        = $zm[0]+$zm[1]+$zm[2]+$zm[3]+$zm[4]+$zm[5];
        $lm[]       = $zmh>= 175 ? '总大' : '总小';
        $lm[]       = $zmh%2 == 0  ? '总双' : '总单';

        //生肖
        if (in_array($tm,$this->shengxiao('天肖'))) {
            $lm[]       = '特天肖';
        }
        if (in_array($tm,$this->shengxiao('地肖'))) {
            $lm[]       = '特地肖';
        }
        if (in_array($tm,$this->shengxiao('前肖'))) {
            $lm[]       = '特前肖';
        }
        if (in_array($tm,$this->shengxiao('后肖'))) {
            $lm[]       = '特后肖';
        }
        if (in_array($tm,$this->shengxiao('家禽'))) {
            $lm[]       = '特家肖';
        }
        if (in_array($tm,$this->shengxiao('野兽'))) {
            $lm[]       = '特野肖';
        }

        return $lm;
    }

    private function shengxiao($sxs)
    {   //$sx     = ['猪','狗','鸡','猴','羊','马','蛇','龙','兔','虎','牛','鼠'];
        //$sx     = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];
        /*$sx['狗'] = ['01','13','25','37','49'];
        $sx['鸡'] = ['02','14','26','38'];
        $sx['猴'] = ['03','15','27','39'];
        $sx['羊'] = ['04','16','28','40'];
        $sx['马'] = ['05','17','29','41'];
        $sx['蛇'] = ['06','18','30','42'];
        $sx['龙'] = ['07','19','31','43'];
        $sx['兔'] = ['08','20','32','44'];
        $sx['虎'] = ['09','21','33','45'];
        $sx['牛'] = ['10','22','34','46'];
        $sx['鼠'] = ['11','23','35','47'];
        $sx['猪'] = ['12','24','36','48'];*/

        $sx           = $this->format_sx(date('Y'));

        $sx['野兽']   = array_merge($sx['鼠'],$sx['虎'],$sx['兔'],$sx['龙'],$sx['蛇'],$sx['猴']);
        $sx['家禽']   = array_merge($sx['牛'],$sx['马'],$sx['羊'],$sx['鸡'],$sx['狗'],$sx['猪']);
        $sx['单']     = array_merge($sx['鼠'],$sx['虎'],$sx['龙'],$sx['马'],$sx['猴'],$sx['狗']);
        $sx['双']     = array_merge($sx['牛'],$sx['兔'],$sx['蛇'],$sx['羊'],$sx['鸡'],$sx['猪']);
        $sx['前肖']   = array_merge($sx['鼠'],$sx['牛'],$sx['虎'],$sx['兔'],$sx['龙'],$sx['蛇']);
        $sx['后肖']   = array_merge($sx['马'],$sx['羊'],$sx['猴'],$sx['鸡'],$sx['狗'],$sx['猪']);
        $sx['天肖']   = array_merge($sx['牛'],$sx['兔'],$sx['龙'],$sx['马'],$sx['猴'],$sx['猪']);
        $sx['地肖']   = array_merge($sx['鼠'],$sx['虎'],$sx['蛇'],$sx['羊'],$sx['鸡'],$sx['狗']);
        
        return $sx[$sxs];
    }

    private function shebo($bss)
    {
        $bs['红波'] = ['01','02','07','08','12','13','18','19','23','24','29','30','34','35','40','45','46'];
        $bs['蓝波'] = ['03','04','09','10','14','15','20','25','26','31','36','37','41','42','47','48'];
        $bs['绿波'] = ['05','06','11','16','17','21','22','27','28','32','33','38','39','43','44','49'];
        return $bs[$bss];
    }

    private function hkwx($wxs)
    {
        $wx['金']    = ['04','05','18','19','26','27','34','35','48','49'];
        $wx['木']    = ['01','08','09','16','17','30','31','38','39','46','47'];
        $wx['水']    = ['06','07','14','18','22','23','36','37','44','45'];
        $wx['火']    = ['02','03','10','11','24','25','32','33','40','41'];
        $wx['土']    = ['12','13','20','21','28','29','42','43'];
        return $wx[$wxs];
    }

    private function format_sx($y = '')
    {
        $y          = (int)$y <= 0 ? (int)date('Y') : (int)$y;

        $sxs        = [];
        $sxs[]      = ['01','13','25','37','49'];
        $sxs[]      = ['02','14','26','38'];
        $sxs[]      = ['03','15','27','39'];
        $sxs[]      = ['04','16','28','40'];
        $sxs[]      = ['05','17','29','41'];
        $sxs[]      = ['06','18','30','42'];
        $sxs[]      = ['07','19','31','43'];
        $sxs[]      = ['08','20','32','44'];
        $sxs[]      = ['09','21','33','45'];
        $sxs[]      = ['10','22','34','46'];
        $sxs[]      = ['11','23','35','47'];
        $sxs[]      = ['12','24','36','48'];

        $sx         = ['猪','狗','鸡','猴','羊','马','蛇','龙','兔','虎','牛','鼠'];
        $sx1        = array_flip($sx);
        $nums       = $sx1[$sx[(int)(11 - ($y-4)%12)]];

        $sx2        = [];
        foreach ($sx as $key=>$val) {
            if ($key >= $nums) {
                $sx2[$key-$nums]        = $val;
            }else{
                $sx2[12-$nums + $key]   = $val;
            }
        }

        ksort($sx2);

        $sx2        = array_flip($sx2);
        foreach ($sx2 as $key => $value) {
            $sx2[$key]  = $sxs[$value];
        }

        return $sx2;
    }
}