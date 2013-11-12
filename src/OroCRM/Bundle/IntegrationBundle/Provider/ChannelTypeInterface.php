<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface ChannelTypeInterface
{
    /** Return channel settings */
    public function getSettings();
}
