<?php
namespace Oro\Bundle\DataFlowBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataFlowBundle\Entity\Configuration;
use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;

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
     * Constrcutor
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(Request $request, ObjectManager $manager)
    {
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
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
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param ConfigurationInterface $entity
     */
    protected function onSuccess(ConfigurationInterface $entity)
    {
        // serialize
        $format = 'xml';
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $data = $serializer->serialize($entity, $format);

        // prepare persist
        $configuration = new Configuration();
        $configuration->setDescription('my new conf');
        $configuration->setTypeName(get_class($entity));
        $configuration->setFormat($format);
        $configuration->setData($data);
        $this->manager->persist($configuration);

        // save
        $this->manager->flush();
    }
}
