<?php

namespace OroCRM\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

/**
 * @ORM\Entity
 */
class InternalTransportSettings extends TransportSettings
{
    /**
     * @var EmailTemplate
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\EmailBundle\Entity\EmailTemplate")
     * @ORM\JoinColumn(name="email_template_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $template;

    /**
     * Set template
     *
     * @param EmailTemplate $emailTemplate
     *
     * @return InternalTransportSettings
     */
    public function setTemplate(EmailTemplate $emailTemplate = null)
    {
        $this->template = $emailTemplate;

        return $this;
    }

    /**
     * Get template
     *
     * @return EmailTemplate|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                array(
                    'template' => $this->getTemplate()
                )
            );
        }

        return $this->settings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
