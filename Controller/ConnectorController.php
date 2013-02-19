<?php
namespace Oro\Bundle\DataFlowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\FormInterface;
use Oro\Bundle\DataFlowBundle\Form\Type\ConnectorType;
use Oro\Bundle\DataFlowBundle\Form\Type\JobType;
use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;
use Oro\Bundle\DataFlowBundle\Entity\Connector;
use Oro\Bundle\DataFlowBundle\Entity\Job;
use Oro\Bundle\DataFlowBundle\Entity\Configuration;

/**
 * Connector controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/connector")
 *
 */
class ConnectorController extends Controller
{

    /**
     * Build contextual navigation menu wit steps
     *
     * @return \ArrayAccess
     */
    public function getNavigationMenu()
    {
        // get params
        $request     = $this->container->get('request');
        $connectorId = $request->get('id');

        // prepare steps
        $translator = $this->container->get('translator');
        $items = array(
            // select
            array(
                'label'  => $translator->trans('(1) Select connector'),
                'route'  => 'oro_dataflow_connector_index',
                'params' => array()
            ),
            // edit connector
            array(
                'label' => $translator->trans('(2) Configure connector'),
                'route' => 'oro_dataflow_connector_edit',
                'params' => array('id' => $connectorId)
            ),
            /*
            // configure connector (add job, etc)
            array(
                'label'  => $translator->trans('(3) Configure jobs'),
                'route'  => 'oro_dataflow_connector_configure',
                'params' => array('id' => $connectorId)
            ),
            */
            // schedule / run
            array(
                'label'  => $translator->trans('(4) Run'),
                'route'  => 'oro_dataflow_connector_run',
                'params' => array('id' => $connectorId)
            )
        );

        // highlight current step, disable following
        $currentRoute = $request->get('_route');
        $toDisable    = false;
        foreach ($items as &$item) {
            if ($item['route'] == $currentRoute) {
                $item['class']= 'active';
                $toDisable = true;
            } elseif ($toDisable) {
                $item['route']= false;
            }
        }

        return $items;
    }

    /**
     * Select a connector
     *
     * @Route("/index")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getEntityManager()->getRepository('OroDataFlowBundle:Connector');
        $entities = $repository->findAll();

        return array(
            'connectors' => $entities,
            'steps'      => $this->getNavigationMenu()
        );
    }

    /**
     * Create connector
     *
     * @Route("/create")
     * @Template("OroDataFlowBundle:Connector:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $entity = new Connector();

        return $this->editAction($entity);
    }

    /**
     * Edit connector
     *
     * @param Connector $entity
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Connector $entity)
    {
        $serviceIds = array_keys($this->container->get('oro_dataflow.connectors')->getConnectorToJobs());
        $form = $this->createForm(new ConnectorType(), $entity, array('serviceIds' => $serviceIds));

        // process form
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bind($this->getRequest());
            if ($form->isValid()) {

                // create default configuration
                if (!$entity->getId()) {
                    $service = $this->container->get($entity->getServiceId());
                    $configuration = new Configuration();
                    $configuration->setTypeName($service->getConfigurationName());
                    $entity->setConfiguration($configuration);
                }

                // persist
                $manager = $this->getDoctrine()->getEntityManager();
                $manager->persist($entity);
                $manager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Connector successfully saved');
                $url = $this->generateUrl('oro_dataflow_connector_index');

                return $this->redirect($url);
            }
        }

        // render configuration form
        return array(
            'form'      => $form->createView(),
            'connector' => $entity,
            'steps'     => $this->getNavigationMenu()
        );
    }

    /**
     * @param Connector $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(Connector $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Connector successfully removed');

        return $this->redirect($this->generateUrl('oro_dataflow_connector_index'));
    }

    /**
     * Run
     *
     * @param Connector $entity
     *
     * @Route("/run/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     *
     * @return array
     */
    public function runAction(Connector $entity)
    {
        if ('POST' === $this->getRequest()->getMethod()) {

            $confConnector = $entity->getConfiguration()->deserialize();

            // TODO deal with jobs order (depends on scheduler ?)
            foreach ($entity->getJobs() as $job) {

                $confJob = $job->getConfiguration()->deserialize();
                $service = $this->get($job->getServiceId());
                $service->configure($confConnector, $confJob);
                $service->run();
                $this->get('session')->getFlashBag()->add('success', 'Run job '.$job->getServiceId());

                $messages = $service->getMessages();
                foreach ($messages as $message) {
                    $this->get('session')->getFlashBag()->add($message[0], $message[1]);
                }
            }
        }

        return array(
            'connector' => $entity,
            'steps'     => $this->getNavigationMenu()
        );
    }
}
