- [AccountBundle](#accountbundle)
- [AnalyticsBundle](#analyticsbundle)
- [ChannelBundle](#channelbundle)
- [ContactBundle](#contactbundle)
- [SalesBundle](#salesbundle)

AccountBundle
-------------
* The following methods in class `AccountHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L21 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler")</sup> were changed:
  > - `__construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L30 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler")</sup>
  > - `__construct(ObjectManager $manager)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L21 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler")</sup>

  > - `process(Account $entity)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L44 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler")</sup>
  > - `process($entity, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L29 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler")</sup>

* The following properties in class `AccountHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L18 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L18 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler::$form")</sup>
   - `$requestStack`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/AccountBundle/Form/Handler/AccountHandler.php#L23 "Oro\Bundle\AccountBundle\Form\Handler\AccountHandler::$requestStack")</sup>

AnalyticsBundle
---------------
* The `CalculateAnalyticsCommand::isActive`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/AnalyticsBundle/Command/CalculateAnalyticsCommand.php#L45 "Oro\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand::isActive")</sup> method was removed.

ChannelBundle
-------------
* The `ChannelRepositoryAbstract::getVisitsCountByPeriodForChannelType`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ChannelBundle/Entity/Repository/ChannelRepositoryAbstract.php#L35 "Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepositoryAbstract::getVisitsCountByPeriodForChannelType")</sup> method was removed.
* The `LifetimeAverageAggregateCommand::isActive`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ChannelBundle/Command/LifetimeAverageAggregateCommand.php#L43 "Oro\Bundle\ChannelBundle\Command\LifetimeAverageAggregateCommand::isActive")</sup> method was removed.
* The `ChannelRepositoryInterface::getVisitsCountByPeriodForChannelType`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ChannelBundle/Entity/Repository/ChannelRepositoryInterface.php#L31 "Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepositoryInterface::getVisitsCountByPeriodForChannelType")</sup> method was removed.

ContactBundle
-------------
* The `HasContactInformation::validatedBy`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Validator/Constraints/HasContactInformation.php#L12 "Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformation::validatedBy")</sup> method was removed.
* The `HasContactInformationValidator::__construct`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Validator/Constraints/HasContactInformationValidator.php#L15 "Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformationValidator::__construct")</sup> method was removed.
* The `ContactType::getName`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Form/Type/ContactType.php#L237 "Oro\Bundle\ContactBundle\Form\Type\ContactType::getName")</sup> method was removed.
* The `Contact::getClass`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Entity/Contact.php#L771 "Oro\Bundle\ContactBundle\Entity\Contact::getClass")</sup> method was removed.
* The `HasContactInformationValidator::$translator`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Validator/Constraints/HasContactInformationValidator.php#L13 "Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformationValidator::$translator")</sup> property was removed.
* The following properties in class `ContactHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L20 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L20 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler::$form")</sup>
   - `$requestStack`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L25 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler::$requestStack")</sup>
* The following methods in class `ContactHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L23 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler")</sup> were changed:
  > - `__construct(FormInterface $form, RequestStack $requestStack, EntityManagerInterface $manager)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L32 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler")</sup>
  > - `__construct(EntityManagerInterface $manager)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L23 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler")</sup>

  > - `process(Contact $entity)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L46 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler")</sup>
  > - `process($entity, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/ContactBundle/Form/Handler/ContactHandler.php#L31 "Oro\Bundle\ContactBundle\Form\Handler\ContactHandler")</sup>

SalesBundle
-----------
* The following classes were removed:
   - `SalesFunnelEntityNameProvider`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Provider/SalesFunnelEntityNameProvider.php#L10 "Oro\Bundle\SalesBundle\Provider\SalesFunnelEntityNameProvider")</sup>
   - `ExtendSalesFunnel`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Model/ExtendSalesFunnel.php#L5 "Oro\Bundle\SalesBundle\Model\ExtendSalesFunnel")</sup>
   - `SalesFunnelApiType`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Type/SalesFunnelApiType.php#L12 "Oro\Bundle\SalesBundle\Form\Type\SalesFunnelApiType")</sup>
   - `SalesFunnelType`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Type/SalesFunnelType.php#L13 "Oro\Bundle\SalesBundle\Form\Type\SalesFunnelType")</sup>
   - `SalesFunnelHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/SalesFunnelHandler.php#L14 "Oro\Bundle\SalesBundle\Form\Handler\SalesFunnelHandler")</sup>
   - `SalesFunnel`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Entity/SalesFunnel.php#L53 "Oro\Bundle\SalesBundle\Entity\SalesFunnel")</sup>
   - `SalesFunnelRepository`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Entity/Repository/SalesFunnelRepository.php#L18 "Oro\Bundle\SalesBundle\Entity\Repository\SalesFunnelRepository")</sup>
   - `SalesFunnelController`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Controller/SalesFunnelController.php#L21 "Oro\Bundle\SalesBundle\Controller\SalesFunnelController")</sup>
   - `SalesFunnelController`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Controller/Api/Rest/SalesFunnelController.php#L19 "Oro\Bundle\SalesBundle\Controller\Api\Rest\SalesFunnelController")</sup>
* The `LeadActionsAccessProvider::isSalesFunnelWfEnabled`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Provider/LeadActionsAccessProvider.php#L80 "Oro\Bundle\SalesBundle\Provider\LeadActionsAccessProvider::isSalesFunnelWfEnabled")</sup> method was removed.
* The `Lead::getClass`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Entity/Lead.php#L496 "Oro\Bundle\SalesBundle\Entity\Lead::getClass")</sup> method was removed.
* The `DashboardController::mySalesFlowB2BAction`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Controller/Dashboard/DashboardController.php#L144 "Oro\Bundle\SalesBundle\Controller\Dashboard\DashboardController::mySalesFlowB2BAction")</sup> method was removed.
* The `LeadActionsAccessProvider::$isSalesFunnelWfEnabled`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Provider/LeadActionsAccessProvider.php#L31 "Oro\Bundle\SalesBundle\Provider\LeadActionsAccessProvider::$isSalesFunnelWfEnabled")</sup> property was removed.
* The following properties in class `B2bCustomerHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L17 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L17 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler::$form")</sup>
   - `$requestStack`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L20 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler::$requestStack")</sup>
* The following properties in class `LeadHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L17 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L17 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler::$form")</sup>
   - `$requestStack`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L20 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler::$requestStack")</sup>
* The following properties in class `OpportunityHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L19 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler")</sup> were removed:
   - `$form`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L19 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler::$form")</sup>
   - `$requestStack`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L22 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler::$requestStack")</sup>
* The following methods in class `B2bCustomerHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L23 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler")</sup> were changed:
  > - `__construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager, RequestChannelProvider $requestChannelProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L28 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler")</sup>
  > - `__construct(ObjectManager $manager, RequestChannelProvider $requestChannelProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L23 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler")</sup>

  > - `process(B2bCustomer $entity)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L47 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler")</sup>
  > - `process($entity, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/B2bCustomerHandler.php#L32 "Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler")</sup>

* The following methods in class `LeadHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L23 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler")</sup> were changed:
  > - `__construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager, RequestChannelProvider $requestChannelProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L28 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler")</sup>
  > - `__construct(ObjectManager $manager, RequestChannelProvider $requestChannelProvider)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L23 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler")</sup>

  > - `process(Lead $entity)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L47 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler")</sup>
  > - `process($entity, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/LeadHandler.php#L32 "Oro\Bundle\SalesBundle\Form\Handler\LeadHandler")</sup>

* The following methods in class `LeadToOpportunityHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/LeadToOpportunityHandler.php#L26 "Oro\Bundle\SalesBundle\Form\Handler\LeadToOpportunityHandler")</sup> were changed:
  > - `__construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager, RequestChannelProvider $requestChannelProvider, LeadToOpportunityProviderInterface $leadToOpportunityProvider, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/LeadToOpportunityHandler.php#L23 "Oro\Bundle\SalesBundle\Form\Handler\LeadToOpportunityHandler")</sup>
  > - `__construct(ObjectManager $manager, RequestChannelProvider $requestChannelProvider, LeadToOpportunityProviderInterface $leadToOpportunityProvider, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/LeadToOpportunityHandler.php#L26 "Oro\Bundle\SalesBundle\Form\Handler\LeadToOpportunityHandler")</sup>

  > - `process(Opportunity $entity)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/LeadToOpportunityHandler.php#L38 "Oro\Bundle\SalesBundle\Form\Handler\LeadToOpportunityHandler")</sup>
  > - `process($entity, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/LeadToOpportunityHandler.php#L39 "Oro\Bundle\SalesBundle\Form\Handler\LeadToOpportunityHandler")</sup>

* The following methods in class `OpportunityHandler`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L26 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler")</sup> were changed:
  > - `__construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager, RequestChannelProvider $requestChannelProvider, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L33 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler")</sup>
  > - `__construct(ObjectManager $manager, RequestChannelProvider $requestChannelProvider, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L26 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler")</sup>

  > - `process(Opportunity $entity)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L52 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler")</sup>
  > - `process($entity, FormInterface $form, Request $request)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Form/Handler/OpportunityHandler.php#L39 "Oro\Bundle\SalesBundle\Form\Handler\OpportunityHandler")</sup>

* The `DashboardController::opportunitiesByLeadSourceAction(Request $request, $widget)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.0.0-alpha.2/src/Oro/Bundle/SalesBundle/Controller/Dashboard/DashboardController.php#L51 "Oro\Bundle\SalesBundle\Controller\Dashboard\DashboardController")</sup> method was changed to `DashboardController::opportunitiesByLeadSourceAction(Request $request, $widget)`<sup>[[?]](https://github.com/oroinc/crm/tree/5.1.0-beta.1/src/Oro/Bundle/SalesBundle/Controller/Dashboard/DashboardController.php#L46 "Oro\Bundle\SalesBundle\Controller\Dashboard\DashboardController")</sup>
