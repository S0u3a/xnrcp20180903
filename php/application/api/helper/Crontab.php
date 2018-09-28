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
use Payment\Client\Notify;
use Payment\Common\PayException;
use Payment\Notify\PayNotifyInterface;

class Crontab extends Base
{
    private $dataValidate       = null;
    private $mainTable          = 'ad';
    
    public function __construct($parame=[],$className='',$methodName='',$modelName='')
    {
        parent::__construct($parame,$className,$methodName,$modelName);
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
    private function crontab($parame)
    {
        wr("",'info.txt',false);
        wr("\n\n...........开始执行[".date("Y-m-d H:i:s")."]...........\n\n");

        //时时彩
        $this->ffsscApiData();
        $this->sfsscApiData();
        $this->cqsscApiData();
        $this->xjsscApiData();
        $this->tjsscApiData();
        $this->hljsscApiData();

        $this->pk10ApiData();
        $this->hk6ApiData();

        //快3
        $this->ahk3ApiData();
        $this->jlk3ApiData();
        $this->gxk3ApiData();
        $this->jsk3ApiData();
        $this->hubk3ApiData();

        //11选5
        $this->sd11x5ApiData();
        $this->gd11x5ApiData();
        $this->sh11x5ApiData();
        $this->js11x5ApiData();
        $this->hub11x5ApiData();
        $this->gx11x5ApiData();

        //PC蛋蛋
        $this->bjkl8ApiData();

        //分分时时彩彩和三分时时彩
        $this->ssc1f();
        $this->ssc3f();

        //开奖
        $this->openPrize();

        wr("\n\n...........结束执行[".date("Y-m-d H:i:s")."]...........\n\n");
        return;
    }
    //分分时时彩接口数据采集
    private function ffsscApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(89);
        $lottery->updateData();
        return true;
    }
    //3分时时彩接口数据采集
    private function sfsscApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(90);
        $lottery->updateData();
        return true;
    }
    //重庆时时彩接口数据采集
    private function cqsscApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(92);
        $lottery->updateData();
        return true;
    }

    //新疆时时彩接口数据采集
    private function xjsscApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(93);
        $lottery->updateData();
        return true;
    }

    //黑龙江时时彩接口数据采集
    private function hljsscApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(94);
        $lottery->updateData();
        return true;
    }

    //天津时时彩接口数据采集
    private function tjsscApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(95);
        $lottery->updateData();
        return true;
    }

    //北京PK10接口数据采集
    private function pk10ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(97);
        $lottery->updateData();
        return true;
    }

    //香港六合彩 接口数据采集
    private function hk6ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(100);
        $lottery->updateData();
        return true;
    }

    //快3 - 安徽
    private function ahk3ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(103);
        $lottery->updateData();
        return true;
    }
    //快3 - 吉林
    private function jlk3ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(104);
        $lottery->updateData();
        return true;
    }
    //快3 - 广西
    private function gxk3ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(105);
        $lottery->updateData();
        return true;
    }
    //快3 - 江苏
    private function jsk3ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(106);
        $lottery->updateData();
        return true;
    }
    //快3 - 湖北
    private function hubk3ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(107);
        $lottery->updateData();
        return true;
    }

    //11选5 - 山东
    private function sd11x5ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(109);
        $lottery->updateData();
        return true;
    }

    //11选5 - 广东
    private function gd11x5ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(110);
        $lottery->updateData();
        return true;
    }

    //11选5 - 上海
    private function sh11x5ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(111);
        $lottery->updateData();
        return true;
    }

    //11选5 - 江苏
    private function js11x5ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(112);
        $lottery->updateData();
        return true;
    }

    //11选5 - 湖北
    private function hub11x5ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(113);
        $lottery->updateData();
        return true;
    }

    //11选5 - 广西
    private function gx11x5ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(114);
        $lottery->updateData();
        return true;
    }

    //PC蛋蛋 - 北京28
    private function bjkl8ApiData()
    {
        $lottery        = new \app\api\lottery\Lottery(116);
        $lottery->updateData();
        return true;
    }

    public function openPrize()
    {
        $lottery        = new \app\api\lottery\Lottery(0);
        $lottery->openPrize();
        return true;
    }

    /**
     * @param array $parame
     * @return array
     */
    private function paySuccess($parame=[])
    {
        $parame             = is_array($parame) ? $parame : json_decode($parame,true) ;
        $payType            = $parame['pay_type'] ;
        switch ($payType){
            case 1:
                //获取配置
                $config = config('pay.alipay');
                try {

                    $ret = Notify::run('ali_charge', $config, new ThirdPayNoticeHelper());// 处理回调，内部进行了签名检查

                    $data = Notify::getNotifyData('ali_charge', $config);

                    $passback_params            = $data['passback_params'] ;
                    $passback_params            = json_decode(urlsafe_b64decode($passback_params),true) ;

                    $out_trade_no               = $data['trade_no'] ;
                    $money                      = $data['total_amount'] ;

                    print_r($ret);
                } catch (PayException $e) {
                    dblog($e) ; exit;
                }

                break;
            case 2:
                try {
                    //获取配置
                    $config         = config('pay.wechat');
                    $ret            = Notify::run('wx_charge', $config, new ThirdPayNoticeHelper());// 处理回调，内部进行了签名检查

                    $data           = Notify::getNotifyData('wx_charge', $config);

                    $passback_params            = $data['attach'] ;
                    $passback_params            = json_decode(urlsafe_b64decode($passback_params),true) ;

                    $out_trade_no               = $data['transaction_id'] ;
                    $money                      = $data['total_fee']/100 ;

                    print_r($ret) ;
                } catch (PayException $e) {
                    dblog($e) ;
                    exit;
                }

                break ;
            case 3:

                wr([request()->param()]);
                try {
                    //获取配置
                    $config                 = config('pay.sslpayment');
                    $passback_params        = request()->param();
                    $return_param           = json_decode(urlsafe_b64decode($passback_params['return_param']),true) ;
                    $passback_params        = array_merge($passback_params,$return_param);

                    $out_trade_no           = $passback_params["BillNo"];
                    $money                  = $passback_params["Amount"];
                    $Succeed                = $passback_params["Succeed"];     
                    $MD5info                = $passback_params["MD5info"];
                    $Result                 = $passback_params["Result"];
                    $MerNo                  = $passback_params['MerNo'];
                    $MD5key                 = "12345678";
                    $MerRemark              = $passback_params['MerRemark'];     //自定义信息返回
                    $md5sign                = getSignature($MerNo, $out_trade_no, $money, $Succeed, $MD5key);
                    if ($MD5info == $md5sign) {
                        if ($Succeed == '88') {
                            //成功，请回写success给服务器             
                            print $Result.'Update order status to successful'.$Succeed;//更新订单状态为支付成功
                        }else {
                            //失败
                            print 'Update order status to:'.$Result.$Succeed;//更新订单状态为其他状态
                        }  
                    }  else {
                        //验证失败
                        echo $Result.$Succeed;
                    }
                } catch (PayException $e) {
                    dblog($e) ;
                    exit;
                }

                break ;
            default :
                break ;
        }

        return $this->updateOrder($passback_params,$out_trade_no,$money,$payType) ;
    }

    /**
     * @param $passback_params
     * @param $order_type
     * @param $out_trade_no
     * @param $money
     * @param $payType
     * @return array
     * @throws
     */
    private function updateOrder($passback_params, $out_trade_no, $money, $payType)
    {
        return $this->updateRechargeOrder($passback_params,$out_trade_no,$money,$payType) ;
    }

    private function updateRechargeOrder($passback_params, $out_trade_no, $money, $payType)
    {
        try{
            
            $find_status = model('order_recharge')->where(['order_sn'=>$passback_params['order_sn'],'uid'=>$passback_params['uid']])->value('status');
            if($find_status != 2){
                //准备用户订单购买数据
                $orderData                  = [] ;
                $orderData['uid']           = $passback_params['uid'] ;
                $orderData['order_sn']      = $passback_params['order_sn'] ;
                $orderData['out_trade_no']  = $out_trade_no ;
                $orderData['money']         = $passback_params['money'] ;
                $orderData['price']         = $money ;
                $orderData['pay_type']      = $passback_params['pay_type'] ;
                $orderData['status']        = 1 ;
                $orderData['create_time']   = time() ;
                $orderData['update_time']   = time() ;

                model('order_recharge') ->addData($orderData) ;
                model('user_account_log')->addAccountLog($orderData['uid'],$orderData['money'],'余额充值',1,3);

                //用户收入增加
                $res = model('user_detail') -> where('uid',$passback_params['uid']) -> setInc('account',$passback_params['money']) ;
                model('user_detail')->delDetailDataCacheByUid($passback_params['uid']);

                if($res !== false){
                    model('order_recharge')->where(['order_sn'=>$passback_params['order_sn'],'uid'=>$passback_params['uid']])->update(['status'=>2]);
                }
            }
            
            return 1;
        }catch (\Exception $exception){
            return ['Code'=>(string)$exception->getCode(),'Msg'=>$exception->getCode()==0?$exception->getMessage().$exception->getLine():$exception->getMessage()] ;
        }
    }

    public function ssc1f()
    {
        $cacheKey       = 'ssc1f_key';
        $addtime        = cache($cacheKey);
        if (!empty($addtime) && $addtime > time()) return false;

        $dbModel        = model('lottery_ssc1');
        $info           = $dbModel->getInfoByLimitTime();
        
        $ff1            = strtotime(date('Ymd 00:00:00'));
        $ff2            = strtotime(date('Ymd H:i:00'));

        //是否被1分钟整除
        if (($ff2-$ff1)%60 != 0) return false;

        $ff             = ($ff2-$ff1)/60;
        if ($ff >= 0 && $ff < 10) {
            $ff             = '000' . $ff;
        }elseif ($ff >= 10 && $ff < 100) {
            $ff             = '00' . $ff;
        }elseif ($ff >= 100 && $ff < 1000) {
            $ff             = '0' . $ff;
        }

        $expect         = date('Ymd').$ff;

        $code           = randomString(5);
        $temp           = [];
        for ($i=0; $i < 5; $i++) { 
            $temp[]     = substr($code,$i,1);
        }

        if(empty($info)){
            $updata['expect']       = $expect;
            $updata['opencode']     = implode(',',$temp);
            $updata['opentime']     = date('Y-m-d H:i:s',$ff2);
            $updata['opentimestamp']= $ff2;
            
            $dbModel->addData($updata);
        }else{
            $addtimestamp          = $info['opentimestamp']+60;
            if ($addtimestamp > time()) {
                cache($cacheKey,$addtimestamp);
                return false;
            }else{

                $updata['expect']       = $expect;
                $updata['opencode']     = implode(',',$temp);
                $updata['opentime']     = date('Y-m-d H:i:s',$ff2);
                $updata['opentimestamp']= $ff2;
                $dbModel->addData($updata);
            }
        }

        //删除无用数据
        $info       = $dbModel->getInfoByLimitTime();
        $id         = isset($info['id']) ? $info['id']-11 : 0;
        $dbModel->delInfoById($id);

        $addtimestamp       = $ff2+60;
        cache($cacheKey,$addtimestamp);
    }

    public function ssc3f()
    {
        $cacheKey       = 'ssc3f_key';
        $addtime        = cache($cacheKey);
        if (!empty($addtime) && $addtime > time()) return false;

        $dbModel        = model('lottery_ssc3');
        $info           = $dbModel->getInfoByLimitTime();
        
        $ff1            = strtotime(date('Ymd 00:00:00'));
        $ff2            = strtotime(date('Ymd H:i:00'));

        //是否被3分钟整除
        if (($ff2-$ff1)%180 != 0) return false;

        $ff             = ($ff2-$ff1)/180;
        $number         = (substr(date('Ymd'),5).'000')*1+$ff;
        $expect         = substr(date('Ymd'),0,5).$number;
        $code           = randomString(5);
        $temp           = [];
        for ($i=0; $i < 5; $i++) { 
            $temp[]     = substr($code,$i,1);
        }

        if(empty($info)){
            $updata['expect']       = $expect;
            $updata['opencode']     = implode(',',$temp);
            $updata['opentime']     = date('Y-m-d H:i:s',$ff2);
            $updata['opentimestamp']= $ff2;
            $dbModel->addData($updata);
        }else{
            $addtimestamp          = $info['opentimestamp']+180;
            if ($addtimestamp > time()) {
                cache($cacheKey,$addtimestamp);
                return false;
            }else{

                $updata['expect']       = $expect;
                $updata['opencode']     = implode(',',$temp);
                $updata['opentime']     = date('Y-m-d H:i:s',$ff2);
                $updata['opentimestamp']= $ff2;
                $dbModel->addData($updata);
            }
        }

        //删除无用数据
        $info       = $dbModel->getInfoByLimitTime();
        $id         = isset($info['id']) ? $info['id']-10 : 0;
        $dbModel->delInfoById($id);

        $addtimestamp       = $ff2+180;
        cache($cacheKey,$addtimestamp);
    }
    /*接口扩展*/
}
