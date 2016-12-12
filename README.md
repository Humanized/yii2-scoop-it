# Yii2 Scoop.it - README


[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

This package proposes a data model for importing and synchronising content locally managed through Scoop.it account

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
        'authorisationCallbackUri' => "path-to-website",
        // Scoop.it Consumer Key/Secret (mandatory for both anonymous & authenticated mode)
        'consumerKey' => '',
        'consumerSecret' => '',
        // Scoop.it Consumer Key/Secret (mandatory for both anonymous & authenticated mode)
        'token' => '',
        'tokenSecret' => '',
    ],
],
```