<?php

namespace OroCRM\Bundle\CaseBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

/**
 * @Route("/comments")
 */
class CommentController extends Controller
{
    /**
     * @Route(
     *      "/widget/{caseId}",
     *      name="orocrm_case_comment_widget_list"
     * )
     *
     * @AclAncestor("oro_note_view")
     * @ParamConverter("case", options={"id" = "caseId"})
     * @Template("OroCRMCaseBundle:Comment:list.html.twig")
     */
    public function listAction(CaseEntity $case)
    {
        return array(
            'comments' => $case->getComments()
        );
    }
}
