# OroCRMAnalyticsBundle

Bundle provides analytics opportunities, tools for recalculation and analytics data visualization.
Can be apply to Channel entities.
The analytics data are available for build the segments and reports.
For available at now RMF metrics you can use this data for definition of columns, conditions and filters of your segments.

## RFM
Recency, Frequency, Monetary ([RFM](https://en.wikipedia.org/wiki/RFM_\(customer_value\))) Configured on Channel level.
Representing this metrics of entity must implement the interface
`OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface`, and also must use the trait
`OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareTrait`. 
The trait extend entity to add opportunity to save and use analytics values.

## Metrics
`AnalyticsBuilderInterface` and `AnalyticsAwareInterface`

## RFM Ranking
Ranking defined in accordance with minimum and maximum values for RFM metric segment.
You can define separated bordering values for each of the indexes (pockets) for each of the metrics. 
Metric amount is not limited.
The analytics builder calculates which pocket have to apply to current value of entity and set it for each metric.


## Providers
Analytics providers provides functional for calculate data of each metric of given entity.
Provider have to implement for the each metric in your bundle and add to service container.
The providers connect by DI and must be added to the service container with `orocrm_analytics.builder.rfm` tag.
Each provider must implement `OroCRM\Bundle\AnalyticsBundle\BuilderRFMProviderInterface`


## Command
Calculate is performed with **oro:cron:analytic:calculate** cron command.

You can use next parameters with the command:

* **--channel=#** (optional) - specify the data channel the data from which will be used to calculate RFM metrics
* **--ids=#** (optional) - object identifier for metrics will calculate. 
In the option you can specify some ids in one command to calculate them all.
(Example: `oro:cron:analytic:calculate --channel=1 --ids=1 --ids=2`). 
You can not use ids without specified data channel.
