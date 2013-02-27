<?php

$data = array();

/** @var $datagrid \Oro\Bundle\GridBundle\Datagrid\Datagrid */
foreach ($datagrid->getResults() as $object) {
    $record = array();
    /** @var $field \Oro\Bundle\GridBundle\Field\FieldDescription */
    foreach ($datagrid->getColumns() as $field) {
        $value = $field->getFieldValue($object);
        if ($value instanceof \DateTime) {
            $value = $value->format(\DateTime::ISO8601);
        } elseif (is_object($value) && is_callable(array($value, '__toString'))) {
            $value = (string)$value;
        }
        $record[$field->getName()] = $value;
    }
    $data[] = $record;
}

echo json_encode($data);
