<?php

namespace Oro\Bundle\ReportCRMBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ReportController extends Controller
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
     */
    public function indexAction($reportGroupName, $reportName)
    {
        $gridName  = implode('-', ['oro_reportcrm', $reportGroupName, $reportName]);
        $gridConfig = $this->get('oro_datagrid.datagrid.manager')->getConfigurationForGrid($gridName);
        $pageTitle = $gridConfig['pageTitle'];

        $requiredFeatures = isset($gridConfig['requiredFeatures']) ? $gridConfig['requiredFeatures'] : [];
        $featureChecker = $this->getFeatureChecker();
        foreach ($requiredFeatures as $requiredFeature) {
            if (!$featureChecker->isFeatureEnabled($requiredFeature)) {
                throw $this->createNotFoundException();
            }
        }

        return [
            'pageTitle' => $this->get('translator')->trans($pageTitle),
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
        return $this->get('oro_featuretoggle.checker.feature_checker');
    }
}
