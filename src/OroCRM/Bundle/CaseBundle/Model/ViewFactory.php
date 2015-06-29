<?php

namespace OroCRM\Bundle\CaseBundle\Model;

use Symfony\Component\Routing\RouterInterface;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\CaseBundle\Entity\CaseComment;

class ViewFactory
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter;

    /**
     * @var CacheManager
     */
    protected $imageCacheManager;

    /**
     * @var AttachmentManager
     */
    protected $attachmentManager;

    /**
     * @param SecurityFacade $securityFacade
     * @param RouterInterface $router
     * @param EntityNameResolver $entityNameResolver
     * @param DateTimeFormatter $dateTimeFormatter
     * @param AttachmentManager $attachmentManager
     */
    public function __construct(
        SecurityFacade $securityFacade,
        RouterInterface $router,
        EntityNameResolver $entityNameResolver,
        DateTimeFormatter $dateTimeFormatter,
        AttachmentManager $attachmentManager
    ) {
        $this->securityFacade = $securityFacade;
        $this->router = $router;
        $this->entityNameResolver = $entityNameResolver;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->attachmentManager = $attachmentManager;
    }

    /**
     * @param CaseComment[] $comments
     * @return array
     */
    public function createCommentViewList($comments)
    {
        $result = array();

        foreach ($comments as $comment) {
            $result[] = $this->createCommentView($comment);
        }

        return $result;
    }

    /**
     * @param CaseComment $comment
     * @return array
     */
    public function createCommentView(CaseComment $comment)
    {
        $result = [
            'id'            => $comment->getId(),
            'message'       => nl2br(htmlspecialchars($comment->getMessage())),
            'briefMessage'  => htmlspecialchars(
                mb_substr(preg_replace('/[\\n\\r]+/', ' ', $comment->getMessage()), 0, 200)
            ),
            'public'        => $comment->isPublic(),
            'createdAt'     => $comment->getCreatedAt() ?
                $this->dateTimeFormatter->format($comment->getCreatedAt()) : null,
            'updatedAt'     => $comment->getUpdatedAt() ?
                $this->dateTimeFormatter->format($comment->getUpdatedAt()) : null,
            'permissions'   => array(
                'edit'      => $this->securityFacade->isGranted('EDIT', $comment),
                'delete'    => $this->securityFacade->isGranted('DELETE', $comment),
            ),
        ];

        if ($comment->getContact()) {
            $result['createdBy'] = $this->createAuthorView($comment->getContact());
        } elseif ($comment->getOwner()) {
            $result['createdBy'] = $this->createAuthorView($comment->getOwner());
        }

        if ($comment->getUpdatedBy()) {
            $result['updatedBy'] = $this->createAuthorView($comment->getUpdatedBy());
        }

        return $result;
    }

    /**
     * @param Contact|User $author
     * @return array
     */
    protected function createAuthorView($author)
    {
        $result = array();
        if ($author instanceof Contact) {
            $result = $this->createContactView($author);
        } elseif ($author instanceof User) {
            $result = $this->createUserView($author);
        }

        return $result;
    }

    /**
     * @param Contact $contact
     * @return array
     */
    protected function createContactView(Contact $contact)
    {
        return [
            'id' => $contact->getId(),
            'url' => $this->router->generate('orocrm_contact_view', array('id' => $contact->getId())),
            'fullName' => $this->entityNameResolver->getName($contact),
            'avatar' => null,
            'permissions' => array(
                'view' => $this->securityFacade->isGranted('VIEW', $contact)
            ),
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    protected function createUserView(User $user)
    {
        return [
            'id' => $user->getId(),
            'url' => $this->router->generate('oro_user_view', array('id' => $user->getId())),
            'fullName' => $this->entityNameResolver->getName($user),
            'avatar' => $user->getAvatar()
                ? $this->attachmentManager->getFilteredImageUrl($user->getAvatar(), 'avatar_xsmall')
                : null,
            'permissions' => array(
                'view' => $this->securityFacade->isGranted('VIEW', $user)
            ),
        ];
    }
}
