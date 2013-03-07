<?php

$data = array();

/** @var $datagrid \Oro\Bundle\GridBundle\Datagrid\Datagrid */
foreach ($datagrid->getResults() as $object) {
    $record = array();
    /** @var $field \Oro\Bundle\GridBundle\Field\FieldDescription */
    foreach ($datagrid->getColumns() as $field) {
        $value = $field->getFieldValue($object);
        if ($value !== null) {
            if ($value instanceof \DateTime) {
                $value = $value->format(\DateTime::ISO8601);
            } elseif ($field->getType() == \Oro\Bundle\GridBundle\Field\FieldDescription::TYPE_INTEGER) {
                $value = (int)$value;
            } elseif ($field->getType() == \Oro\Bundle\GridBundle\Field\FieldDescription::TYPE_DECIMAL) {
                $value = (float)$value;
            } elseif (is_object($value) && is_callable(array($value, '__toString'))) {
                $value = (string)$value;
            }
            $record[$field->getName()] = $value;
        }
    }
    $data[] = $record;
}

$result = array(
    'data' => $data,
    'options' => array(
        'totalRecords' => $datagrid->getPager()->getNbResults()
    ),
);

echo json_encode($result);
