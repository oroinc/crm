<?php

namespace Oro\Bundle\CampaignBundle\Transport;

interface VisibilityTransportInterface
{
    /**
     * Determination of transport options in the form of creation.
     *
     * @return bool
     */
    public function isVisibleInForm();
}
