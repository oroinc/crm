<?php

namespace Oro\Bundle\UserBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Acl
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $parent = null;

    public function __construct(array $data)
    {
        $this->setId($data['id']);
        $this->setName($data['name']);
        $this->setDescription($data['description']);
        if (isset($data['parent'])) {
            $this->setParent($data['parent']);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool|string
     */
    public function getParent()
    {
        if ($this->parent) {

            return $this->parent;
        }

        return false;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}