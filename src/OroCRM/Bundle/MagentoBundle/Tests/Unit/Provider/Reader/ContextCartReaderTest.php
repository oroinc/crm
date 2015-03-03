<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCartReader;

class ContextCartReaderTest extends AbstractContextReaderTest
{
    /**
     * @return ContextCartReader
     */
    protected function getReader()
    {
        return new ContextCartReader($this->contextRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        return [
            ['customer' => ['originId' => 1]],
            ['customer' => ['originId' => 2]],
            ['customer' => ['originId' => 3]]
        ];
    }
}
