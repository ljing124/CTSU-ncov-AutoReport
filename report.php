<?php
/**
 * CTSU健康打卡平台自动打卡
 * 
 * @author LJING
 * @version 3.0
 */
    require_once __DIR__ . '/vendor' . '/autoload.php';
    use Goutte\Client;
    
    ignore_user_abort();

    include_once './captcha/ustcv2.php';

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
            $this->crawler = $this->client->request('GET', 'https://passport.ustc.edu.cn/login?service=https%3A%2F%2Fweixine.ustc.edu.cn%2F2020%2Fcaslogin');
            $form = $this->crawler->filter('#login')->form();
            $form->disableValidation();
            $form['username'] = $this->username;
            $form['password'] = $this->password;
            if($form->getValues()['showCode']==1){
                $this->client->request('GET', 'https://passport.ustc.edu.cn/validatecode.jsp?type=login');
                $captcha = new ustccaptcha($this->client->getResponse()->getContent());
                $ltnode = $form->getFormNode()->childNodes->item(1)->cloneNode(True);
                $ltnode->setAttribute('name', 'LT');
                $form->addField($ltnode);
                $form['LT'] = $captcha->recognize();
            }
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
            $form['now_country']            = $this->reportdata['now_country'];
            $form['gps_country']            = $this->reportdata['gps_country'];
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
            $form['is_danger']              = $this->reportdata['is_danger'];
            $form['is_goto_danger']         = $this->reportdata['is_goto_danger'];
            $form['jinji_lxr']              = $this->reportdata['jinji_lxr'];
            $form['jinji_guanxi']           = $this->reportdata['jinji_guanxi'];
            $form['jiji_mobile']            = $this->reportdata['jinji_mobile'];
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

    /* 通过GET传参或命令传参获取secrects值，使用自建环境时可选择直接在此处修改 */
    $username = ( $_GET['username'] ?? ($argv[1]??''));
    $password = ( $_GET['password'] ?? ($argv[2]??''));
    $contact_name = ( $_GET['contactname'] ?? ($argv[3]??''));
    $contact_rela = ( $_GET['contactrela'] ?? ($argv[4]??''));
    $contact_phone = ( $_GET['contactphone'] ?? ($argv[5]??''));

    /* 在这里修改健康上报数据 */
    $reporter = new AutoReport($username, $password, array(
        'now_address'            => '1',
        'gps_now_address'        => '',
        'now_province'           => '340000',         //省份行政区划代码
        'gps_province'           => '',
        'now_city'               => '340100',         //城市行政区划代码
        'gps_city'               => '',
        'now_country'            => '340111',         //县区行政区划代码
        'gps_country'            => '',
        'now_detail'             => '',
        'is_inschool'            => '2',              //是否在校 东区:2 南区:3 中区:4 北区:5 西区:6 先研院:7 国金院:8 校外:0
        'body_condition'         => '1',              //身体状况 正常:1 疑似:2 确诊:3 其他:4
        'body_condition_detail'  => '',
        'now_status'             => '1',              //当前状态 正常在校:1 正常在家:2 居家留观:3 集中留观:4 住院治疗:5 其他:6
        'now_status_detail'      => '',
        'has_fever'              => '0',              //发热症状 无:0 有:1 
        'last_touch_sars'        => '0',
        'last_touch_sars_date'   => '',               //是否接触确诊或疑似病例 无:0 有:1
        'last_touch_sars_detail' => '',
        'is_danger'              => '0',              //当前居住地是否为疫情中高风险地区 否:0 是:1
        'is_goto_danger'         => '0',              //14天内是否有疫情中高风险地区旅居史 否:0 是:1
        'jinji_lxr'              => $contact_name,    //自建环境可在此处修改紧急联系人姓名，使用Github Action时请使用secrects传入
        'jinji_guanxi'           => $contact_rela,    //自建环境可在此处修改紧急联系人关系，使用Github Action时请使用secrects传入
        'jinji_mobile'           => $contact_phone,   //自建环境可在此处修改紧急联系人电话，使用Github Action时请使用secrects传入
        'other_detail'           => ''
    ));

    if($reporter->login())
    {
        $result = $reporter->report();
        if($result['status'])
        {
            echo '打卡成功 | '.$result['time'].' | '.$result['name'].' | '.$result['note'];
        }
        else
        {
            echo '打卡失败 | '.$result['time'].' | '.$result['name'].' | '.$result['note'];
            exit(-1);
        }
    }
    else
    {
        echo '登录失败 | '.date('Y-m-d H:i:s').' | '.$username;
        exit(-1);
    }

?>
