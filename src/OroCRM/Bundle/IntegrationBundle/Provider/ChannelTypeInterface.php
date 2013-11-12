<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider;

interface ChannelTypeInterface
{
    /** Init channel type */
    public function init();

    /** Return channel settings */
    public function getSettings();
}
