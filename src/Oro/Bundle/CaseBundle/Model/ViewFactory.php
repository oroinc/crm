<?php

namespace Oro\Bundle\CaseBundle\Model;

use Oro\Bundle\AttachmentBundle\Provider\PictureSourcesProviderInterface;
use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Provide functionality to create view for custom entities
 */
class ViewFactory
{
    private AuthorizationCheckerInterface $authorizationChecker;

    private RouterInterface $router;

    private EntityNameResolver $entityNameResolver;

    private DateTimeFormatterInterface $dateTimeFormatter;

    private PictureSourcesProviderInterface $pictureSourcesProvider;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        EntityNameResolver $entityNameResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        PictureSourcesProviderInterface $pictureSourcesProvider
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->entityNameResolver = $entityNameResolver;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->pictureSourcesProvider = $pictureSourcesProvider;
    }

    /**
     * @param CaseComment[] $comments
     * @return array
     */
    public function createCommentViewList(array $comments): array
    {
        $result = [];

        foreach ($comments as $comment) {
            $result[] = $this->createCommentView($comment);
        }

        return $result;
    }

    public function createCommentView(CaseComment $comment): array
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
            'permissions'   => [
                'edit'      => $this->authorizationChecker->isGranted('EDIT', $comment),
                'delete'    => $this->authorizationChecker->isGranted('DELETE', $comment),
            ],
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

    private function createAuthorView(Contact|User $author): array
    {
        return $author instanceof Contact
            ? $this->createContactView($author)
            : $this->createUserView($author);
    }

    private function createContactView(Contact $contact): array
    {
        return [
            'id' => $contact->getId(),
            'url' => $this->router->generate('oro_contact_view', ['id' => $contact->getId()]),
            'fullName' => $this->entityNameResolver->getName($contact),
            'avatar' => null,
            'permissions' => [
                'view' => $this->authorizationChecker->isGranted('VIEW', $contact)
            ],
        ];
    }

    private function createUserView(User $user): array
    {
        $avatar = $user->getAvatar();

        return [
            'id' => $user->getId(),
            'url' => $this->router->generate('oro_user_view', ['id' => $user->getId()]),
            'fullName' => $this->entityNameResolver->getName($user),
            'avatarPicture' => $this->pictureSourcesProvider->getFilteredPictureSources($avatar, 'avatar_xsmall'),
            'permissions' => [
                'view' => $this->authorizationChecker->isGranted('VIEW', $user)
            ],
        ];
    }
}
