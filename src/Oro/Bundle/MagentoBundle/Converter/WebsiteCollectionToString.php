<?php

namespace Oro\Bundle\MagentoBundle\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\MagentoBundle\Entity\Website;

/**
 * Used in datagrid for conversion websites list to string
 */
class WebsiteCollectionToString
{
    /**
     * @return callback
     */
    public static function getConverterCallback()
    {
        return function (ResultRecordInterface $record) {
            /** @var ArrayCollection $websites */
            $websites = $record->getValue('websites');

            if (!$websites) {
                return '';
            }

            return implode(
                ', ',
                $websites->map(
                    function (Website $website) {
                        return $website->getName();
                    }
                )->toArray()
            );
        };
    }
}
