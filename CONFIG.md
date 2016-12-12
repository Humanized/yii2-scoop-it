# Yii2 Maintenance - CONFIG

## Redirection Behavior

### Minimal Configuration

As defined in the [README](README.md)-file, minimal behavior configuration is setup in the root of the configuration file that effects the web application to be placed under maintenance. 

```php
return [
    'id' => 'application-name',
    ...
    'as beforeAction'=>[ 
      'class'=>'humanized\maintenance\components\RedirectionBehavior',
      //Add custom configuration options here
      ...
    ]
    ...
],
```


### -force (boolean)

Bypasses standard maintenance-mode status check, forcing it to always evaluate to true.

Default: true


### -bypassPermission (string)

Permission to be evaluated using Yii::$app->user->can() - Bypasses redirection when evaluating to true

Default: null

### -bypassRedirection (boolean|callable)

Bypasses redirection when evaluating to true

When using a callback, use a function without parameters which returns a boolean value

Default: false

### -whitelistLoginUrl (boolean)

Bypass redirection for route setup by loginUrl through the Yii::$app->user component

Default: true

### -whitelistErrorAction (boolean)

Bypass redirection for route setup by errorAction through the Yii::$app->errorHandler component

Default: true

### -whitelist (array[string])

Array containing individual routes (e.g. /path/to/route) which bypass the redirection

Default: []
