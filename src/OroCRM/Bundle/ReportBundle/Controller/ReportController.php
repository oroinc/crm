<?php

namespace OroCRM\Bundle\ReportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ReportController extends Controller
{
    /**
     * @Route(
     *      "/static/{reportGroupName}/{reportName}/{_format}",
     *      name="orocrm_report_index",
     *      requirements={"reportGroupName"="\w+", "reportName"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_report_view")
     */
    public function indexAction($reportGroupName, $reportName)
    {
        $gridName  = implode('-', ['orocrm_report', $reportGroupName, $reportName]);
        $pageTitle = $this->get('oro_datagrid.datagrid.manager')->getConfigurationForGrid($gridName)['pageTitle'];

        return [
            'pageTitle' => $this->get('translator')->trans($pageTitle),
            'gridName'  => $gridName,
            'params'    => [
                'reportGroupName' => $reportGroupName,
                'reportName'      => $reportName
            ]
        ];
    }
}
