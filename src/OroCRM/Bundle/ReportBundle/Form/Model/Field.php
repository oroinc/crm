<?php

namespace OroCRM\Bundle\ReportBundle\Form\Model;

class Field
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $sorting;

    /**
     * Get field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set field name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get field label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set field label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get sorting mode.
     * Can be ASC, DESC or null.
     *
     * @return string|null
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Get sorting mode.
     * Can be ASC, DESC, empty string or null. The empty string or null are same and means no sorting.
     *
     * @param string|null $sorting
     */
    public function setSorting($sorting = null)
    {
        if ($sorting !== null && empty($sorting)) {
            $sorting = null;
        }
        $this->sorting = $sorting;
    }
}
