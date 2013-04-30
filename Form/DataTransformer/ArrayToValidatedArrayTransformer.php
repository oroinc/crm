<?php

namespace Oro\Bundle\FilterBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class ArrayToValidatedArrayTransformer implements DataTransformerInterface
{
    /**
     * @var null|int|callback
     */
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($array)
    {
        if (null === $array) {
            return array();
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($array)
    {
        if (null === $array) {
            return array();
        }

        if ($this->filter) {
            foreach ($array as $key => $value) {
                $resultValue = false;

                if (is_callable($this->filter)) {
                    $resultValue = call_user_func($this->filter, $value);
                } elseif (is_int($this->filter)) {
                    $resultValue = filter_var($value, $this->filter);
                }

                if (false === $resultValue) {
                    unset($array[$key]);
                } else {
                    $array[$key] = $resultValue;
                }
            }
        }

        return $array;
    }
}
