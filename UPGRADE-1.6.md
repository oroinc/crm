UPGRADE FROM 1.5 to 1.6
=======================

####OroCRMCampaignBundle:
- Route orocrm_campaign_event_plot second parameter changed from campaignCode to campaign itself.
```
@Route("/plot/{period}/{campaign}", name="orocrm_campaign_event_plot")
```
