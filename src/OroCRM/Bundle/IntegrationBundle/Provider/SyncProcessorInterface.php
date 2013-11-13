<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface SyncProcessorInterface
{
    /**
     * @param $batchData
     * @return mixed
     */
    public function process($batchData);
}
