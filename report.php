<?php
    require_once __DIR__ . '/vendor' . '/autoload.php';
    use Goutte\Client;
    
    ignore_user_abort();

    function report(string $username, string $password, array $reportdata){
        $client = new Client();
        $crawler = $client->request('GET', 'https://passport.ustc.edu.cn/login?service=https%3A%2F%2Fweixine.ustc.edu.cn%2F2020%2Fcaslogin',);
        
        $form = $crawler->filter('#login')->form();
        $form['username'] = $username;
        $form['password'] = $password;
        $crawler = $client->submit($form);    
    
        if($crawler->filter('#wrapper > div.header > ul.nav.navbar-nav.pull-right > li > span')->count()==0){
            return array('status'=>-1, 'name'=>$username, 'time'=>date('Y-m-d H:i:s'), 'note'=>'身份认证失败');
        }

        $form = $crawler->filter('#report-submit-btn')->form();
        $form->disableValidation();
        $form['now_address'] = $reportdata['now_address'];
        $form['gps_now_address'] = $reportdata['gps_now_address'];
        $form['now_province'] = $reportdata['now_province'];
        $form['gps_province'] = $reportdata['gps_province'];
        $form['now_city'] = $reportdata['now_city'];
        $form['gps_city'] = $reportdata['gps_city'];
        $form['now_detail'] = $reportdata['now_detail'];
        $form['is_inschool'] = $reportdata['is_inschool'];
        $form['body_condition'] = $reportdata['body_condition'];
        $form['body_condition_detail'] = $reportdata['body_condition_detail'];
        $form['now_status'] = $reportdata['now_status'];
        $form['now_status_detail'] = $reportdata['now_status_detail'];
        $form['has_fever'] = $reportdata['has_fever'];
        $form['last_touch_sars'] = $reportdata['last_touch_sars'];
        $form['last_touch_sars_date'] = $reportdata['last_touch_sars_date'];
        $form['last_touch_sars_detail'] = $reportdata['last_touch_sars_detail'];
        $form['other_detail'] = $reportdata['other_detail'];
        $crawler = $client->submit($form);
    
        $realname = $crawler->filter('#wrapper > div.header > ul.nav.navbar-nav.pull-right > li > span')->text();
        $datestr = $crawler->filter('#daliy-report > form > div > div.form-group.clearfix > span > strong')->text();
        $datestr = str_replace('*上次上报时间：','',$datestr);
        $datestr = str_replace('，请每日按时打卡','',$datestr);
        $reportdate = strtotime($datestr);
        if($crawler->filter('#wrapper > div.page-wrap > div.flash-message.mgb10.pd020 > p')->count()>0){
            $errnote = $crawler->filter('#wrapper > div.page-wrap > div.flash-message.mgb10.pd020 > p')->text();
        } else {
            $errnote = '未知错误';
        }

        if(time()-$reportdate<60){
            return array('status'=>1, 'name'=>$realname, 'time'=>$datestr, 'note'=>'--');
        } else {
            return array('status'=>-1, 'name'=>$realname, 'time'=>date('Y-m-d H:i:s'), 'note'=>$errnote);
        }
    }
    
    $username = $_GET['username'];
    $password = $_GET['password'];

    $result = report($username, $password, array(
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
    
    echo $result['time'].' | '.$result['name'].' | 状态：'.$result['status'].' | '.$result['note'];

?>