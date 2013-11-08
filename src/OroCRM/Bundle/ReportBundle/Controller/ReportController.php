<?php

namespace OroCRM\Bundle\ReportBundle\Controller;

use OroCRM\Bundle\ReportBundle\Entity\Report;
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
     *      name="orocrm_report_view",
     *      requirements={"reportGroupName"="\w+", "reportName"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template()
     * @Acl(
     *      id="orocrm_report_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMReportBundle:Report"
     * )
     */
    public function viewAction($reportGroupName, $reportName)
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

    /**
     * @Route("/create", name="orocrm_report_create")
     * @Template("OroCRMReportBundle:Report:update.html.twig")
     * @Acl(
     *      id="orocrm_report_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMReportBundle:Report"
     * )
     */
    public function createAction()
    {
        return $this->update(null);
    }

    /**
     * @Route("/update/{id}", name="orocrm_report_update", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template
     * @Acl(
     *      id="orocrm_report_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMReportBundle:Report"
     * )
     */
    public function updateAction(Report $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_report_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     *
     * @Template
     * @AclAncestor("orocrm_report_view")
     */
    public function indexAction()
    {
        return array();
    }

    protected function update(Report $entity = null)
    {
        if (!$entity) {
            //$entity = $this->getManager()->createEntity();
        }

        return array(
            'entity'   => $entity,
            'form'     => $this->get('orocrm_report.form.report')->createView(),
        );
    }
}
