<?php

namespace Oro\Bundle\SalesBundle\Tests\Behat\Element;

use Behat\Mink\Element\NodeElement;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\AssertTrait;

class OpportunityProbabilitiesConfigRaw
{
    use AssertTrait;

    /** @var NodeElement */
    private $label;

    /** @var string */
    private $labelValue;

    /** @var NodeElement */
    private $probability;

    /** @var int */
    private $probabilityValue;

    /** @var bool */
    private $isProbabilityReadOnly;

    /** @var NodeElement */
    private $default;

    /** @var NodeElement */
    private $remove;

    /** @var bool */
    private $isRemovable;

    public function __construct(NodeElement $row)
    {
        $this->label = $row->find('css', 'input[data-name="field__label"]');
        self::assertTrue($this->label->isValid());
        $this->labelValue = $this->label->getValue();

        $this->probability = $row->find('css', 'input[data-name="field__probability"]');
        self::assertTrue($this->probability->isValid());
        $this->probabilityValue = (int) $this->probability->getValue();
        $this->isProbabilityReadOnly = $this->probability->hasAttribute('readonly');

        $this->default = $row->find('css', 'input[data-name="field__is-default"]');
        self::assertTrue($this->default->isValid());

        $this->remove = $row->find('css', 'button.removeRow');
        $this->isRemovable = !is_null($this->remove);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->labelValue;
    }

    /**
     * @param string $value
     */
    public function setLabel($value)
    {
        $this->label->setValue($value);
    }

    /**
     * @return int
     */
    public function getProbability()
    {
        return $this->probabilityValue;
    }

    /**
     * @param int $value
     */
    public function setProbability($value)
    {
        if ($value != $this->probabilityValue) {
            self::assertFalse($this->isProbabilityReadOnly, sprintf('"%s" is read only', $this->labelValue));
            $this->probability->setValue($value);
        }
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default->isChecked();
    }

    public function setDefault()
    {
        $this->default->click();
    }

    public function remove()
    {
        self::assertTrue($this->isRemovable, sprintf('Can\'t remove "%s" row', $this->labelValue));
        $this->remove->press();
    }
}
