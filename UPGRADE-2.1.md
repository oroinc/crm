UPGRADE FROM 2.0 to 2.1
========================

### Oro Marketing Bundles
####CampaignBundle:
- Method `getCampaignsByCloseRevenue` was removed from `\Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository`
  Please use `\Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider::getCampaignsByCloseRevenueData` instead 
