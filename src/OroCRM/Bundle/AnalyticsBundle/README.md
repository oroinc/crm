# OroCRMAnalyticsBundle

This Bundle provides analytics opportunities for OroCRM. 
It provide instruments are allowing to add and to use recalculation and visualisation the analytics data. 
Can be apply to data of data channel import. 
And applicable to chosen entity of the imported data.
The analytics data are available for build the segments and reports. 
You can use this data for definition of columns, conditions and filters of your segments.


## Metrics
At the now we have implemented three metrics - Recency, Frequency, Monetary ([RFM](https://en.wikipedia.org/wiki/RFM_\(customer_value\))). 
It can be set to chosen data channel.
Representing this metrics of entity must implement the interface
_OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface_, and also must use the trait
_OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareTrait_. 
The trait extend entity to add opportunity to save and use analytics values.


## Ranking
Ranking defined in accordance with set values for each of the 3 RFM metrics. 
You can define separated bordering values for each of the indexes (pockets) for each of the metrics.
For each metric can be defined any amount of pockets. 
The analytics function calculates which pocket have to apply to current value of entity and set it for each metric.


## Providers
Analytics providers provides functional for calculate data of each metric of given entity.
Provider have to implement for the each metric in your bundle and add to service container.
The providers connect by DI and must be added to the service container with _orocrm_analytics.builder.rfm_ tag.
Each provider must implement _OroCRM\Bundle\AnalyticsBundle\BuilderRFMProviderInterface_


## Command
Calculate is performed with **oro:cron:analytic:calculate** cron command.

You can use next parameters with the command:

* **--channel=#** (optional) - specify the data channel the data from which will be used to calculate RFM metrics
* **--ids=#** (optional) - object identifier for metrics will calculate. 
In the option you can specify some ids in one command to calculate them all.
(Example: `oro:cron:analytic:calculate --channel=1 --ids=1 --ids=2`). 
You can not use ids without specified data channel.
