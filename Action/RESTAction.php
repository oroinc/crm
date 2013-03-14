<?php

namespace Oro\Bundle\GridBundle\Action;

class RESTAction extends AbstractAction
{
    /**
     * @var string
     */
    protected $type = self::TYPE_REST;

    /**
     * Assert additional REST options ("method")
     *
     * @return array
     */
    public function getOptions()
    {
        if (!$this->isProcessed) {
            $this->assertOption('method');
        }

        return parent::getOptions();
    }
}
