@echo off
docker exec -t php_apache_debian tail -f -n 20 /var/www/projects/demo_bg/logs/kafka-consumer.log