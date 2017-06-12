<?php

namespace OroCRM\Bundle\MagentoBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

abstract class IntegrationAwareSearchHandler extends SearchHandler
{
    /** @var string */
    protected $dataChannelClass;

    /** @var SecurityFacade */
    protected $securityFacade;

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
     * @param SecurityFacade $securityFacade
     */
    public function setSecurityFacade(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
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
