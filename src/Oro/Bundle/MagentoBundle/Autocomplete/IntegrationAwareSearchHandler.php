<?php

namespace Oro\Bundle\MagentoBundle\Autocomplete;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class IntegrationAwareSearchHandler extends SearchHandler
{
    /** @var string */
    protected $dataChannelClass;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param string $dataChannelClass
     * @return IntegrationAwareSearchHandler
     */
    public function setDataChannelClass($dataChannelClass)
    {
        $this->dataChannelClass = $dataChannelClass;

        return $this;
    }

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAllDependenciesInjected()
    {
        if (!$this->entityRepository || !$this->idFieldName || !$this->dataChannelClass) {
            throw new \RuntimeException('Search handler is not fully configured');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts = explode(';', $query);
        $id = $parts[0];
        $channelId = !empty($parts[1]) ? $parts[1] : false;

        $criteria = [$this->idFieldName => $id];
        if (false !== $channelId) {
            $dataChannel = $this->getDataChannelById($channelId);
            if ($dataChannel) {
                $criteria['channel'] = $dataChannel->getDataSource();
            }
        }

        return [$this->entityRepository->findOneBy($criteria, null)];
    }

    /**
     * @param int $dataChannelId
     * @return Channel
     */
    protected function getDataChannelById($dataChannelId)
    {
        /** @var Channel $dataChannel */
        return $this->objectManager->find($this->dataChannelClass, $dataChannelId);
    }
}
