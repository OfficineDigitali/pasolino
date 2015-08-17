# Pasolino

# WARNING: THIS PROJECT IS STILL UNDER HEAVY DEVELOPMENT AND IN ANY WAY AFFORDABLE

**Pasolino** is intended to be a web interface for the _melt_ command line application, from the [MLT Framework](http://www.mltframework.org/), capable to render files generated with the video editor [Kdenlive](https://kdenlive.org/).

The aim of the project is to move the resources-angry rendering process on a dedicated computer, so to permit operators to continue their post-production work without get their own machines stuck on the final phase.

In the ideal setup **Pasolino** is installed on a computer on the same LAN, and able to access the same files included in the video to be rendered (see below).

## Requirements

* an RDBMS among those supported by [the Laravel framework](http://laravel.com/) (tested on MySQL)
* a webserver (tested on apache2)
* [Kdenlive](https://kdenlive.org/) (possibly at the same version on both production and rendering machines)
* [BeanStalk](https://kr.github.io/beanstalkd/)

## Installation

```
apt-get install apache2 php5 php5-cli php5-mysql curl git mysql-server kdenlive beanstalkd
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

git clone https://github.com/OfficineDigitali/pasolino
cd pasolino
composer install
cp .env.example .env
(edit the .env file with your own credentials for the database)
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan queue:listen --tries=1 --timeout=0
```

## Setup

Be sure the Laravel's queue listener is up and running. [Look here](http://laravel.com/docs/5.1/queues#running-the-queue-listener) for further details. The **Pasolino** scheduler of choice is BeanStalk, but every solution supported by Laravel and listed on that webpage is fine.

The complex part of the system is to share files to be included into the rendered video between the operator's computer and the rendering computer. Two different setups are possibile:

* use a shared disk on the network, mounted via SMB, NFS or whatever on both machines under the same mountpoint. In this way, the reference paths to the embedded files will be the same for all the involved computers
* on the operator's machine, enable a _pasolino_ user with proper permissions and authorized to log in via SSH with the public key provided on the "Configurations" panel. In this way, the application will be able to fetch the files directly from the operator's place and alter the .mlt file accordly before rendering it
