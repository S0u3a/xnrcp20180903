<?php
/**
 * 彩票基础类
 * @author 王远庆 <[562909771@qq.com]>
 */

namespace app\api\lottery;

use think\Controller;
use think\facade\Lang;

class Base
{
    private $lotteryUrl         = '';
    public $lotteryid           = 0;
    public $domainsUrl          = '';

    public function __construct($lotteryid=0)
    {
      $this->lotteryUrl   = 'http://ho.apiplus.net/newly.do?token=t3a1e89426cd3c6d6k&';
      $this->lotteryid    = $lotteryid;
      $this->domainsUrl   = trim(get_domain(),'/') . '/' . 'api/lottery/getlottery/?';
    }

    //彩票接口地址定义
    private function getLotteryUrl()
    {
        $url        = [];
        $url[89]    = $this->domainsUrl.'code=ffssc&format=json';//分分时时彩
        $url[90]    = $this->domainsUrl.'code=3fssc&format=json';//3分时时彩
        $url[92]    = $this->lotteryUrl.'code=cqssc&format=json';//重庆时时彩
        $url[93]    = $this->lotteryUrl.'code=xjssc&format=json';//新疆时时彩
        $url[94]    = $this->lotteryUrl.'code=hljssc&format=json';//黑龙江时时彩
        $url[95]    = $this->lotteryUrl.'code=tjssc&format=json';//天津时时彩
        $url[97]    = $this->lotteryUrl.'code=bjpk10&format=json';//北京PK拾
        $url[100]   = $this->lotteryUrl.'code=hk6&format=json';//香港六合彩
        $url[103]   = $this->lotteryUrl.'code=ahk3&format=json';//安徽快三
        $url[104]   = $this->lotteryUrl.'code=jlk3&format=json';//吉林快三
        $url[105]   = $this->lotteryUrl.'code=gxk3&format=json';//广西快三
        $url[106]   = $this->lotteryUrl.'code=jsk3&format=json';//江苏快三
        $url[107]   = $this->lotteryUrl.'code=hubk3&format=json';//湖北快3
        $url[109]   = $this->lotteryUrl.'code=sd11x5&format=json';//山东11选5
        $url[110]   = $this->lotteryUrl.'code=gd11x5&format=json';//广东11选5
        $url[111]   = $this->lotteryUrl.'code=sh11x5&format=json';//上海11选5
        $url[112]   = $this->lotteryUrl.'code=js11x5&format=json';//江苏11选5
        $url[113]   = $this->lotteryUrl.'code=hub11x5&format=json';//湖北11选5
        $url[114]   = $this->lotteryUrl.'code=gx11x5&format=json';//广西11选5
        $url[116]   = $this->lotteryUrl.'code=bjkl8&format=json';//北京28

        return isset($url[$this->lotteryid]) ? $url[$this->lotteryid] : '';
    }

    public function format_lottery_limit($his)
    {
      return strtotime(date('Y-m-d ' . $his));
    }

    public function getData()
    {
      $url    = $this->getLotteryUrl();
      $url    .= (strpos($url,'?')>0 ? '&':'?').'_='.time();

      $html   = file_get_contents($url);
      $json   = json_decode($html,true);

      return isset($json['rows']) ? $json['data'] : [];
    }
}