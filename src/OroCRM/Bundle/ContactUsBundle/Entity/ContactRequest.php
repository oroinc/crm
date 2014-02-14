<?php

namespace OroCRM\Bundle\ContactUsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_contactus_request")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=50)
 * @Config(
 *  routeName="orocrm_contactus_request_index",
 *  defaultValues={
 *      "security"={
 *          "type"="ACL",
 *          "permissions"="All",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class ContactRequest extends AbstractContactRequest
{
}
