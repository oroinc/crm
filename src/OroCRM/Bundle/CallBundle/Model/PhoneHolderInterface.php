<?php

namespace OroCRM\Bundle\CallBundle\Model;

/**
 * Represents a subject which may provide phone numbers for call activity
 */
interface PhoneHolderInterface
{
    /**
     * Gets a primary phone number of entity which can be used to log call
     *
     * @return string
     */
    public function getPrimaryPhoneNumber();

    /**
     * Gets list of entity phone numbers which can be used to log call
     *
     * @return array
     */
    public function getPhoneNumbers();
}
