<?php
namespace Oro\Bundle\DataFlowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\FormInterface;
use Oro\Bundle\DataFlowBundle\Form\Type\JobType;
use Oro\Bundle\DataFlowBundle\Entity\Connector;
use Oro\Bundle\DataFlowBundle\Entity\Job;
use Oro\Bundle\DataFlowBundle\Entity\Configuration;

/**
 * Job controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/job")
 *
 */
class JobController extends Controller
{

    /**
     * Create job
     *
     * @param Connector $connector
     *
     * @Route("/create/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     *
     * @Template("OroDataFlowBundle:Job:edit.html.twig")
     *
     * @return array
     */
    public function createAction(Connector $connector)
    {
        $entity = new Job();
        $entity->setConnector($connector);

        return $this->editAction($entity);
    }

    /**
     * Edit job
     *
     * @param Job $entity
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Job $entity)
    {
        $connectorToJobIds = $this->container->get('oro_dataflow.connectors')->getConnectorToJobs();
        $connector = $entity->getConnector();
        $serviceIds = $connectorToJobIds[$connector->getServiceId()];

        $form = $this->createForm(new JobType(), $entity, array('serviceIds' => $serviceIds));

        // process form
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bind($this->getRequest());
            if ($form->isValid()) {

                // create default configuration
                if (!$entity->getId()) {
                    $service = $this->container->get($entity->getServiceId());
                    $configuration = new Configuration($service->getConfigurationName());
                    $entity->setConfiguration($configuration);
                }

                $manager = $this->getDoctrine()->getEntityManager();
                $manager->persist($entity);
                $manager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Job successfully saved');
                $url = $this->generateUrl('oro_dataflow_connector_index');

                return $this->redirect($url);
            }
        }

        return array('form' => $form->createView(), 'job' => $entity, 'connector' => $connector);
    }

    /**
     * @param Job $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(Job $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Job successfully removed');

        return $this->redirect($this->generateUrl('oro_dataflow_connector_index'));
    }
}
