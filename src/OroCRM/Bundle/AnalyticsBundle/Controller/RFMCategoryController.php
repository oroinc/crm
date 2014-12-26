<?php

namespace OroCRM\Bundle\AnalyticsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\AnalyticsBundle\Entity\Repository\RFMMetricCategoryRepository;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @Route("/analytics")
 */
class RFMCategoryController extends Controller
{
    /**
     * @var RFMMetricCategoryRepository
     */
    protected $rfmMetricCategoryRepository;

    /**
     * @Route(
     *      "/rfm-category/view/channel/{entity}",
     *      name="orocrm_analytics_rfm_category_channel_view",
     *      requirements={"entity"="\d+"}
     * )
     * @ParamConverter(
     *      "channel",
     *      class="OroCRMChannelBundle:Channel",
     *      options={"id" = "entity"}
     * )
     * @AclAncestor("orocrm_channel_view")
     * @Template
     *
     * @param Channel $channel
     * @return array
     */
    public function channelViewAction(Channel $channel)
    {
        $rfmCategories = [
            RFMMetricCategory::TYPE_RECENCY => $this->getCategoriesByType(RFMMetricCategory::TYPE_RECENCY),
            RFMMetricCategory::TYPE_FREQUENCY => $this->getCategoriesByType(RFMMetricCategory::TYPE_FREQUENCY),
            RFMMetricCategory::TYPE_MONETARY => $this->getCategoriesByType(RFMMetricCategory::TYPE_MONETARY),
        ];

        return [
            'channel' => $channel,
            'rfmCategories' => $rfmCategories,
            'rfmCategoriesCount' => count($rfmCategories[RFMMetricCategory::TYPE_RECENCY])
        ];
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getCategoriesByType($type)
    {
        return $this->getRFMMetricCategoryRepository()
            ->getCategories($this->get('oro_security.acl_helper'), $type);
    }

    /**
     * @return RFMMetricCategoryRepository
     */
    protected function getRFMMetricCategoryRepository()
    {
        if (!$this->rfmMetricCategoryRepository) {
            $this->rfmMetricCategoryRepository = $this->getDoctrine()
                ->getRepository($this->container->getParameter('orocrm_analytics.entity.rfm_category.class'));
        }

        return $this->rfmMetricCategoryRepository;
    }
}
