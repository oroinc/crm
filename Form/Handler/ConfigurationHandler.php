<?php
namespace Oro\Bundle\DataFlowBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataFlowBundle\Entity\Configuration;
use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;
use Symfony\Component\Form\FormError;

/**
 * Configuration form handler
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConfigurationHandler
{

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * Format used for serialization
     * @var string
     */
    protected $format;

    /**
     * Constructor
     *
     * @param Request       $request the request
     * @param ObjectManager $manager the manager which deal with configuration persistence
     * @param string        $format  the format used to serialize data
     */
    public function __construct(Request $request, ObjectManager $manager, $format)
    {
        $this->request = $request;
        $this->manager = $manager;
        $this->format  = $format;
    }

    /**
     * Set form
     *
     * @param FormInterface $form
     *
     * @return ConfigurationHandler
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Return form
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Process form
     *
     * @param ConfigurationInterface $entity
     *
     * @return bool True on successfull processing, false otherwise
     */
    public function process(ConfigurationInterface $entity)
    {
        $this->form->setData($entity);

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);

            if ($this->form->isValid()) {
                if ($this->onSuccess($entity)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param ConfigurationInterface $entity
     *
     * @return boolean
     */
    protected function onSuccess(ConfigurationInterface $entity)
    {
        // serialize configuration
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $data = $serializer->serialize($entity, $this->format);

        // prepare persist of configuration entity
        if ($entity->getId()) {
            $repository = $this->manager->getRepository('OroDataFlowBundle:Configuration');
            $configuration = $repository->find($entity->getId());
        } else {
            $configuration = new Configuration();
        }
        $configuration->setTypeName(get_class($entity));
        $configuration->setFormat($this->format);
        $configuration->setData($data);

        // save
        $this->manager->persist($configuration);
        $this->manager->flush();
        $entity->setId($configuration->getId());

        return true;
    }
}
