# sfswoole

## 运行环境
    1.sudo docker run -ti --name sfswoole -d ubuntu:16.04 bash
    2.sudo docker exec -it sfswoole bash
    3.apt-get update
    4.apt-get install vim
    5.更换阿里yuan
        mv /etc/apt/sources.list /etc/apt/sources.list.bak
        vim /etc/apt/sources.list
        
        deb http://mirrors.aliyun.com/ubuntu/ bionic main restricted universe multiverse
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic main restricted universe multiverse
        
        deb http://mirrors.aliyun.com/ubuntu/ bionic-security main restricted universe multiverse
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-security main restricted universe multiverse
        
        deb http://mirrors.aliyun.com/ubuntu/ bionic-updates main restricted universe multiverse
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-updates main restricted universe multiverse
        
        deb http://mirrors.aliyun.com/ubuntu/ bionic-proposed main restricted universe multiverse
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-proposed main restricted universe multiverse
        
        deb http://mirrors.aliyun.com/ubuntu/ bionic-backports main restricted universe multiverse
        deb-src http://mirrors.aliyun.com/ubuntu/ bionic-backports main restricted universe multiverse

        sudo apt-get update
    6.安装 nginx
        apt-get install nginx
            配置文件都在/etc/nginx下，并且每个虚拟主机已经安排在了/etc/nginx/sites-available下
            程序文件在/usr/sbin/nginx
            日志放在了/var/log/nginx中
            并已经在/etc/init.d/下创建了启动脚本nginx
            默认的虚拟主机的目录设置在了/var/www/nginx-default (有的版本默认的虚拟主机的目录设置在了/var/www, 请参考/etc/nginx/sites-available里的配置)
        启动 service nginx start
    7.  安装php和php扩展
            sudo apt-get install software-properties-common
            sudo add-apt-repository ppa:ondrej/php
            sudo apt-get update
            sudo apt-get install -y php7.3
            apt-get install php7.3-gd
    8. 安装swoole
        安装 pecl
            相比于phpize方式安装,pecl方式安装更为简便,可省去手动添加到php.ini的环节
            sudo apt-get install php7.3-dev php7.3-pear autoconf automake libtool  -y --allow-unauthenticated
        安装swoole  
            sudo pecl install swoole
        配置php.ini
            php -i | grep php.ini
            vim /etc/php/7.3/cli/php.ini 添加 extension=swoole.so
        ps -ajft 查看进程
    9 安装mysql
        sudo apt-get install mysql-server
        service mysql start
        mysql -uroot -proot
            grant all on *.* to root@'%' identified by 'root' with grant option;
            flush privileges;
        sudo vi /etc/mysql/mysql.conf.d/mysqld.cnf
            注释掉bind-address = 127.0.0.1
        service mysql restart
    10 安装redis
        sudo apt-get install redis-server
    11 安装composer
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
    12.将容器生成镜像
        sudo docker commit -m "nginx+phpcli+swoole+redis+mysql" -a "symfonycordova" 2fd2b9f3abc9 sfswoole
        ~~~~sudo docker build -t symfonycordova/sfswoole:v1 .~~~~
    13.需要将容器挂在的目录
        VOLUME /etc/nginx/sites-enabled
        VOLUME /var/log/nginx
        VOLUME /var/www/html
        
        vim my.cnf
            结尾出加上 !includedir /etc/mysql/custom.conf.d/
        VOLUME /etc/mysql/custom.conf.d
        VOLUME /var/lib/mysql
    14.需要运行
        sudo docker run --name swoole -p 80:80 \
        -v /home/zler/桌面/docker/sfswoole/mysql.conf.d:/etc/mysql/custom.conf.d \
        -v /home/zler/桌面/docker/sfswoole/mysql.data:/var/lib/mysql \
        -v /home/zler/桌面/docker/sfswoole/mysql.log:/var/log/mysql \
        -v /home/zler/桌面/docker/sfswoole/nginx.conf.d:/etc/nginx/conf.d \
        -v /home/zler/桌面/docker/sfswoole/nginx.log:/var/log/nginx \
        -v /home/zler/桌面/docker/sfswoole/html:/var/www/html \
        -v /home/zler/桌面/docker/sfswoole/redis:/etc/redis -d symfonycordova/sfswoole:v1