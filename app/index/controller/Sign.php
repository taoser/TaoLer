<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\model\User;
use think\facade\Session;
use think\facade\Db;
use think\facade\View;
use think\Collection;
use think\Response;

class Sign extends BaseController
{

    protected $uid;

    protected function _initialize()
    {
        parent::_initialize();
        $this->uid = session('user_id');
		
    }

    public function lists()
    {
        //总榜
        $totallist = Db::name('user_sign')->alias('s')->join('user u', 's.uid=u.id', 'LEFT')->field('s.uid,s.id,max(s.days) as days,u.name as name,u.user_img as user_img')->group('s.uid')->order('days desc')->limit(20)->select();
        $time = time();
        $start_stime = strtotime(date('Y-m-d 0:0:0', $time)) - 1;
        $end_stime = strtotime(date('Y-m-d 23:59:59', $time)) + 1;
        //今日最快
        $fastlist = Db::name('user_sign')->alias('s')->join('user u', 's.uid=u.id')->field('s.*,u.name as name,u.user_img as user_img')->where("s.stime > $start_stime and s.stime < $end_stime")->order('s.id asc')->limit(20)->select();
        //最新
        $newlist = Db::name('user_sign')->alias('s')->join('user u', 's.uid=u.id')->field('s.*,u.name as name,u.user_img as user_img')->order('id desc')->limit(20)->select();

        View::assign('totallist', $totallist);
        View::assign('fastlist', $fastlist);
        View::assign('newlist', $newlist);
        return View::fetch();
    }

    /**
     * 执行当天签到
     * @return json 签到成功返回 {status:1,info:'已签到'}
     */
    public function sign()
    {
        if (!Session::has('user_id') || !Session::has('user_name')) {
            return json(array('code' => 0, 'msg' => '亲，登陆后才能签到哦','url' => 'index/login/index'));
        } else {
            $uid = session('user_id');
			$todayData = $this->todayData()->getData();    
			//var_dump($todayData);

            if ($todayData['is_sign'] == 1) { //数组中是返回的是一个对象，不能直接用[]来显示，正确的输出方法是：$pic[0]->title问题解决！
                exit('{"code":-1,"msg":"你今天已经签过到了"}');
            } else {
                $data = $this->getInsertData($uid);

                $days = $data['days'];
                // 无今天数据

                $data['uid'] = $uid;
                $data['stime'] = time();
                $id = Db::name('user_sign')->insertGetId($data);

                if ($id) {
                    //$will_getscore
                    //$score = $this->getTodayScores($days);
                    $score = $todayData['will_getscore'];
                    $date=date('Ymd');
                    $msg='';
                    $teshudate=['20180215','20180216','20180217','20180218','20180219','20180220','20180221'];
                    //签到奖励
                    if(in_array($date,$teshudate)){
                        $randnum=rand(1,99);
                        $msg='新年好！您额外获得随机奖励'.$randnum.'金币！';
                        point_note($randnum, $uid, 'NewYearReward', $id);
                    }

                    session('signdate', $date);
                    
                    if ($score > 0) {
                        // 为该用户添加积分
						$user = User::find($uid);
						$point = $user['point']+$score;
						$user->save(['point' => $point]);
                        //point_note($score, $uid,  $id);
                      
                    }
                    return json(['code'=>200,'score'=>$score,'days'=>$days,'msg'=>$msg]);
                   // exit('{"code":200,"score":"' . $score . '","days":"' . $days . '"}');
                } else {
                    exit('{"code":-1,"msg":"签到失败，请刷新后重试！"}');
                }
            }
        }
    }
    /**
     * 返回每次签到要插入的数据
     *
     * @param int $uid 用户id
     * @return array(
     *  'days'   =>  '天数',
     *  'is_sign'  =>  '昨天是否签到,用1表示已经签到',
     *  'stime'   =>  '签到时间',
     * );
     */
    protected function getInsertData($uid)
    {
        // 昨天的连续签到天数
        $start_time = strtotime(date('Y-m-d 0:0:0', time() - 86400)) - 1;
        $end_time = strtotime(date('Y-m-d 23:59:59', time() - 86400)) + 1;
        $days = Db::name('user_sign')->where("uid = $uid and stime > $start_time and stime < $end_time")->value('days');
        if ($days) {
            $days++;
            $is_sign = 1;
            $time = time();
            // if($days > 30){
            //   $days = 1;
            // }
        } else {
            //$days=1;
            $is_sign = 0;
            $days = 1;
            $time = '';
        }
        return [
            'days' => $days,
            'is_sign' => $is_sign,
            'stime' => $time,
        ];
    }
    /**
     * 用户当天签到的数据
     * @return array 签到信息 is_sign,stime 等
     */
    public function todayData()
    {
        $time = time();
        $start_stime = strtotime(date('Y-m-d 0:0:0', $time)) - 1;
        $end_stime = strtotime(date('Y-m-d 23:59:59', $time)) + 1;
        $res = Db::name('user_sign')->where('uid',session('user_id'))->where('stime', '>', $start_stime)->where('stime', '<', $end_stime)->find();
        $score = 0;
        if ($res) {
            $is_sign = 1;
            //昨天已签到
            //已连续签到 已获取
            $days = $res['days'];
            $score = $this->getTodayScores($res['days']);
            $will_getscore = $this->getTodayScores($res['days'] + 1);
        } else {
            //今天没有签，看昨天
            $is_sign = 0;
            $yestoday = $this->getInsertData(session('user_id'));
            if ($yestoday['is_sign']) {
                //今天连续天数
                $days = $yestoday['days'] - 1;
                $will_getscore = $this->getTodayScores($yestoday['days']);
            } else {
                //今天第一天
                $days = 0;
                $will_getscore = $this->getTodayScores(1);

            }

            //已连续签到 可获取
            $score = $this->getTodayScores($days);
        }
		$data = [
            'is_sign' => $is_sign,
            'days' => $days,
            'score' => $score,
            'will_getscore' => $will_getscore,
        ];
		
        return json($data);
    }
    /**
     * 积分规则，返回连续签到的天数对应的积分
     *
     * @param int $days 当天应该得的分数
     * @return int 积分
     */
    protected function getTodayScores($days)
    {
        $score = 0;
        $scores = Db::name('user_signrule')->where("days <= $days")->order('days desc')->limit(1)->value('score');
        if ($scores) {
            $score = $scores;
        }

        return $score;
    }

    public function getsignrule()
    {
        $rules = Db::name('user_signrule')->order('days asc')->select();
        return json(array('code' => 200, 'msg' => $rules));
    }

    /**
     * 显示签到列表
     *
     * @param array  $signDays 某月签到的日期 array(1,2,3,4,5,12,13)
     * @param int $year    可选，年份
     * @param int $month   可选，月份
     * @return string 日期列表<li>1</li>....
     */
    public function showDays($signDays, $year = '', $month = '')
    {
        $time = time();
        $year = $year ? $year : date('Y', $time);
        $month = $month ? $month : date('m', $time);
        $daysTotal = date('t', mktime(0, 0, 0, $month, 1, $year));
        $now = date('Y-m-d', $time);
        $str = '';
        // $i=0;
        for ($j = 1; $j <= $daysTotal; $j++) {
            // $i++;
            $someDay = date('Y-m-d', strtotime("$year-$month-$j"));
            // 小于今天的日期样式
            if ($someDay <= $now) {
                // 当天日期样式 tdc = todayColor
                if ($someDay == $now) {
                    // 当天签到过的
                    if (in_array($j, $signDays)) {
                        $str .= '<li class="current fw tdc">' . $j . '</li>';
                    } else {
                        $str .= '<li class="today fw tdc">' . $j . '</li>';
                    }
                } else {
                    // 签到过的日期样式 current bfc = beforeColor , fw = font-weight
                    if (in_array($j, $signDays)) {
                        $str .= '<li class="current fw bfc">' . $j . '</li>';
                    } else {
                        $str .= '<li class="fw bfc">' . $j . '</li>';
                    }
                }
            } else {
                $str .= '<li>' . $j . '</li>';
            }
        }
        return $str;
    }
    /**
     * 获取当月签到的天数，与 $this->showDays() 配合使用
     * @return 当月签到日期 array(1,2,3,4,5,12,13)
     */
    public function getMonthSign()
    {
        $time = time();
        $year = date('Y', $time);
        $month = date('m', $time);
        $day = date("t", strtotime("$year-$month"));  
        $start_stime = strtotime("$year-$month-1 0:0:0") - 1;
        $end_stime = strtotime("$year-$month-$day 23:59:59") + 1;
        $list = Db::name('user_sign')->where("uid = {$this->uid} and stime > $start_stime and stime < $end_stime")->order('stime asc')->column('stime');
        //if(is_array($list)){
        foreach ($list as $key => $value) {
            $list[$key] = date('j', $value);
        }
        //}
        //return $list;
        return json_encode($list);
    }

}
