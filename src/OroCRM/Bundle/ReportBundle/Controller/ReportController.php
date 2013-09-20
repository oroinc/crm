<?php

namespace OroCRM\Bundle\ReportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     */
    public function indexAction($reportGroupName, $reportName)
    {
        $input = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/reports.yml'));
        /** @var ReportGridManagerAbstract $datagridManager */
        $datagridManager = $this->get('orocrm_report.datagrid.' . implode('.', array($reportGroupName, $reportName)));

        if (isset($input['reports'][$reportGroupName][$reportName])) {
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
        }
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
