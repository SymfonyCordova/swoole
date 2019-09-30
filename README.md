# sfswoole

## 运行容器
    下拉镜像
        sudo docker pull registry.cn-hangzhou.aliyuncs.com/symfonycordova/sfswoole:v1
    运行镜像
        sudo docker run --name swoole -p 80:80 \
        -v ./docker-swoole/mysql.conf.d:/etc/mysql/custom.conf.d \
        -v ./docker-swoole/mysql.data:/var/lib/mysql \
        -v ./docker-swoole/mysql.log:/var/log/mysql \
        -v ./docker-swoole/nginx.conf.d:/etc/nginx/conf.d \
        -v ./docker-swoole/nginx.log:/var/log/nginx \
        -v ./docker-swoole/html:/var/www/html \
        -v ./docker-swoole/redis:/etc/redis -d symfonycordova/sfswoole:v1