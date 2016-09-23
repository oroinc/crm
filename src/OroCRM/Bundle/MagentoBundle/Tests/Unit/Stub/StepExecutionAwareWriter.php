<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Stub;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

interface StepExecutionAwareWriter extends StepExecutionAwareInterface, ItemWriterInterface
{
}
