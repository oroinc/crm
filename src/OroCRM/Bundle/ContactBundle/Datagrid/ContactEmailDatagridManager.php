<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Oro\Bundle\EmailBundle\Datagrid\EmailDatagridManager;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailRepository;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactEmailDatagridManager extends EmailDatagridManager
{
    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @param Contact $contact
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;
        $this->routeGenerator->setRouteParameters(array('id' => $contact->getId()));
    }

    /**
     * @return Contact
     * @throws \LogicException
     */
    public function getContact()
    {
        if (!$this->contact) {
            throw new \LogicException('Datagrid manager has no configured Contact entity');
        }

        return $this->contact;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $emailAddresses = EmailUtil::extractEmailAddresses($this->contact->getEmails());
        $query->setParameter(EmailRepository::EMAIL_ADDRESSES, !empty($emailAddresses) ? $emailAddresses : null);
    }
}
