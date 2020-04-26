<?php
declare(strict_types=1);

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator\Stub;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractLoadeableSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;

abstract class AbstractLoadableSoapIteratorStub extends AbstractLoadeableSoapIterator
{
    public function getTransport(): MagentoSoapTransportInterface
    {
        return $this->transport;
    }
}
