<?php

namespace OroCRM\Bundle\CampaignBundle\Transport;

interface VisibilityTransportInterface extends TransportInterface {

    /**
     * Determination of transport options in the form of creation.
     *
     * @return bool
     */
    public function isVisibleInForm();
}