<?php

namespace Oro\Bundle\ReportCRMBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
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
     * @Route(
     *      "/static/{reportGroupName}/{reportName}/{_format}",
     *      name="oro_reportcrm_index",
     *      requirements={"reportGroupName"="\w+", "reportName"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_report_view")
     *
     * @param string $reportGroupName
     * @param string $reportName
     * @return array
     */
    public function indexAction($reportGroupName, $reportName)
    {
        $gridName  = implode('-', ['oro_reportcrm', $reportGroupName, $reportName]);
        $gridConfig = $this->get(Manager::class)->getConfigurationForGrid($gridName);
        $pageTitle = (string) ($gridConfig['pageTitle'] ?? '');

        $requiredFeatures = $gridConfig['requiredFeatures'] ?? [];
        $featureChecker = $this->getFeatureChecker();
        foreach ($requiredFeatures as $requiredFeature) {
            if (!$featureChecker->isFeatureEnabled($requiredFeature)) {
                throw $this->createNotFoundException();
            }
        }

        return [
            'pageTitle' => $this->get(TranslatorInterface::class)->trans($pageTitle),
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
        return $this->get(FeatureChecker::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                FeatureChecker::class,
                Manager::class,
            ]
        );
    }
}
