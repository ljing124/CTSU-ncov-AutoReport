<?php
/**
 * CTSU健康打卡平台自动打卡
 * 
 * @author LJING
 * @version 3.1
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
        
        function __construct(string $username, string $password){
            $this->username = $username;
            $this->password = $password;
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
            $form = $this->crawler->filter('#report-submit-btn-a24');
            if($form) $form = $form->form();
            else return array('status'=>false, 'name'=>$this->username, 'time'=>date('Y-m-d H:i:s'), 'note'=>'表单定位失败');
            $form->disableValidation();
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

    $reporter = new AutoReport($username, $password);

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
