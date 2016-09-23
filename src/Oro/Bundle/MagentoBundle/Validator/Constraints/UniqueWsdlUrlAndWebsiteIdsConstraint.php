<?php

namespace Oro\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class UniqueWsdlUrlAndWebsiteIdsConstraint extends UniqueEntity
{
    /** @var string */
    public $message = 'oro.magento.unique_wsdl_url_and_website_ids.message';

    /** @var string */
    public $repositoryMethod = 'getUniqueByWsdlUrlAndWebsiteIds';
}
