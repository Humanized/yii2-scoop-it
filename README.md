# yii2-scoopit
Scoop it

[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Various interfaces dealing with the Scoop.it API

## Features

## Dependencies

This package relies on Guzzle version 6.x.

## Installation

### Install Using Composer

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require humanized/yii2-scoop-it "dev-master"
```

or add

```
"humanized/yii2-scoop-it": "dev-master"
```

to the ```require``` section of your `composer.json` file.


### Add Module to Configuration

Add following lines to the configuration file:

```php
'modules' => [
    'news' => [
        'class' => 'humanized\scoopit\ScoopIt',
    ],
],
```

### Run Migrations 

```bash
$ php yii migrate/up --migrationPath=@vendor/humanized/yii2-scoop-it/migrations
```

## Module Configuration Options

Ensure the following lines are accessible as local parameters:

```php
'params' => [
    'scoopit' =>
    [
        // Add the base URI used to connect to here
        'remoteUri' => 'https://acme-corp.scoop.it/api/1/',
        // Add your own Consumer Key here
        'remoteConsumerKey' => '',
        // Add your own Secret Key here
        'remoteSecretKey' => '',
    ],
],
```