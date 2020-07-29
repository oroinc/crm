- [ContactUsBundle](#contactusbundle)
- [MagentoBundle](#magentobundle)
- [MarketingCRM](#marketingcrm)

ContactUsBundle
---------------
* The `ContactReasonRepository::getExistedContactReasonsQB`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bundle/ContactUsBundle/Entity/Repository/ContactReasonRepository.php#L31 "Oro\Bundle\ContactUsBundle\Entity\Repository\ContactReasonRepository::getExistedContactReasonsQB")</sup> method was removed.
* The `ContactReasonRepository::getContactReason($id)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bundle/ContactUsBundle/Entity/Repository/ContactReasonRepository.php#L16 "Oro\Bundle\ContactUsBundle\Entity\Repository\ContactReasonRepository")</sup> method was changed to `ContactReasonRepository::getContactReason(int $id)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-beta/src/Oro/Bundle/ContactUsBundle/Entity/Repository/ContactReasonRepository.php#L21 "Oro\Bundle\ContactUsBundle\Entity\Repository\ContactReasonRepository")</sup>
* The following methods in class `ContactRequestController`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-beta/src/Oro/Bundle/ContactUsBundle/Controller/ContactRequestController.php#L79 "Oro\Bundle\ContactUsBundle\Controller\ContactRequestController")</sup> were changed:
  > - `updateAction(ContactRequest $contactRequest)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bundle/ContactUsBundle/Controller/ContactRequestController.php#L76 "Oro\Bundle\ContactUsBundle\Controller\ContactRequestController")</sup>
  > - `updateAction(ContactRequest $contactRequest, UpdateHandlerFacade $formHandler, TranslatorInterface $translator)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-beta/src/Oro/Bundle/ContactUsBundle/Controller/ContactRequestController.php#L79 "Oro\Bundle\ContactUsBundle\Controller\ContactRequestController")</sup>

  > - `update(ContactRequest $contactRequest)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bundle/ContactUsBundle/Controller/ContactRequestController.php#L122 "Oro\Bundle\ContactUsBundle\Controller\ContactRequestController")</sup>
  > - `update(ContactRequest $contactRequest, UpdateHandlerFacade $formHandler, TranslatorInterface $translator)`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-beta/src/Oro/Bundle/ContactUsBundle/Controller/ContactRequestController.php#L128 "Oro\Bundle\ContactUsBundle\Controller\ContactRequestController")</sup>


MagentoBundle
-------------
* The bundle was removed

MarketingCRM
------------
* The following classes were removed:
   - `OroMarketingCRMBridgeBundle`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/OroMarketingCRMBridgeBundle.php#L11 "Oro\Bridge\MarketingCRM\OroMarketingCRMBridgeBundle")</sup>
   - `AbstractPrecalculatedVisitProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Provider/AbstractPrecalculatedVisitProvider.php#L16 "Oro\Bridge\MarketingCRM\Provider\AbstractPrecalculatedVisitProvider")</sup>
   - `PrecalculatedTrackingVisitProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Provider/PrecalculatedTrackingVisitProvider.php#L11 "Oro\Bridge\MarketingCRM\Provider\PrecalculatedTrackingVisitProvider")</sup>
   - `PrecalculatedWebsiteVisitProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Provider/PrecalculatedWebsiteVisitProvider.php#L10 "Oro\Bridge\MarketingCRM\Provider\PrecalculatedWebsiteVisitProvider")</sup>
   - `TrackingCustomerIdentification`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Provider/TrackingCustomerIdentification.php#L18 "Oro\Bridge\MarketingCRM\Provider\TrackingCustomerIdentification")</sup>
   - `TrackingVisitEventProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Provider/TrackingVisitEventProvider.php#L13 "Oro\Bridge\MarketingCRM\Provider\TrackingVisitEventProvider")</sup>
   - `TrackingVisitProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Provider/TrackingVisitProvider.php#L21 "Oro\Bridge\MarketingCRM\Provider\TrackingVisitProvider")</sup>
   - `WebsiteVisitProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Provider/WebsiteVisitProvider.php#L16 "Oro\Bridge\MarketingCRM\Provider\WebsiteVisitProvider")</sup>
   - `OroMarketingCRMBridgeBundleInstaller`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Schema/OroMarketingCRMBridgeBundleInstaller.php#L19 "Oro\Bridge\MarketingCRM\Migrations\Schema\OroMarketingCRMBridgeBundleInstaller")</sup>
   - `UpdateChannelFormType`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Schema/v1_1/UpdateChannelFormType.php#L12 "Oro\Bridge\MarketingCRM\Migrations\Schema\v1_1\UpdateChannelFormType")</sup>
   - `OroChannelBundleAssociation`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Schema/v1_0/OroChannelBundleAssociation.php#L14 "Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroChannelBundleAssociation")</sup>
   - `OroMarketingCRMBridgeBundle`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Schema/v1_0/OroMarketingCRMBridgeBundle.php#L13 "Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroMarketingCRMBridgeBundle")</sup>
   - `LoadCampaignPerformanceReport`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/ORM/LoadCampaignPerformanceReport.php#L18 "Oro\Bridge\MarketingCRM\Migrations\Data\ORM\LoadCampaignPerformanceReport")</sup>
   - `LoadTrackingRolesData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/ORM/LoadTrackingRolesData.php#L17 "Oro\Bridge\MarketingCRM\Migrations\Data\ORM\LoadTrackingRolesData")</sup>
   - `LoadCampaignData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/Demo/ORM/LoadCampaignData.php#L19 "Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadCampaignData")</sup>
   - `LoadCampaignEmailData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/Demo/ORM/LoadCampaignEmailData.php#L12 "Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadCampaignEmailData")</sup>
   - `LoadMarketingActivityData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/Demo/ORM/LoadMarketingActivityData.php#L15 "Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadMarketingActivityData")</sup>
   - `LoadMarketingListData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/Demo/ORM/LoadMarketingListData.php#L12 "Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadMarketingListData")</sup>
   - `LoadSegmentsData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/Demo/ORM/LoadSegmentsData.php#L13 "Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadSegmentsData")</sup>
   - `LoadTrackingWebsiteData`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Migrations/Data/Demo/ORM/LoadTrackingWebsiteData.php#L22 "Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM\LoadTrackingWebsiteData")</sup>
   - `LoadClassMetadataListener`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/EventListener/LoadClassMetadataListener.php#L10 "Oro\Bridge\MarketingCRM\EventListener\LoadClassMetadataListener")</sup>
   - `TrackingEventsDataGridListener`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/EventListener/TrackingEventsDataGridListener.php#L18 "Oro\Bridge\MarketingCRM\EventListener\TrackingEventsDataGridListener")</sup>
   - `ChannelRepository`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Entity/Repository/ChannelRepository.php#L9 "Oro\Bridge\MarketingCRM\Entity\Repository\ChannelRepository")</sup>
   - `OroMarketingCRMBridgeExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/DependencyInjection/OroMarketingCRMBridgeExtension.php#L10 "Oro\Bridge\MarketingCRM\DependencyInjection\OroMarketingCRMBridgeExtension")</sup>
   - `MagentoTrackingVisitEventProviderPass`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/DependencyInjection/CompilerPass/MagentoTrackingVisitEventProviderPass.php#L10 "Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass\MagentoTrackingVisitEventProviderPass")</sup>
   - `MagentoTrackingVisitProviderPass`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/DependencyInjection/CompilerPass/MagentoTrackingVisitProviderPass.php#L10 "Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass\MagentoTrackingVisitProviderPass")</sup>
   - `MagentoWebsiteVisitProviderPass`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/DependencyInjection/CompilerPass/MagentoWebsiteVisitProviderPass.php#L10 "Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass\MagentoWebsiteVisitProviderPass")</sup>
   - `CustomerController`<sup>[[?]](https://github.com/oroinc/crm/tree/4.2.0-alpha.3/src/Oro/Bridge/MarketingCRM/Controller/CustomerController.php#L14 "Oro\Bridge\MarketingCRM\Controller\CustomerController")</sup>

