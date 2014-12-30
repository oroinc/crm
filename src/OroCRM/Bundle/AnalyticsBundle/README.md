# OroCRMAnalyticsBundle

The Bundle is aimed for analytical tools and means of the analysis results visualisation. 

The bundle includes:

- interfaces necessary to create and use a metric builder
- settings of the  analysis results visualization tools
- a command for recalculation of the analytical results

Out of the box, the bundle is used to implement calculation and realization of ([RFM](https://en.wikipedia.org/wiki/RFM_\(customer_value\))) metrics.

You can also implement additional metric builders.

## OroCRMAnalyticsBundle Interfaces

The following interfaces defined in the bundle can be implemented for the aims of analysis:

- `AnalyticsAwareInterface`: must be implemented for the entity to enable processing by a metrics builder
- `AnalyticsBuilderInterface`: must be implemented for a metrics builder to enable collection of its metrics by the Analitics builder
- `RFMAwareInterface` : must be implemented for an entity of a Channel to enable processing of its RFM metrics.  
- `RFMAwareTrait`: can be used for an entity of a Channel to extend it with custom fields to
  keep RFM scores.
- `RFMProviderInterface`: must be implemented for a provider function (added to the service container with `orocrm_analytics.builder.rfm` tag) for the provider to pass the metric values to the metric builder.
  
## OroCRMAnalyticsBundle Visualization Tool Settings

Settings of the tools designed for visualization of analytical results are kept in the bundle. 
Currently, the only tool implemented enables users to define a sets of threashold values, such that all the RFM metrics within a specific interval are assigned a specific score that will be saved in the dedicated custom fields. Representation of the form in the UI and the fields available can be configured from the Analytics Bundle.

## OroCRMAnalyticsBundle Recalculation Command 

Recalculation is performed with **oro:cron:analytic:calculate** cron command.

The following parameters can be used with the command: 

* **--channel=#** (optional) - specify the Channel the data from which will be used to calculate the metrics
* **--ids=#** (optional) - object identifier for the metrics to collect 
You can use the "Option" to specify `ids` and calculate all specified metrics with one command.
(Example: `oro:cron:analytic:calculate --channel=1 --ids=1 --ids=2`). 
Please note that 'ids' can be defined only if a `channel` is defined.


## RFM Metrics Collection and Processing with OroCRM

RFM are customer value assessment by Recency, Frequency and Monetary metrics.
The metrics are configured at a Channel level and can be used to define columns, conditions and filters of segments and to create reports.


In order to collect RFM values of an entity:

- Define the following settings for the entity in the channel bundle:
  
    - implement `RFMAwareInterface` interface : now RFM metrics are collected for the builder
    - implement `RFMAwareTrait` trait : now the entity has fields to save the scores into
    - define providers for each metric of the entity to be collected and implement `RFMProviderInterface` for each provider : now you have defined the functions to collect the metrics

- Add the providers to the service container with `orocrm_analytics.builder.rfm` tag: now they can be used by the system

As soon as `RFMAwareInterface` is implemented, the  RFM Segment Configuration section will appear in the “Edit” form of the Channel. 
Define the intervals for the scores for each of the metrics available.

- Run the calculation command for the channel and all the defined RFM metrics will be collected and scores will be assigned to the entity records. 

## Custom Metric Builders

You can implement additional analytical metric builders. To do so:

- Define the following settings for the entity in the channel bundle:
  
    - specify the custom fields of an entity that will be used to store the metrics data
    - implement `AnalyticsAwareInterface`: now metrics of the entity will be collected
  
- Add the metric builder to the AnalyticsBundle and implement `AnalyticsBuilderInterface` for it.
