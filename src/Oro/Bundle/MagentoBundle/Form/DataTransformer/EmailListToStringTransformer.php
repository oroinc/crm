<?php

namespace Oro\Bundle\MagentoBundle\Form\DataTransformer;

use Oro\Bundle\FormBundle\Form\DataTransformer\AbstractArrayToStringTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class EmailListToStringTransformer extends AbstractArrayToStringTransformer
{
    /**
     * @var array
     */
    private $availableDelimiters;

    /**
     * @param array $availableDelimiters
     * @param string $defaultDelimiter
     * @param boolean $filterUniqueValues
     */
    public function __construct(
        array $availableDelimiters = [',', ';'],
        $defaultDelimiter = ',',
        $filterUniqueValues = true
    ) {
        if (!in_array($defaultDelimiter, $availableDelimiters)) {
            throw new \LogicException(
                sprintf(
                    'Default delimiter \'%s\', should be included in available delimiters list',
                    $defaultDelimiter
                )
            );
        }
        parent::__construct($defaultDelimiter, $filterUniqueValues);
        $this->availableDelimiters = $availableDelimiters;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value || [] === $value) {
            return '';
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        return $this->transformArrayToString($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $value = $this->convertToDefaultDelimiter($value);
        return $this->transformStringToArray($value);
    }

    /**
     * @param string $value
     *
     * @return mixed|string
     */
    private function convertToDefaultDelimiter($value)
    {
        foreach ($this->availableDelimiters as $availableDelimiter) {
            $value = str_replace($availableDelimiter, $this->delimiter, $value);
        }

        return $value;
    }
}
