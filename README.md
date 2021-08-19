# CTSU健康打卡平台自动打卡脚本

![School](https://img.shields.io/badge/School-CTSU-blue)
![Language](https://img.shields.io/badge/php-%3E%3D7.3.0-blue)
![Github Action](https://github.com/ljing124/CTSU-ncov-AutoReport/workflows/PHP%20report/badge.svg)

## 介绍

本打卡脚本基于PHP开发，仅供学习交流使用，作者对于使用本脚本导致的问题不承担相应责任。

agile分支使用学校健康打卡平台记录的上报数据进行每日打卡，不再需要在代码中维护信息，修改上报数据需要在学校健康打卡平台手动填写进行一次上报。

如果需要使用脚本准确填写上报数据并在每次打卡覆盖学校健康打卡平台的填写内容，请切换至master分支

## 更新

2021-08-19 新分支建立，使用学校健康打卡平台预填充数据打卡。

2021-08-18 同步学校打卡平台更新，增加了紧急联系人相关填写项。

2021-08-17 同步学校打卡平台更新增加了位置县区代码、14天是否旅居中高风险地区等数据项，增加了先研院与国金院2个校区。

2021-07-30 云打卡增加了打卡失败的邮件提醒。

2021-03-20 东区校园网节点下线。

此前fork的项目可能需要fetch upstream同步主分支到最新版本以正常运行。

## 使用

### 通过自建PHP运行环境

0. 搭建PHP运行环境，PHP版本＞7.3，无需MYSQL，也可以使用docker部署。(打卡脚本资源占用较少，可以在openwrt、群晖等轻量环境下运行)

1. 将本仓库clone到本地并上传至您的PHP运行环境。也可以仅上传report.php文件，通过composer安装依赖。
```
composer require fabpot/goutte
```

2. 通过计划任务工具定时访问report.php页面，linux环境下使用corntab，win环境下使用任务计划程序，也可以使用第三方云监控定时访问URI。
```
yourhost/report.php?username=USERNAME&password=PASSWORD
```

### 通过Github Action

1. 将本仓库fork到自己的github。
   
2. 修改.github/workflows/php.yml文件中第7行的schedule为自己需要打卡的时间。如果您不知道如何设置时间，可以先了解 [POSIX cron](https://pubs.opengroup.org/onlinepubs/9699919799/utilities/crontab.html#tag_20_25_07) 表达式。注意这里使用的是**UTC时间**，即北京时间减去8小时。
   
3. 选择Settings选项卡点选左侧secret，创建名为USERNAME和PASSWORD的secret，值分别为自己统一身份认证的账号(学号)和密码。再创建名为CONTACTNAME、CONTACTRELA、CONTACTPHONE的secret，值为紧急联系人的姓名、与本人关系、联系电话。
![](imgs/img-secrets.png)

4. 选择Actions选项卡启用自己仓库的Action，当push到master分支或计划时间时将会自动运行report.php打卡，在Actions界面选择PHP report -> build可以查看打卡结果。
![](imgs/img-actions.png)
   
5. 在Github个人设置页面开启Email通知(可选)。
   
### 通过云平台

您也可以使用由本仓库作者提供的带图形界面的云打卡平台(基于master分支)

[阿里云教育网节点](http://auto.biqiqi.com.cn) *▶正常维护中*

~~[西区校园网节点](http://lxk.b77.tech:10888)~~ *■已转交同学维护*

~~[东区校园网节点](http://mxh.b77.tech)~~ *■已停止维护*

![](imgs/img-demo.png)

注意：
+ 作者对该云平台运行的稳定性不作任何保证，该云平台随时可能停止服务。(若停止服务，已注册使用的用户将收到邮件通知)
+ 云平台需要存储您的统一身份认证账号和密码(非公开)，请确认知悉这一点再使用。

## 相关项目

其他开发者的一个基于python3的打卡脚本[URC-ncov-AutoReport](https://github.com/Violin9906/URC-ncov-AutoReport.git)
   
## TODO LIST

1. GitHub Action 打卡失败时进行重试
