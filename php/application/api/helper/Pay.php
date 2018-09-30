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
use Payment\Client\Charge;
use Payment\Common\PayException;

class Pay extends Base
{
	private $dataValidate 		= null;
    private $mainTable          = 'order_recharge';
	
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

    /*api:3134925f23ab1238e4a57211b7f16e53*/
    /**
     * * 账户充值
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function recharge($parame)
    {
        try{

            $paytype        = $parame['pay_type'] ;
            $banktag        = '';

            if(!in_array($paytype,[1,2,3]))
            return ['Code' => '200001', 'Msg'=>lang('200001')];

            //支付方式为银联支付时需要校验银行是否存在
            /*if ($paytype == 3) {
                $banktag    = isset($parame['banktag']) ? trim($parame['banktag']) : '';
                $bank       = $this->bankInfo($banktag);
                if (empty($bank)) return ['Code' => '200003', 'Msg'=>lang('200003')];
            }*/

            //订单编号
            $order_sn               = date('ymdHis',time()).randomString(3,0) ;

            $body                   = '充值订单' ;
            $attach                 = '充值订单' ;
            $fee                    = floatval($parame['money']);
            $order_type             = 1 ;
            $uid                    = $parame['uid'];

            //$fee = 50;

            $extend                  = [] ;
            $extend['order_sn']     = $order_sn;
            $extend['money']        = $fee;
            $extend['order_type']   = $order_type;
            $extend['pay_type']     = $paytype;
            $extend['uid']          = $parame['uid'] ;

            $payInfo   = $this->getPayInfo($order_sn,$body,$attach,$fee,$paytype,$extend,$uid,$banktag);
            if($payInfo['Code'] !== '000000') return ['Code' => $payInfo['Code'], 'Msg'=>$payInfo['Msg']];

            //事先写入订单数据 未支付状态
            $orderData                  = [] ;
            $orderData['uid']           = $uid ;
            $orderData['order_sn']      = $order_sn;
            $orderData['out_trade_no']  = $order_sn ;
            $orderData['money']         = $fee ;
            $orderData['price']         = $fee ;
            $orderData['pay_type']      = $paytype;
            $orderData['status']        = 1 ;
            $orderData['create_time']   = time() ;
            $orderData['update_time']   = time() ;

            model('order_recharge') ->addData($orderData) ;

            return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$payInfo['Data']];
        }catch (\Exception $exception){
            return ['Code'=>(string)$exception->getCode(),'Msg'=>$exception->getCode()==0?$exception->getMessage().$exception->getLine():$exception->getMessage()] ;
        }
    }

    /*api:3134925f23ab1238e4a57211b7f16e53*/

    /*api:97a96cfbdb3effe2be005c5df98736af*/
    /**
     * * 提现代付
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function substitute($parame)
    {
        $time                       = time();
        /*$data                       = [];
        $data['insCode']            = '80000384';
        $data['insMerchantCode']    = '887581298600467';
        $data['hpMerCode']          = 'WKJGWKQTCS@20180813173307';
        $data['orderNo']            = date('YmdHis',$time) . randomString(6);;
        $data['orderDate']          = date('Ymd',$time);
        $data['orderTime']          = date('YmdHis',$time);
        $data['currencyCode']       = 156;
        $data['orderAmount']        = 100;
        $data['orderType']          = 'D0';
        $data['certType']           = '01';
        $data['certNumber']         = '341126197709218366';
        $data['accountType']        = '01';
        $data['accountName']        = '互联网';
        $data['accountNumber']      = '6221558812340000';
        $data['mainBankName']       = '';
        $data['mainBankCode']       = '';
        $data['openBranchBankName'] = '';
        $data['mobile']             = '13552535506';
        $data['attach']             = '用户提现';
        $data['nonceStr']           = randomString(15,7);

        $keys       = '3F7DB75AFBE34A4B40ECD0CC4A8B6492';
        $signArr    = [$data['insCode'],$data['insMerchantCode'],$data['hpMerCode'],$data['orderNo'],$data['orderDate'],$data['orderTime'],$data['currencyCode'],$data['orderAmount'],$data['orderType'],$data['accountType'],$data['accountName'],$data['accountNumber'],$data['nonceStr'],$keys];
        $data['signature']          = md5(implode('|',$signArr));

        $url    = 'https://gateway.handpay.cn/hpayTransGatewayWeb/trans/df/transdf.htm';
print_r($data);exit;*/
        print_r(1);exit;
        $mid                        = '13010152';
        $order_sn                   = date('YmdHis',$time) . randomString(6);
        $money                      = '000000000100';
        $subject                    = '支付提现转账';
        $data = [
            'head' => [
                'version'           => '1.0',
                'method'            => 'sandPay.fastPay.quickPay.index',
                'productId'         => '00000016',
                'accessType'        => '1',
                'mid'               => $mid,
                'channelType'       => '07',
                'reqTime'           => date('YmdHis', $time)
            ],
            'body' => [
                'userId'            => $_POST['userId'],
                'orderCode'         => $order_sn,
                'orderTime'         => date('YmdHis', $time),
                'totalAmount'       => $money,
                'subject'           => $_POST['subject'],
                'body'              => $_POST['body'],
                'currencyCode'      => $_POST['currencyCode'],
                'notifyUrl'         => $_POST['notifyUrl'],
                'frontUrl'          => $_POST['frontUrl'],
                'clearCycle'        => $_POST['clearCycle'],
                'extend'            => ''
            ]
        ];

        print_r($data);exit;
        $res    = CurlHttp($url,$data,'POST');

        

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>[]];
    }

    /*api:97a96cfbdb3effe2be005c5df98736af*/

    /*接口扩展*/

    private function getPayInfo($order_sn, $body, $attach, $fee, $paytype,$extend,$uid,$banktag='')
    {
        switch (intval($paytype)){
            case 1 :
                try{
                    //获取配置
                    $config = config('pay.alipay');

                    if (empty($config)) return ['Code' => '200002', 'Msg'=>lang('200002')];

                    $options = [
                        'order_no'          => $order_sn, // 订单号
                        'amount'            => $fee , // 订单金额，**单位：元**
                        'subject'           => $attach, // 订单描述
                        'body'              => $body, // 订单描述
                        'spbill_create_ip'  => get_client_ip() , // 支付人的 IP
                        'return_param'      => urlsafe_b64encode(json_encode($extend)),     //不变的返回参数
                        'goods_type'        => 1 ,
                    ];

                    $payInfo = Charge::run('ali_app',$config,$options);

                    return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['alipay'=>$payInfo,'wxpay'=>[]]];

                }catch (PayException $exception){
                    return ['Code'=>'10000','Msg'=>$exception->errorMessage()] ;
                }
                break ;
            case 2 :
                try{
                    //获取配置
                    $config = config('pay.wechat');

                    if (empty($config)) return ['Code' => '200002', 'Msg'=>lang('200002')];

                    $options = [
                        'order_no'          => $order_sn, // 订单号
                        'amount'            => $fee , // 订单金额，**单位：元**
                        'subject'           => $attach, // 订单描述
                        'body'              => $body, // 订单描述
                        'client_ip'         => get_client_ip() , // 支付人的 IP
                        'timeout_express' => time() + 600,// 表示必须 600s 内付款
                        'return_param'      => urlsafe_b64encode(json_encode($extend)),//不变的返回参数
                        'goods_type'        => 1,
                    ];

                    $payInfo = Charge::run('wx_app',$config,$options);
                    $payInfo = !empty($payInfo) ? json_encode($payInfo) : '';
                    return ['Code' => '000000','Msg'=>lang('000000'),'Data'=>['alipay'=>"",'wxpay'=>$payInfo]];

                }catch (PayException $exception){
                    return ['Code'=>'10000','Msg'=>$exception->errorMessage()] ;
                }
                break ;
            case 3://泰佳科技-聚合通道 H5订单交易接口<微信v1.0>
                try{
                    //获取配置
                    $config                     = config('pay.sslpayment');

                    //获取配置
                    $payData                    = [];
                    $payData['TrCode']          = '4005';
                    $payData['InstNo']          = $config['InstNo'];
                    $payData['MchtNo']          = $config['MchtNo'];
                    $payData['ReturnURL']       = 'http://xnrcp20180903.php.xnrcms.cn/api/Crontab/paySuccess/pay_type/3/uid/'.$uid;
                    $payData['OrderNo']         = $order_sn;
                    $payData['OrderAmount']     = $fee;
                    $payData['Rsv']             = '0';
                    $mac                        = md5($config['InstNo'].$payData['OrderNo'].$config['SignKey']);
                    $payData['Mac']             = strtoupper($mac);

                    $url                        = 'http://www.bfhnj.top:8080/TopWeb/HLCNL/HLpay.do';
                    $payInfo                    = CurlHttp($url,$payData,'POST');

                    $payInfo                    = !empty($payInfo) ? json_decode($payInfo,true) : '';

                    if (isset($payInfo['PayUrl']) && !empty($payInfo['PayUrl'])) {
                        
                        /*$qrcode     = new \xnrcms\QRcode();
                        $filename   = './uploads/payqrcode/'. $mac . '.png';
                        $qrcode->png($payInfo['PayUrl'],$filename,'L',6);*/

                        $path       = $payInfo['PayUrl'];
                        return ['Code' => '000000','Msg'=>lang('000000'),'Data'=>['alipay'=>$path,'wxpay'=>[]]];
                    }

                    return ['Code' => '200004', 'Msg'=>lang('200004')];break ;

                }catch (PayException $exception){
                    return ['Code'=>'10000','Msg'=>$exception->errorMessage()] ;
                }
                break ;
            default :
                return ['Code' => '200001', 'Msg'=>lang('200001')];break ;
        }
    }

    private function bankInfo($tag='')
    {
        $back               = [];
        $bank['ICBC']       = '工商银行';
        $bank['ABC']        = '农业银行';
        $bank['BOCSH']      = '中国银行';
        $bank['CCB']        = '建设银行';
        $bank['CMB']        = '招商银行';
        $bank['SPDB']       = '上海浦东发展银行';
        $bank['GDB']        = '广发银行';
        $bank['BOCOM']      = '交通银行';
        $bank['PSBC']       = '邮政储蓄银行';
        $bank['CNCB']       = '中信银行';
        $bank['CMBC']       = '民生银行';
        $bank['CEB']        = '光大银行';
        $bank['HXB']        = '华夏银行';
        $bank['CIB']        = '兴业银行';
        $bank['PAB']        = '平安银行';
        $bank['BOS']        = '上海银行';
        $bank['BCCB']       = '北京银行';
        $bank['SRCB']       = '上海农村商业银行';
        $bank['BRCB']       = '北京农村商业银行';

        return isset($bank[$tag]) ? $bank[$tag] : '';
    }
}
