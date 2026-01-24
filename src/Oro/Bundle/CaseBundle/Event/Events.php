<?php

namespace Oro\Bundle\CaseBundle\Event;

/**
 * Defines event names for case entity operations.
 */
final class Events
{
    const BEFORE_SAVE = 'orcrm_case.before_save';

    private function __construct()
    {
    }
}
