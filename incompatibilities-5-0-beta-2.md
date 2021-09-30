- [AccountBundle](#accountbundle)
- [ActivityContactBundle](#activitycontactbundle)
- [AnalyticsBundle](#analyticsbundle)
- [CallCRM](#callcrm)
- [CaseBundle](#casebundle)
- [ChannelBundle](#channelbundle)
- [ContactBundle](#contactbundle)
- [ReportCRMBundle](#reportcrmbundle)
- [SalesBundle](#salesbundle)
- [TestFrameworkCRMBundle](#testframeworkcrmbundle)

AccountBundle
-------------
* The `Configuration`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/AccountBundle/DependencyInjection/Configuration.php#L8 "Oro\Bundle\AccountBundle\DependencyInjection\Configuration")</sup> class was removed.

ActivityContactBundle
---------------------
* The `Configuration`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ActivityContactBundle/DependencyInjection/Configuration.php#L8 "Oro\Bundle\ActivityContactBundle\DependencyInjection\Configuration")</sup> class was removed.

AnalyticsBundle
---------------
* The `Configuration`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/AnalyticsBundle/DependencyInjection/Configuration.php#L8 "Oro\Bundle\AnalyticsBundle\DependencyInjection\Configuration")</sup> class was removed.

CallCRM
-------
* The `Configuration`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bridge/CallCRM/DependencyInjection/Configuration.php#L8 "Oro\Bridge\CallCRM\DependencyInjection\Configuration")</sup> class was removed.

CaseBundle
----------
* The `Configuration`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/CaseBundle/DependencyInjection/Configuration.php#L8 "Oro\Bundle\CaseBundle\DependencyInjection\Configuration")</sup> class was removed.
* The following methods in class `CaseController`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/CaseBundle/Controller/CaseController.php#L77 "Oro\Bundle\CaseBundle\Controller\CaseController")</sup> were changed:
  > - `createAction()`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/CaseBundle/Controller/CaseController.php#L76 "Oro\Bundle\CaseBundle\Controller\CaseController")</sup>
  > - `createAction(Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/CaseBundle/Controller/CaseController.php#L77 "Oro\Bundle\CaseBundle\Controller\CaseController")</sup>

  > - `update(CaseEntity $case)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/CaseBundle/Controller/CaseController.php#L97 "Oro\Bundle\CaseBundle\Controller\CaseController")</sup>
  > - `update(CaseEntity $case, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/CaseBundle/Controller/CaseController.php#L99 "Oro\Bundle\CaseBundle\Controller\CaseController")</sup>


ChannelBundle
-------------
* The following classes were removed:
   - `LifetimeValueExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ChannelBundle/Twig/LifetimeValueExtension.php#L17 "Oro\Bundle\ChannelBundle\Twig\LifetimeValueExtension")</sup>
   - `MetadataExtension`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ChannelBundle/Twig/MetadataExtension.php#L16 "Oro\Bundle\ChannelBundle\Twig\MetadataExtension")</sup>
* The following methods in class `ChannelController`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/ChannelBundle/Controller/ChannelController.php#L53 "Oro\Bundle\ChannelBundle\Controller\ChannelController")</sup> were changed:
  > - `createAction()`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ChannelBundle/Controller/ChannelController.php#L52 "Oro\Bundle\ChannelBundle\Controller\ChannelController")</sup>
  > - `createAction(Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/ChannelBundle/Controller/ChannelController.php#L53 "Oro\Bundle\ChannelBundle\Controller\ChannelController")</sup>

  > - `update(Channel $channel)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ChannelBundle/Controller/ChannelController.php#L77 "Oro\Bundle\ChannelBundle\Controller\ChannelController")</sup>
  > - `update(Channel $channel, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/ChannelBundle/Controller/ChannelController.php#L79 "Oro\Bundle\ChannelBundle\Controller\ChannelController")</sup>

* The `ChannelVoter::setSettingsProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ChannelBundle/Acl/Voter/ChannelVoter.php#L44 "Oro\Bundle\ChannelBundle\Acl\Voter\ChannelVoter::setSettingsProvider")</sup> method was removed.
* The `ChannelVoter::$settingsProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ChannelBundle/Acl/Voter/ChannelVoter.php#L24 "Oro\Bundle\ChannelBundle\Acl\Voter\ChannelVoter::$settingsProvider")</sup> property was removed.

ContactBundle
-------------
* The `ContactExtension::getName`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ContactBundle/Twig/ContactExtension.php#L36 "Oro\Bundle\ContactBundle\Twig\ContactExtension::getName")</sup> method was removed.
* The `PrepareResultItemListener::prepareEmailItemDataEvent`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ContactBundle/EventListener/PrepareResultItemListener.php#L29 "Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener::prepareEmailItemDataEvent")</sup> method was removed.
* The `ContactNormalizer::normalize($object, $format = null, $context = [])`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ContactBundle/ImportExport/Serializer/Normalizer/ContactNormalizer.php#L45 "Oro\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer")</sup> method was changed to `ContactNormalizer::normalize($object, $format = null, $context = [])`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/ContactBundle/ImportExport/Serializer/Normalizer/ContactNormalizer.php#L42 "Oro\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer")</sup>
* The `PrepareResultItemListener::__construct(ContactNameFormatter $nameFormatter, DoctrineHelper $doctrineHelper)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ContactBundle/EventListener/PrepareResultItemListener.php#L23 "Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener")</sup> method was changed to `PrepareResultItemListener::__construct(ContactNameFormatter $nameFormatter, ManagerRegistry $doctrine)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/ContactBundle/EventListener/PrepareResultItemListener.php#L18 "Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener")</sup>
* The `PrepareResultItemListener::$doctrineHelper`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ContactBundle/EventListener/PrepareResultItemListener.php#L18 "Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener::$doctrineHelper")</sup> property was removed.

ReportCRMBundle
---------------
* The `Configuration`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/ReportCRMBundle/DependencyInjection/Configuration.php#L11 "Oro\Bundle\ReportCRMBundle\DependencyInjection\Configuration")</sup> class was removed.

SalesBundle
-----------
* The following properties in class `OpportunityListener`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/SalesBundle/Entity/EventListener/OpportunityListener.php#L16 "Oro\Bundle\SalesBundle\Entity\EventListener\OpportunityListener")</sup> were removed:
   - `$valuableChangesetKeys`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/SalesBundle/Entity/EventListener/OpportunityListener.php#L16 "Oro\Bundle\SalesBundle\Entity\EventListener\OpportunityListener::$valuableChangesetKeys")</sup>
   - `$currencyProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/SalesBundle/Entity/EventListener/OpportunityListener.php#L30 "Oro\Bundle\SalesBundle\Entity\EventListener\OpportunityListener::$currencyProvider")</sup>
* The `LeadController::disqualifyAction(Lead $lead)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/SalesBundle/Controller/LeadController.php#L154 "Oro\Bundle\SalesBundle\Controller\LeadController")</sup> method was changed to `LeadController::disqualifyAction(Lead $lead, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/SalesBundle/Controller/LeadController.php#L159 "Oro\Bundle\SalesBundle\Controller\LeadController")</sup>
* The following methods in class `SalesFunnelController`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/SalesBundle/Controller/SalesFunnelController.php#L79 "Oro\Bundle\SalesBundle\Controller\SalesFunnelController")</sup> were changed:
  > - `createAction()`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/SalesBundle/Controller/SalesFunnelController.php#L78 "Oro\Bundle\SalesBundle\Controller\SalesFunnelController")</sup>
  > - `createAction(Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/SalesBundle/Controller/SalesFunnelController.php#L79 "Oro\Bundle\SalesBundle\Controller\SalesFunnelController")</sup>

  > - `update(SalesFunnel $entity)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/SalesBundle/Controller/SalesFunnelController.php#L104 "Oro\Bundle\SalesBundle\Controller\SalesFunnelController")</sup>
  > - `update(SalesFunnel $entity, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.2/src/Oro/Bundle/SalesBundle/Controller/SalesFunnelController.php#L106 "Oro\Bundle\SalesBundle\Controller\SalesFunnelController")</sup>


TestFrameworkCRMBundle
----------------------
* The `Configuration`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-beta.1/src/Oro/Bundle/TestFrameworkCRMBundle/DependencyInjection/Configuration.php#L8 "Oro\Bundle\TestFrameworkCRMBundle\DependencyInjection\Configuration")</sup> class was removed.

