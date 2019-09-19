<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Element;

use Behat\Gherkin\Node\TableNode;
use Oro\Bundle\FormBundle\Tests\Behat\Element\OroForm;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Element;

/**
 * Form describe opportunity form for behat test
 */
class OpportunityForm extends OroForm
{
    /**
     * Set recurrence parameters from TableNode
     *
     * @param TableNode $table
     */
    public function fill(TableNode $table)
    {
        foreach ($table->getRows() as list($name, $value)) {
            $value = self::normalizeValue($value);

            $field = $this->findField($name);
            self::assertNotNull($field, "Element $name not found");
            if ($value instanceof \DateTime) {
                $field->setValue($value->format("Y-m-d"));
            } else {
                $field->setValue($value);
            }
        }
    }

    /**
     * @param string $name
     * @return \Behat\Mink\Element\NodeElement|Element|null
     */
    public function findField($name)
    {
        if ($this->elementFactory->hasElement($name)) {
            return $this->elementFactory->createElement($name);
        } else {
            return parent::findField($name);
        }
    }
}
