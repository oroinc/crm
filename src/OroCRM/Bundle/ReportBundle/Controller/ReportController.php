<?php

namespace OroCRM\Bundle\ReportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;

class ReportController extends Controller
{
    /**
     * @Route(
     *      "/{reportGroupName}/{reportName}/{_format}",
     *      name="orocrm_report_index",
     *      requirements={"reportGroupName"="\w+", "reportName"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template()
     * @Acl(
     *      id="orocrm_reports",
     *      type="action",
     *      label="Reports",
     *      group_name=""
     * )
     */
    public function indexAction($reportGroupName, $reportName)
    {
        $input = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/reports.yml'));
        $gridServiceName = 'orocrm_report.datagrid.' . implode('.', array($reportGroupName, $reportName));
        if (!$this->has($gridServiceName) || !isset($input['reports'][$reportGroupName][$reportName])) {
            throw $this->createNotFoundException();
        }

        /** @var ReportGridManagerAbstract $datagridManager */
        $datagridManager = $this->get($gridServiceName);

        $definition = $input['reports'][$reportGroupName][$reportName];
        $datagridManager->setReportDefinitionArray($definition);
        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'reportGroupName' => $reportGroupName,
                'reportName'      => $reportName
            )
        );

        $pageTitle = $definition['name'];
        $this->get('oro_navigation.title_service')->setParams(array('%reportName%' => $pageTitle));

        $datagridView = $datagridManager->getDatagrid()->createView();

        if ('json' == $this->getRequest()->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        return array(
            'pageTitle' => $pageTitle,
            'datagrid'  => $datagridView
        );
    }
}
