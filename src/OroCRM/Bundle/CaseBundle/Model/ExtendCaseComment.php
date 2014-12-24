<?php

namespace OroCRM\Bundle\CaseBundle\Model;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\CommentBundle\Entity\BaseComment;

/**
 * @ORM\Entity
 */
class ExtendCaseComment extends BaseComment
{
    public function __construct()
    {
    }
}
