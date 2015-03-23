<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

/**
 * @Route("/newsletter-subscriber")
 */
class NewsletterSubscriberController extends Controller
{
    /**
     * @Route("/", name="orocrm_magento_newsletter_subscriber_index")
     * @AclAncestor("orocrm_magento_newsletter_subscriber_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_magento.entity.newsletter_subscriber.class')
        ];
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @return array
     *
     * @Route("/view/{id}", name="orocrm_magento_newsletter_subscriber_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_newsletter_subscriber_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:NewsletterSubscriber"
     * )
     * @Template
     */
    public function viewAction(NewsletterSubscriber $newsletterSubscriber)
    {
        return ['entity' => $newsletterSubscriber];
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @return array
     *
     * @Route("/info/{id}", name="orocrm_magento_newsletter_subscriber_info", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_magento_newsletter_subscriber_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMMagentoBundle:NewsletterSubscriber"
     * )
     * @Template("OroCRMMagentoBundle:NewsletterSubscriber/widget:info.html.twig")
     */
    public function infoAction(NewsletterSubscriber $newsletterSubscriber)
    {
        return ['entity' => $newsletterSubscriber];
    }
}
