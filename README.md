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

Add following lines to the configuration file for minimum integration:

```php
'modules' => [
    'news' => [
        'class' => 'humanized\scoopit\ScoopIt',
    ],
],
```
Further information about module configuration can be found on the CONFIG page of this repository.

### Run Migrations 

```bash
$ php yii migrate/up --migrationPath=@vendor/humanized/yii2-scoop-it/migrations
```

## Setup Scoop.it Credentials

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
        // URL root of local website instance, used for three-legged authententication callback
         'authorisationCallbackUri' => "path-to-website",
        // Scoop.it Consumer Key/Secret (mandatory for authenticated mode)
        'token' => '',
        'tokenSecret' => '',
    ],
],
```

The OAuth consumer key-secret combination can be obtained, after Scoop.it account login, on the [Scoop.it Account Application Management Page](https://www.scoop.it/dev/apps)

Configuration of these details allow the client to interact with the remote Scoop.it account in the restricted anonymous mode, offering (partial) read-only access to the remote account.

To enable authenticated mode which provides maximal access to the remote account , a three-legged OAuth authentication procedure is followed. 

For this, an appropriate value is set for the authorisationCallbackUri, which must be set to the root path of the local instance. For example, should the local instance deployment have routes defined as https://example.com/path/to/webapp/module/controller/view, then the authorisationCallbackUri is set to https://example.com/path/to/webapp/,

Once configured, the process for obtaining token and token secret values can be described as followed

*Leg #1*


From commandline, run following command from the application root.

```bash
$ php yii news/oauth
```
The system subsequently displays an external URL to the remote Scoop.it account, and prompts the user for a verifier.. 

*Leg #2*


To obtain the authorisation verfier, follow the external link in a graphical browser, authorising application access by  the local application instance to the remote Scoop.it account. Upon authorisation, a redirection occurs to the local instance, using the authorisationCallbackUrl setup earlier, displaying a (temporary) token and the verifier requested by the prompt.    

*Leg #3*


Providing the verfier obtained in the previous leg in the prompt obtained in the first leg subsequently displays the token and token secret values, for local configuration setup


For verification purposes, run following command after setup is complete.

```bash
$ php yii news/test
```


