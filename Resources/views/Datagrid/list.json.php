<?php

$data = array();

/** @var $datagrid \Oro\Bundle\GridBundle\Datagrid\Datagrid */
foreach ($datagrid->getResults() as $object) {
    $record = array();
    /** @var $property \Oro\Bundle\GridBundle\Property\PropertyInterface */
    foreach ($datagrid->getProperties() as $property) {
        $record[$property->getName()] = $property->getValue($object);
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
