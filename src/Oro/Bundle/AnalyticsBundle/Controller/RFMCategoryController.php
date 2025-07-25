<?php

namespace Oro\Bundle\AnalyticsBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AnalyticsBundle\Entity\Repository\RFMMetricCategoryRepository;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Displays RFM categories settings for an entity.
 */
#[Route(path: '/analytics')]
class RFMCategoryController extends AbstractController
{
    /**
     * @var RFMMetricCategoryRepository
     */
    protected $rfmMetricCategoryRepository;

    /**
     *
     * @param Channel $channel
     * @return array
     */
    #[Route(
        path: '/rfm-category/view/channel/{entity}',
        name: 'oro_analytics_rfm_category_channel_view',
        requirements: ['entity' => '\d+']
    )]
    #[ParamConverter('channel', class: Channel::class, options: ['id' => 'entity'])]
    #[Template]
    #[AclAncestor('oro_channel_view')]
    public function channelViewAction(Channel $channel)
    {
        $rfmCategories = [
            RFMMetricCategory::TYPE_RECENCY => $this->getCategories($channel, RFMMetricCategory::TYPE_RECENCY),
            RFMMetricCategory::TYPE_FREQUENCY => $this->getCategories($channel, RFMMetricCategory::TYPE_FREQUENCY),
            RFMMetricCategory::TYPE_MONETARY => $this->getCategories($channel, RFMMetricCategory::TYPE_MONETARY)
        ];

        return [
            'channel' => $channel,
            'rfmCategories' => $rfmCategories,
            'rfmCategoriesCount' => count($rfmCategories[RFMMetricCategory::TYPE_RECENCY])
        ];
    }

    /**
     * @param Channel $channel
     * @param string $type
     * @return array
     */
    protected function getCategories(Channel $channel, $type)
    {
        return $this->getRFMMetricCategoryRepository()
            ->getCategoriesByChannel($this->container->get(AclHelper::class), $channel, $type);
    }

    /**
     * @return RFMMetricCategoryRepository
     */
    protected function getRFMMetricCategoryRepository()
    {
        if (!$this->rfmMetricCategoryRepository) {
            $this->rfmMetricCategoryRepository = $this->container->get('doctrine')
                ->getRepository(RFMMetricCategory::class);
        }

        return $this->rfmMetricCategoryRepository;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                AclHelper::class,
                'doctrine' => ManagerRegistry::class,
            ]
        );
    }
}
