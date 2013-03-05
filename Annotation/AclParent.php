<?php

namespace Oro\Bundle\UserBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class AclParent
{
    /**
     * @var string
     */
    private $id;

    public function __construct($id)
    {
        $this->setId($id);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
