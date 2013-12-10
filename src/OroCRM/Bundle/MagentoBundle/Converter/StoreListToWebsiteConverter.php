<?php

namespace OroCRM\Bundle\MagentoBundle\Converter;

use Symfony\Component\Translation\TranslatorInterface;

class StoreListToWebsiteConverter
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Map stores array to grouped by website_id list
     *
     * Example:
     * [
     *      WebsiteId => 'Website: WebsiteId, Stores: Store1, Store2'
     * ]
     *
     * @param array $stores
     *
     * @return array
     */
    public function convert(array $stores = [])
    {
        $websites = [];

        foreach ($stores as $store) {
            $websites[$store->website_id] = isset($websites[$store->website_id])
                ? $websites[$store->website_id] : [$store->website_id];

            $websites[$store->website_id][] = $store->name;
        }
        $websites = array_map(
            function ($item) {
                $id = array_shift($item);

                return [
                    'id'    => $id,
                    'label' => $this->translator->trans(
                        'Website ID: %websiteId%, Stores: %storesList%',
                        [
                            '%websiteId%'  => $id,
                            '%storesList%' => implode(', ', $item)
                        ]
                    )
                ];
            },
            $websites
        );

        return $websites;
    }
}
