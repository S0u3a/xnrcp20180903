<?php
namespace app\manage\controller;

use app\manage\controller\Base2;

/**
 * H5页面展示控制器
 */
class Showhtml extends Base2
{


	//玩法规则
	public function article()
	{	
		
		$aid = input('get.aid');
		$aid = empty($aid)?1:$aid;	//1:彩票玩法说明 2:推广规则 3:推广明细 4:帮助中心 5:会员折扣 6:优惠活动 7:常见问题
		$info = model('shuoming')->where(['id'=>$aid])->find();
		$this->assign('info',$info);  
		return view('article');
	}

	//分享
	public function share()
	{	
		return view('share');
	}

}