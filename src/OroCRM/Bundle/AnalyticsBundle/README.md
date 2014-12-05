# OroCRMAnalyticsBundle

Bundle provides analytics opportunities, tools for recalculation and analytics data visualization.
Can be apply to Channel entities.
The analytics data are available for build the segments and reports.
For available at now RFM metrics you can use this data for definition of columns, conditions and filters of your segments.

## RFM
Recency, Frequency, Monetary ([RFM](https://en.wikipedia.org/wiki/RFM_\(customer_value\))) Configured on Channel level.
Representing this metrics of entity must implement the interface
`RFMAwareInterface`, and also must use the trait
`RFMAwareTrait`. 
The trait extend entity to add opportunity to save and use analytics values.

## Metrics
You can implement additional analytics metric builders.
For it you need to specify your own fields to store metric data and need to use `AnalyticsAwareInterface` to class will known. 
In addition for it you need to add your own metric builder with `AnalyticsBuilderInterface`.

## RFM Ranking
Ranking defined in accordance with minimum and maximum values for RFM metric segment.
You can define separated bordering values for each of the indexes (pockets) for each of the metrics. 
Metric amount is not limited.
The analytics builder calculates which pocket have to apply to current value of entity and set it for each metric.

## Providers
Analytics providers add functionality for retrieve data of each metric of given entity and it data will be used to get accordance metric indexes by builder.
Provider can be implement for the each metric in your bundle and add to service container.
If there is not provider for some metrics - calculation will not work for it and it will be empty.
The providers connect by DI and must be added to the service container with `orocrm_analytics.builder.rfm` tag.
Each provider must implement `BuilderRFMProviderInterface`

## Command
Calculate is performed with **oro:cron:analytic:calculate** cron command.

You can use next parameters with the command:

* **--channel=#** (optional) - specify the Channel the data from which will be used to calculate RFM metrics
* **--ids=#** (optional) - object identifier for metrics will calculate. 
In the option you can specify some `ids` in one command to calculate all specified items.
(Example: `oro:cron:analytic:calculate --channel=1 --ids=1 --ids=2`). 
Please note that `channel` option is required to use `ids` option.
