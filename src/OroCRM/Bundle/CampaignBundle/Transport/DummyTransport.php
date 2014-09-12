<?php

namespace OroCRM\Bundle\CampaignBundle\Transport;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Form\Type\DummyTransportSettingsType;

class DummyTransport implements TransportInterface
{
    const NAME = 'dummy';

    /**
     * {@inheritdoc}
     */
    public function send(EmailCampaign $campaign, $entity, array $from, array $to)
    {
        $str = $campaign->getName();
        $str .= ' ';
        $str .= $campaign->getEntityName();
        $str .= $entity->getId();
        $str .= PHP_EOL;
        file_put_contents(sys_get_temp_dir() . '/' . 'dummy_transport.log', $str, FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Dummy';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return DummyTransportSettingsType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\Bundle\CampaignBundle\Entity\DummyTransportSettings';
    }
}
