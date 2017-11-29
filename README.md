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

## Setup Scoop.it Account Credentials

Account credentials are stored as local parameters, e.g. storing them in common/config/params-local:
Note, these credentials are personal and should never be stored on a public software repository!

```php
'params' => [
    'scoopit' =>
    [
        // Scoop.it API account base url here (suffixed with /api/1)
        'remoteUri' => 'https://acme-corp.scoop.it/api/1/',
        // Scoop.it Consumer Key/Secret (allows anonymous mode and mandatory for authenticated mode)
        'consumerKey' => '',
        'consumerSecret' => '',
        // URL root of local website instance, used for three-tier authententication callback
         'authorisationCallbackUri' => "path-to-website",
        // Scoop.it Consumer Key/Secret (mandatory for authenticated mode)
        'token' => '',
        'tokenSecret' => '',
    ],
],
```

The OAuth consumer key-secret combination can be obtained, after account login, on the [Scoop.it Account Application Management Page](https://www.scoop.it/dev/apps)



