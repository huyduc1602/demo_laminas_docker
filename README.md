# DEMO LAMINAS DOCKER

This is a brief description of what this project is about.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

What things you need to install the software and how to install them.

### Installation

A step by step series of examples that tell you how to get a development environment running.

## Running the tests
Chạy task bất đồng bộ sử dụng mezzio-swoole

Demo tạo 1 task trong swoole
```
- Tạo file app\UploadFile\Config\module.config.php
```
```
- Tạo file app\UploadFile\Task\TestUploadListener.php extends \GrootSwoole\BaseTaskEventListener
```
```
- Tạo file app\UploadFile\Task\TestUploadTask.php extends \GrootSwoole\BaseTaskEvent
```
```
- Tạo app\UploadFile\ConfigProvider.php
```

Thêm dòng sau vào file  `config\config.php`:

`UploadFile\ConfigProvider::class`

Thêm dòng sau vào phần `autoload` của file `composer.json` :

`"UploadFile\\" : "app/UploadFile/"`

Sau đó chạy lệnh

 `php \var\www\projects\laminas_mvc_core\composer.phar dump`

Restart supervisor

`supervisorctl status` (kiểm tra tên program)

`supervisorctl restart demo_task`


Bắt đầu swoole

`docker exec -it php_apache_debian bash`

`cd demo_bg`

` php ./laminas mezzio:swoole:start`

Kiểm tra các process đang chạy

`ps aux`

Chạy test như sau:

` curl -sk -X POST -H 'content-type: application/json' https://localhost:8080/api/test-upload -d '{"file": "abc","size": "123"}'  > ./debug.html`

Chạy lệnh sau để theo dõi log:

`docker exec -t php_apache_debian tail -f -n 20 /var/www/projects/demo_bg/logs/kafka-consumer.log`

## Deployment

Add additional notes about how to deploy this on a live system.

## Built With

* [Laminas](https://getlaminas.org/) - The web framework used

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning.

## Authors

**Hoang Huy Duc** - *Groot* - [HoangHuyDuc](https://github.com/huyduc1602)

See also the list of [contributors](https://github.com/yourname/yourproject/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone whose code was used
* Inspiration
* etc