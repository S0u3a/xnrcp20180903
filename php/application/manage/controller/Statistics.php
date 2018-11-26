<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 杭州新苗科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: xnrcms<562909771@qq.com>
 * Date: 2018-04-10
 * Description:广告模块
 */

namespace app\manage\controller;

use app\manage\controller\Base;

class Statistics extends Base
{
    private $apiUrl         = [];

    public function __construct()
    {
        parent::__construct();

        $nowTime        = strtotime(date('Y-m-d 59:59:59'));
        $lastTime       = db('data_statistics')->order('data_time DESC')->value('data_time');

        $days           = (int)(($nowTime - (!empty($lastTime) ? $lastTime : $nowTime - 86400*60) ) / 86400) - 1;

        if ($days >= 0) {
            for ($i=$days; $i >=0 ; $i--) {
                $updata[]   = [
                    'data_time'        => strtotime(date('Y-m-d 23:59:59')) - 86400*$i
                ];
            }

            if (!empty($updata)) db('data_statistics')->insertAll($updata);
        }
    }

	//列表页面
	public function index()
    {
		$menuid     = input('menuid',0) ;
		$search 	= input('search','');
        $page       = input('page',1);
        //sendJpus('','',['extras'=>json_encode(['wang'=>1,'abc'=>2,'ssss'=>3,'time'=>time()])]);
        //页面操作功能菜单
        $topMenu    = formatMenuByPidAndPos($menuid,2, $this->menu);
        $rightMenu  = formatMenuByPidAndPos($menuid,3, $this->menu);

        //获取表头以及搜索数据
        $tags       = strtolower(request()->controller() . '/' . request()->action());
        $listNode   = $this->getListNote($tags) ;

        $wtime          = '(create_time <= data_time) AND create_time > (data_time - 86400)';

        $field          = [];
        $field[]        = 'id';
        $field[]        = 'data_time';
        $field[]        = '(select COUNT(*) from duoduo_data_statistics where 1) as abcd';
        $field[]        = '(select SUM(money) from duoduo_order_recharge where '.$wtime.' AND status >= 1) as rmoney3';
        $field[]        = '(select SUM(money) from duoduo_order_recharge where '.$wtime.' AND status = 2) as rmoney2';
        $field[]        = '(select SUM(money) from duoduo_order_recharge where '.$wtime.' AND status = 1) as rmoney1';

        $field[]        = '(select SUM(money) from duoduo_bank_cash where '.$wtime.' AND status >= 1) as cmoney1';
        $field[]        = '(select SUM(money) from duoduo_bank_cash where '.$wtime.' AND status = 1) as cmoney2';
        $field[]        = '(select SUM(money) from duoduo_bank_cash where '.$wtime.' AND status = 3) as cmoney3';
        $field[]        = '(select SUM(money) from duoduo_bank_cash where '.$wtime.' AND status = 4) as cmoney4';

        $field[]        = '(select COUNT(*) from duoduo_lottery_order where '.$wtime.' AND iswin >= 0) as lnum1';
        $field[]        = '(select COUNT(*) from duoduo_lottery_order where '.$wtime.' AND iswin = 0) as lnum2';
        $field[]        = '(select COUNT(*) from duoduo_lottery_order where '.$wtime.' AND iswin = 1) as lnum3';

        $field[]        = '(select SUM(money) from duoduo_lottery_order where '.$wtime.' AND iswin >= 0) as lmoney1';
        $field[]        = '(select SUM(win_umoney) from duoduo_lottery_order where '.$wtime.' AND iswin = 1) as lmoney2';

        $data_time_start    = input('searchdata_time_start','');
        $data_time_end      = input('searchdata_time_end','');
        $map                = [];

        if ( !empty($data_time_start) || !empty($data_time_end) )
        {
            $data_time_start    = !empty($data_time_start) ? strtotime($data_time_start . ' 00:00:00') : strtotime(date('Y-m-d 00:00:00'));
            $data_time_end      = !empty($data_time_end) ? strtotime($data_time_end . ' 23:59:59') : strtotime(date('Y-m-d 23:59:59'));

            if ($data_time_start < $data_time_end) {
                $map[]      = ['data_time','>=',$data_time_start];
                $map[]      = ['data_time','<=',$data_time_end];
            }
        }

        $listLimit      = 15;
        $listTotal      = db('data_statistics')->where($map)->count('id');
        $listData       = db('data_statistics')->field($field)->where($map)->page($page)->limit($listLimit)->order('id DESC')->select();
        
        foreach ($listData as $key => $value) {
            $listData[$key]['data_time']   = date('Y-m-d',$value['data_time']);
            $listData[$key]['rmoney']      = implode(',', ['成功：'.(float)$value['rmoney2'],'&nbsp;失败：'.(float)$value['rmoney1'],'&nbsp;总和：'.(float)$value['rmoney3']]);
            $listData[$key]['cmoney']      = implode(',', ['审核中：'.(float)$value['cmoney2'],'&nbsp;通过：'.(float)$value['cmoney3'],'&nbsp;驳回：'.(float)$value['cmoney4'],'&nbsp;总和：'.(float)$value['cmoney1']]);
            $listData[$key]['lnum']        = implode(',', [
                '注单总数：'.(float)$value['lnum1'],
                '&nbsp;已中注单：'.(float)$value['lnum3'],
                '&nbsp;未中注单：'.(float)$value['lnum2'],
                '&nbsp;注单总额：'.(float)$value['lmoney1'],
                '&nbsp;赔付总额：'.((float)$value['lmoney2'])
            ]);

            $listData[$key]['money']        = (float)$value['lmoney1']  - (float)$value['lmoney2'];
        }

        $p 					= '';
        if ($listData){
            //分页信息
            $page           = new \xnrcms\Page($listTotal, $listLimit);
            if($listTotal >= 1){

                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                $page->setConfig('header','');
            }

            $p 				= trim($page->show());
        }

        //页面头信息设置
        $pageData['isback']             = 0;
        $pageData['title1']             = '数据统计';
        $pageData['title2']             = '数据统计索引与管理';
        $pageData['notice']             = ['数据统计展示每日的数据统计和.'];

        //渲染数据到页面模板上
        $assignData['_page']            = $p;
        $assignData['_total']           = $listTotal;
        $assignData['topMenu']          = $topMenu;
        $assignData['rightMenu']        = $rightMenu;
        $assignData['listId']           = isset($listNode['info']['id']) ? intval($listNode['info']['id']) : 0;
        $assignData['listNode']         = $listNode;
        $assignData['listData']         = $listData;
        $assignData['pageData']         = $pageData;
        $this->assignData($assignData);

        //记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        //异步请求处理
        if(request()->isAjax()){

            echo json_encode(['listData'=>$this->fetch('public/list/listData'),'listPage'=>$p]);exit();
        }

        //加载视图模板
        return view();
	}
}
?>