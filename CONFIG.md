# Yii2 Scoop.it - CONFIG

## Minimal Module Configuration

As defined in the [README](README.md)-file, minimal module configuration is setup in the desired config file.

```php
'modules' => [
    'scoopit' => [
        'class' => 'humanized\scoopit\ScoopIt',
    ],
],
```

## Additional Import Configuration Options

### -saveSuggestions (boolean)

When true, a local copy is stored of all curable (unpublished) posts.

Runlevel: Authenticated

Default: false


### -enableRmTag (boolean)

When true, published posts when be removed from the local topic when tagged using #rm 

Runlevel: Anonymous

Default: false

### -remoteLifetime (integer)

Amount of hours that a post tagged with #rm tag should remain available before remote deletion. This setting is useful for allowing multiple local deployments to synchronise to a single remote system. When set to 0, the post is removed remotely immediately after being processed by the corresponding local deployment. 

Runlevel: Authenticated

Default: 0

### -enableAutoTag (boolean)

When true, automatically published posts are tagged remotely with #auto tag. 

Runlevel: Authenticated

Default: false
