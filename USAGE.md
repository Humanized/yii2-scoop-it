# Yii2 Scoop.it - USAGE
## Assertion


## Command Line Interface

Once configured to specification, the local instance can synchronise data obtained from the remote Scoop.it account.

On first run, topic information requires importing. Following command should also run, should a configuration change occur on scoop.it side, specifically when topics are added or removed.

```
$ php yii scoopit/setup
```


Note: These commands are designed be run as cron-jobs for automated local system instance remote synchronisation.

Content gathered by the webcrawler, for a single topic registered on the local instance, is obtained on-demand using following command.

```
$ php php yii scoopit/data/sync <topic> <interval>
```

Content gathered by the webcrawer, for all topics registered on the local instance, is obtained on-demand using following command.


```
$ php php yii scoopit/data <interval>
```

Running synchronisation commands in anonymous mode provides read-access related to topical content which is explictely published or scooped. 

Running synchronisation commands in authenticated mode provides read-and-write access to topical content gathered by the webcrawler, which includes content which has not been previously scooped.  



## Graphical User Interface

