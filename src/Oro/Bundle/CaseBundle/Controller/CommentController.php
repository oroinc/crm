<?php

namespace Oro\Bundle\CaseBundle\Controller;

use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Form\Handler\CaseEntityHandler;
use Oro\Bundle\CaseBundle\Model\CaseEntityManager;
use Oro\Bundle\CaseBundle\Model\ViewFactory;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controller for Comments.
 */
class CommentController extends AbstractController
{
    /**
     * @param Request $request
     * @param CaseEntity $case
     * @return JsonResponse
     */
    #[Route(
        path: '/{id}/comment/list.{_format}',
        name: 'oro_case_comment_list',
        requirements: ['id' => '\d+', '_format' => 'json'],
        defaults: ['_format' => 'json']
    )]
    #[AclAncestor('oro_case_comment_view')]
    public function commentsListAction(Request $request, CaseEntity $case)
    {
        $order = $request->get('sorting', 'DESC');
        $comments = $this->container->get(CaseEntityManager::class)->getCaseComments($case, $order);

        return new JsonResponse(
            $this->container->get(ViewFactory::class)->createCommentViewList($comments)
        );
    }

    #[Route(path: '/{id}/widget/comment', name: 'oro_case_widget_comments', requirements: ['id' => '\d+'])]
    #[Template('@OroCase/Comment/comments.html.twig')]
    #[AclAncestor('oro_case_comment_view')]
    public function commentsWidgetAction(CaseEntity $case)
    {
        return [
            'case' => $case
        ];
    }

    /**
     * @param Request $request
     * @param CaseEntity $case
     * @return array|RedirectResponse
     */
    #[Route(path: '/{caseId}/comment/create', name: 'oro_case_comment_create', requirements: ['caseId' => '\d+'])]
    #[ParamConverter('case', options: ['id' => 'caseId'])]
    #[Template('@OroCase/Comment/update.html.twig')]
    #[AclAncestor('oro_case_comment_create')]
    public function createAction(Request $request, CaseEntity $case)
    {
        $comment = $this->container->get(CaseEntityManager::class)->createComment($case);
        $comment->setOwner($this->getUser());

        $formAction = $this->container->get(EntityRoutingHelper::class)
            ->generateUrlByRequest('oro_case_comment_create', $request, ['caseId' => $case->getId()]);

        return $this->update($comment, $formAction);
    }

    #[Route(path: '/comment/{id}/update', name: 'oro_case_comment_update', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_case_comment_update')]
    public function updateAction(CaseComment $comment)
    {
        $formAction = $this->container->get('router')->generate('oro_case_comment_update', ['id' => $comment->getId()]);

        $user = $this->getUser();
        if ($user instanceof User) {
            $comment->setUpdatedBy($user);
        }

        return $this->update($comment, $formAction);
    }

    /**
     * @param CaseComment $comment
     * @param string $formAction
     * @return array
     */
    protected function update(CaseComment $comment, $formAction)
    {
        $saved = $this->container->get(CaseEntityHandler::class)->process($comment);

        return [
            'saved'      => $saved,
            'entity'     => $comment,
            'data'       => $saved ? $this->container->get(ViewFactory::class)->createCommentView($comment) : null,
            'form'       => $this->container->get('oro_case.form.comment')->createView(),
            'formAction' => $formAction,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                ViewFactory::class,
                CaseEntityManager::class,
                EntityRoutingHelper::class,
                CaseEntityHandler::class,
                'oro_case.form.comment' => Form::class,
            ]
        );
    }
}
