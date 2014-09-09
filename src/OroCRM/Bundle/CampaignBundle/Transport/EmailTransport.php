<?php

namespace OroCRM\Bundle\CampaignBundle\Transport;

use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailTransport implements TransportInterface
{
    /**
     * @var Processor $processor
     */
    protected $processor;

    /**
     * @var EmailRenderer
     */
    protected $emailRenderer;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param Processor $processor
     * @param EmailRenderer $emailRenderer
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        Processor $processor,
        EmailRenderer $emailRenderer,
        DoctrineHelper $doctrineHelper
    ) {
        $this->processor = $processor;
        $this->emailRenderer = $emailRenderer;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function send(EmailCampaign $campaign, $entity, $from, array $to)
    {
        $entityId = $this->doctrineHelper->getSingleEntityIdentifier($entity);
        $marketingList = $campaign->getMarketingList();

        $template = $campaign->getTemplate();
        list ($subjectRendered, $templateRendered) = $this->emailRenderer->compileMessage(
            $template,
            ['entity' => $entity]
        );


        $emailModel = new Email();
        $emailModel
            ->setType($template->getType())
            ->setFrom($from)
            ->setEntityClass($marketingList->getEntity())
            ->setEntityId($entityId)
            ->setTo($to)
            ->setSubject($subjectRendered)
            ->setBody($templateRendered);

        $this->processor->process($emailModel);
    }
}
