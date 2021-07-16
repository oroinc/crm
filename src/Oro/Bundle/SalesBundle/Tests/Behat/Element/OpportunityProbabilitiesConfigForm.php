<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Element;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Oro\Bundle\TestFrameworkBundle\Behat\Element\Form;

class OpportunityProbabilitiesConfigForm extends Form
{
    /**
     * @throws ElementNotFoundException
     */
    public function fill(TableNode $table)
    {
        $headers = $table->getRow(0);
        self::assertEquals(['Status', 'Probability', 'Default'], $headers);
        $rows = $this->getRows();

        foreach ($table as $index => $item) {
            /** @var OpportunityProbabilitiesConfigRaw $row */
            $row = $rows[$index];
            self::assertEquals(
                $row->getLabel(),
                $item['Status'],
                'Change position and change Label is not implemented'
            );
            $row->setProbability($item['Probability']);

            if (true === in_array($item['Default'], ['yes', 'true'], true)) {
                $row->setDefault();
            }
        }
    }

    /**
     * @return OpportunityProbabilitiesConfigRaw[]
     */
    private function getRows()
    {
        $meta = [];
        $rows = $this->findAll(
            'css',
            'div[data-content^="opportunity[oro_sales___opportunity_statuses][value][enum][enum_options]"]'
        );

        /**
         * @var int $index
         * @var NodeElement $row
         */
        foreach ($rows as $index => $row) {
            $meta[$index] = new OpportunityProbabilitiesConfigRaw($row);
        }

        return $meta;
    }
}
