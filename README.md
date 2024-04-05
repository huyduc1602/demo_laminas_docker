# DEMO LAMINAS DOCKER

This is a brief description of what this project is about.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

What things you need to install the software and how to install them.

### Installation

A step by step series of examples that tell you how to get a development environment running.

## Running the tests
Chạy task bất đồng bộ trong demo_bg/app/Notify/Task 
Tạo mới router trong `demo_bg/config/routes.php`

Chạy test như sau:

`curl -sk -X POST -H 'content-type: application/json' https://localhost:8080/api/test-mail -d '{"email": "abc","name": "123"}'`

Chạy lệnh sau để theo dõi log:

`docker-compose -f ./php.yml up -d`

`cd demo_bg`

`exec -it php_apache_debian bash`

` php ./laminas mezzio:swoole:start -d`
## Deployment

Add additional notes about how to deploy this on a live system.

## Built With

* [Laminas](https://getlaminas.org/) - The web framework used

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning.

## Authors

* **Your Name** - *Initial work* - [YourName](https://github.com/yourname)

See also the list of [contributors](https://github.com/yourname/yourproject/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone whose code was used
* Inspiration
* etc