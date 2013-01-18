<?php

namespace Oro\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
