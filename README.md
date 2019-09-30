# sfswoole

## 运行容器
    下拉项目
        git clone https://github.com/SymfonyCordova/swoole.git
    下拉镜像
        sudo docker pull registry.cn-hangzhou.aliyuncs.com/symfonycordova/sfswoole:v1
    进入项目目录
        cd swoole
    运行镜像
        sudo docker run --name swoole -p 80:80 -p 443:443 -p 3306:3306 -p 6379:6379 \
        -v $(pwd)/docker-swoole/mysql.conf.d:/etc/mysql/custom.conf.d \
        -v $(pwd)/docker-swoole/mysql.data:/var/lib/mysql \
        -v $(pwd)/docker-swoole/mysql.log:/var/log/mysql \
        -v $(pwd)/docker-swoole/nginx.conf.d:/etc/nginx/conf.d \
        -v $(pwd)/docker-swoole/nginx.log:/var/log/nginx \
        -v $(pwd)/docker-swoole/html:/var/www/html \
        -v $(pwd)/docker-swoole/redis:/etc/redis -d registry.cn-hangzhou.aliyuncs.com/symfonycordova/sfswoole:v1