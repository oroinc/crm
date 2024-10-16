<?php

namespace Oro\Bundle\ReportCRMBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides action to display all reports.
 */
class ReportController extends AbstractController
{
    /**
     *
     * @param string $reportGroupName
     * @param string $reportName
     * @return array
     */
    #[Route(
        path: '/static/{reportGroupName}/{reportName}/{_format}',
        name: 'oro_reportcrm_index',
        requirements: ['reportGroupName' => '\w+', 'reportName' => '\w+', '_format' => 'html|json'],
        defaults: ['_format' => 'html']
    )]
    #[Template]
    #[AclAncestor('oro_report_view')]
    public function indexAction($reportGroupName, $reportName)
    {
        $gridName  = implode('-', ['oro_reportcrm', $reportGroupName, $reportName]);
        $gridConfig = $this->container->get(ManagerInterface::class)->getConfigurationForGrid($gridName);
        $pageTitle = (string) ($gridConfig['pageTitle'] ?? '');

        $requiredFeatures = $gridConfig['requiredFeatures'] ?? [];
        $featureChecker = $this->getFeatureChecker();
        foreach ($requiredFeatures as $requiredFeature) {
            if (!$featureChecker->isFeatureEnabled($requiredFeature)) {
                throw $this->createNotFoundException();
            }
        }

        return [
            'pageTitle' => $this->container->get(TranslatorInterface::class)->trans($pageTitle),
            'gridName'  => $gridName,
            'params'    => [
                'reportGroupName' => $reportGroupName,
                'reportName'      => $reportName
            ]
        ];
    }

    /**
     * @return FeatureChecker
     */
    protected function getFeatureChecker()
    {
        return $this->container->get(FeatureChecker::class);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                FeatureChecker::class,
                ManagerInterface::class,
            ]
        );
    }
}
