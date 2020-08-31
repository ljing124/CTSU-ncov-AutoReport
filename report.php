<?php
/**
 * CTSU健康打卡平台自动打卡
 * 
 * @author LJING
 * @version 2.0
 */
    require_once __DIR__ . '/vendor' . '/autoload.php';
    use Goutte\Client;
    
    ignore_user_abort();

    class AutoReport
    {
        private $username;
        private $password;
        private $reportdata;
        private $client;
        private $crawler;
        
        function __construct(string $username, string $password, array $reportdata){
            $this->username = $username;
            $this->password = $password;
            $this->reportdata = $reportdata;
            $this->client = new Client();
        }
        
        /**
         * 统一身份认证登录
         * @access public
         */
        public function login(){
            $this->crawler = $this->client->request('GET', 'https://passport.ustc.edu.cn/login?service=https%3A%2F%2Fweixine.ustc.edu.cn%2F2020%2Fcaslogin',);
            $form = $this->crawler->filter('#login')->form();
            $form['username'] = $this->username;
            $form['password'] = $this->password;
            $this->crawler = $this->client->submit($form);
            if($this->crawler->filter('#wrapper > div.header > ul.nav.navbar-nav.pull-right > li > span')->count()>0)
            {
                return true;
            } 
            else
            {
                return false;
            }
        }

        /**
         * 健康上报
         * @access public
         */
        public function report(){
            $form = $this->crawler->filter('#report-submit-btn')->form();
            $form->disableValidation();
            $form['now_address']            = $this->reportdata['now_address'];
            $form['gps_now_address']        = $this->reportdata['gps_now_address'];
            $form['now_province']           = $this->reportdata['now_province'];
            $form['gps_province']           = $this->reportdata['gps_province'];
            $form['now_city']               = $this->reportdata['now_city'];
            $form['gps_city']               = $this->reportdata['gps_city'];
            $form['now_detail']             = $this->reportdata['now_detail'];
            $form['is_inschool']            = $this->reportdata['is_inschool'];
            $form['body_condition']         = $this->reportdata['body_condition'];
            $form['body_condition_detail']  = $this->reportdata['body_condition_detail'];
            $form['now_status']             = $this->reportdata['now_status'];
            $form['now_status_detail']      = $this->reportdata['now_status_detail'];
            $form['has_fever']              = $this->reportdata['has_fever'];
            $form['last_touch_sars']        = $this->reportdata['last_touch_sars'];
            $form['last_touch_sars_date']   = $this->reportdata['last_touch_sars_date'];
            $form['last_touch_sars_detail'] = $this->reportdata['last_touch_sars_detail'];
            $form['other_detail']           = $this->reportdata['other_detail'];
            $this->crawler = $this->client->submit($form);
            $realname = $this->crawler->filter('#wrapper > div.header > ul.nav.navbar-nav.pull-right > li > span')->text();
            $datestr = $this->crawler->filter('#daliy-report > form > div > div.form-group.clearfix > span > strong')->text();
            $datestr = str_replace('*上次上报时间：','',$datestr);
            $datestr = str_replace('，请每日按时打卡','',$datestr);
            $reportdate = strtotime($datestr);
            
            if($this->crawler->filter('#wrapper > div.page-wrap > div.flash-message.mgb10.pd020 > p')->count()>0)
            {
                $errnote = $this->crawler->filter('#wrapper > div.page-wrap > div.flash-message.mgb10.pd020 > p')->text();
            } 
            else 
            {
                $errnote = '未知错误';
            }

            if(time()-$reportdate<60)
            {
                return array('status'=>true, 'name'=>$realname, 'time'=>$datestr, 'note'=>'--');
            } 
            else 
            {
                return array('status'=>false, 'name'=>$realname, 'time'=>date('Y-m-d H:i:s'), 'note'=>$errnote);
            }
        }
    }

    $username = ( isset($_GET['username']) ? $_GET['username'] : (isset($argv[1])?$argv[1]:'');
    $password = ( isset($_GET['password']) ? $_GET['password'] : (isset($argv[2])?$argv[2]:''))

    $username = $argv[1];
    $password = $argv[2];

    /* 在这里修改健康上报数据 */
    $reporter = new AutoReport($username, $password, array(
        'now_address'            => '1',
        'gps_now_address'        => '',
        'now_province'           => '340000',
        'gps_province'           => '',
        'now_city'               => '340100',
        'gps_city'               => '',
        'now_detail'             => '',
        'is_inschool'            => '2',
        'body_condition'         => '1',
        'body_condition_detail'  => '',
        'now_status'             => '1',
        'now_status_detail'      => '',
        'has_fever'              => '0',
        'last_touch_sars'        => '0',
        'last_touch_sars_date'   => '',
        'last_touch_sars_detail' => '',
        'other_detail'           => ''
    ));

    if($reporter->login())
    {
        $result = $reporter->report();
        if($result['status'])
        {
            echo '1';
        }
        else
        {
            echo '0';
        }
    }
    else
    {
        echo '-1';
    }

?>