<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebsiteChoicesProvider
{
    /** @var  TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Example:
     * [
     *      WebsiteId => 'Website: WebsiteId, Stores: Store1, Store2'
     * ]
     *
     * @param MagentoTransportInterface $transport
     *
     * @return array
     */
    public function formatWebsiteChoices(MagentoTransportInterface $transport)
    {
        $websites = iterator_to_array($transport->getWebsites());
        $websites = array_map(
            function ($website) {
                return [
                    'id' => $website['website_id'],
                    'label' => $this->translator->trans(
                        'Website ID: %websiteId%, Stores: %storesList%',
                        [
                            '%websiteId%' => $website['website_id'],
                            '%storesList%' => $website['name']
                        ]
                    )
                ];
            },
            $websites
        );

        // Delete Admin website
        foreach ($websites as $key => $website) {
            if ($website['id'] == Website::ADMIN_WEBSITE_ID) {
                unset($websites[$key]);
            }
        }

        // Add all web sites choice
        array_unshift(
            $websites,
            [
                'id' => Website::ALL_WEBSITES,
                'label' => $this->translator->trans('oro.magento.magentotransport.all_sites')
            ]
        );

        return $websites;
    }
}
