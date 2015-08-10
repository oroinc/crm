<?php

namespace OroCRM\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class UniqueWsdlUrlAndWebsiteIdsConstraint extends UniqueEntity
{
    /** @var string */
    public $message = 'orocrm.magento.unique_wsdl_url_and_website_ids.message';

    /** @var string */
    public $repositoryMethod = 'getUniqueByWsdlUrlAndWebsiteIds';
}
