# Yii2 Scoop.it - CONFIG

## Minimal Module Configuration

As defined in the [README](README.md)-file, module configuration is setup in the desired config file.

```php
'modules' => [
    'scoopit' => [
        'class' => 'humanized\scoopit\ScoopIt',
        'saveSuggestions'=>true,
    ],
],
```


## Additional Import Settings

### saveSuggestions (boolean)

When true, a local copy is stored of all curable (unpublished) posts.

Runlevel: Authenticated

Default: false


### enableRmTag (boolean)

When true, published posts when be removed from the local topic when tagged using #rm 

Runlevel: Anonymous

Default: false

### remoteLifetime (integer)

Amount of hours that a post tagged with #rm tag should remain available before remote deletion. This setting is useful for allowing multiple local deployments to synchronise to a single remote system. When set to 0, the post is removed remotely immediately after being processed by the corresponding local deployment. 

Runlevel: Authenticated

Default: 0

### autoscoopSuffix (string)

To enable automated content publication for a topic, a user-specified suffix must be provided identifying topic pages subject to the mechanism. For this, it is required that the topic is maintained on the remote system in two versions denoted by the naming convention as exemplified next.

Suppose content involving a topic called health is subject to manual curation. Then, a second topic called health-auto is used to maintains a list of trusted sources which are subject to be automatically publication on the health topic page, and this without any manual user invention. In this case, as by default, the autoScoopSuffix is set thus to '-auto'. 

Runlevel: Authenticated

Default: '-auto'

### enableAutoTag (boolean)

When true, and autoscoopSuffix is set, automatically published posts are tagged remotely with #auto tag. 

Runlevel: Authenticated

Default: false


### processorClass (string)



## Display Settings

