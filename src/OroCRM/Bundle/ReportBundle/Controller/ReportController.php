<?php

namespace OroCRM\Bundle\ReportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

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
        return [
            'reportGroupName' => $reportGroupName,
            'reportName'      => $reportName
        ];
    }
}
