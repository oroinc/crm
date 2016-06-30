<?php

namespace OroCRM\Bundle\SalesBundle\Datagrid;

use OroCRM\Bundle\SalesBundle\Provider\OpportunityGridViewsProvider;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;
use Oro\Bundle\DataGridBundle\Extension\GridViews\View;

class OpportunityViewList extends AbstractViewsList
{
    /** @var OpportunityGridViewsProvider  */
    protected $gridViewsProvider;

    /**
     * @param TranslatorInterface $translator
     * @param OpportunityGridViewsProvider $provider
     */
    public function __construct(TranslatorInterface $translator, OpportunityGridViewsProvider $provider)
    {
        parent::__construct($translator);
        $this->gridViewsProvider = $provider;
    }

    /**
     * {@inheritDoc}
     */
    protected function getViewsList()
    {
        $views = [];
        $viewsData = $this->gridViewsProvider->getData();
        foreach ($viewsData as $view) {
            $opportunityView = new View(
                $view['name'],
                $view['filters'],
                $view['sorters'],
                'system',
                $view['columns']
            );
            if ($view['is_default']) {
                $opportunityView->setDefault(true);
            }
            $opportunityView->setLabel($this->translator->trans($view['label']));
            $views[] = $opportunityView;
        }
        
        return $views;
    }
}
