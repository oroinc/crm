<?php
namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\HasRequiredValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Exception\HasRequiredValueException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Aims to add has value required behavior
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class HasRequiredValueListener implements EventSubscriber
{

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * Inject service container
     *
     * @param ContainerInterface $container
     *
     * @return HasRequiredValueListener
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     *
     * @throws HasValueRequiredException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->checkRequired($args);
    }

    /**
     * Before update
     *
     * @param LifecycleEventArgs $args
     *
     * @throws IsRequiredException
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->checkRequired($args);
    }

    /**
     * Check if all values required are set
     * @param LifecycleEventArgs $args
     *
     * @throws HasValueRequiredException
     */
    protected function checkRequired(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // check entity implements "has required value" behavior
        if ($entity instanceof HasRequiredValueInterface) {

            // get flexible config
            $entityClass = get_class($entity);
            $flexibleConfig = $this->container->getParameter('oro_flexibleentity.flexible_config');
            $flexibleManagerName = $flexibleConfig['entities_config'][$entityClass]['flexible_manager'];
            $flexibleManager = $this->container->get($flexibleManagerName);

            // get required attributes
            $repo = $flexibleManager->getAttributeRepository();
            $attributes = $repo->findBy(array('entityType' => $entityClass, 'required' => true));

            // check that value is set for any required attributes
            foreach ($attributes as $attribute) {
                if (!$entity->getValueData($attribute->getCode())) {
                    throw new HasRequiredValueException('attribute '.$attribute->getCode().' is required');
                }
            }
        }
    }
}
