<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Stub;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;

/**
 * Stub required to mock extended entity methods
 */
class ContactReasonStub extends ContactReason
{
    /**
     * @var string
     */
    private $defaultTitle;

    /**
     * @return string
     */
    public function getDefaultTitle()
    {
        return $this->defaultTitle;
    }

    /**
     * @param string $value
     */
    public function setDefaultTitle($value)
    {
        $this->defaultTitle = $value;
    }

    /**
     * This is not real implementation of the method needed only for test purposes!
     * @param Collection $values
     * @return mixed
     */
    public function getDefaultFallbackValue(Collection $values)
    {
        return $this->defaultTitle;
    }
}
