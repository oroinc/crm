<?php

namespace OroCRM\Bundle\ReportBundle\Form\Model;

class Filter
{
    /**
     * @var string
     */
    protected $fieldName;

    /**
     * Get field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set field name
     *
     * @param string $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }
}
