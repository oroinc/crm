<?php
namespace Oro\Bundle\DataFlowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Form\FormInterface;
use Oro\Bundle\DataFlowBundle\Form\Type\ConnectorType;
use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;
use Oro\Bundle\DataFlowBundle\Configuration\EditableConfigurationInterface;
use Oro\Bundle\DataFlowBundle\Form\Handler\ConfigurationHandler;
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
            // configure connector (add job, etc)
            array(
                'label'  => $translator->trans('(3) Configure jobs'),
                'route'  => 'oro_dataflow_connector_configure',
                'params' => array('id' => $connectorId)
            ),
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
        $serviceIds = array_keys($this->container->get('oro_dataflow.connectors')->getConnectorToJobs());

        $repository = $this->getDoctrine()->getEntityManager()->getRepository('OroDataFlowBundle:Connector');
        $entities = $repository->findAll();

        return array(
            'serviceIds' => $serviceIds,
            'connectors' => $entities,
            'steps'      => $this->getNavigationMenu()
        );
    }

    /**
     * Create connector
     *
     * @Route("/create/{serviceId}")
     * @Template("OroDataFlowBundle:Connector:edit.html.twig")
     *
     * @return null
     */
    public function createAction($serviceId)
    {
        // TODO pass by form to change description
        $description = time();

        $entity = new Connector();
        $entity->setServiceId($serviceId);
        $entity->setDescription($description);

        $service = $this->container->get($entity->getServiceId());
        $configuration = new Configuration();
        $configuration->setTypeName($service->getConfigurationName());
        $entity->setConfiguration($configuration);

        // TODO allow to choose many jobs and order them
        $serviceIds = $this->container->get('oro_dataflow.connectors')->getConnectorToJobs();
        $jobId = current($serviceIds[$serviceId]);
        $jobService = $this->container->get($jobId);
        $job = new Job();
        $description = time();
        $configuration = new Configuration();
        $configuration->setTypeName($jobService->getConfigurationName());
        $job->setConfiguration($configuration);
        $job->setServiceId($jobId);
        $job->setDescription($description);

        $entity->addJob($job);
        $manager = $this->getDoctrine()->getEntityManager();
        $manager->persist($entity);
        $manager->flush();

        return $this->editAction($entity);
    }

    /**
     * Edit connector
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function editAction(Connector $entity)
    {

        $service = $this->container->get($entity->getServiceId());

        $form = $this->get($service->getConfigurationFormServiceId());
      //  var_dump($form); exit();

        $type = 'Acme\Bundle\DemoDataFlowBundle\Form\Type\MagentoConnectorType';
        $form = $this->createForm(new ConnectorType(), $entity, array('configuration_type' => $type));

        /*
        // prepare configuration
        $configuration = $entity->getConfiguration()->deserialize();
        $configuration->setId($entity->getConfiguration()->getId());

        // prepare and process if posted
        $form = $this->get($service->getConfigurationFormServiceId());
        $form->setData($configuration);
*/
        // process form
        if ('POST' === $this->get('request')->getMethod()) {

            $form->bind($this->get('request'));

            if ($form->isValid()) {


//                die('success');

                $this->get('session')->getFlashBag()->add('success', 'Configuration successfully saved');
                $url = $this->generateUrl('oro_dataflow_connector_configure', array('id' => $entity->getId()));

                return $this->redirect($url);



            }

            /*
            $handler = $this->get($service->getConfigurationFormHandlerServiceId());
            $handler->setForm($form);

            if ($handler->process($configuration)) {
            */

            //}
        }

        // render configuration form
        return array(
            'form'      => $form->createView(),
            'connector' => $entity,
            'steps'     => $this->getNavigationMenu()
        );
    }

    /**
     * Configure connector jobs
     *
     * @Route("/configure/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function configureAction(Connector $entity)
    {
        // TODO : edit only one job for now
        $job = $entity->getJobs()->first();
        $service = $this->container->get($job->getServiceId());
        $configuration = $job->getConfiguration()->deserialize();
        $configuration->setId($job->getConfiguration()->getId());

        // prepare and process if posted
        $form = $this->get($service->getConfigurationFormServiceId());
        $form->setData($configuration);

        // process form
        if ('POST' === $this->get('request')->getMethod()) {

            $handler = $this->get($service->getConfigurationFormHandlerServiceId());
            $handler->setForm($form);

            if ($handler->process($configuration)) {
                $this->get('session')->getFlashBag()->add('success', 'Configuration successfully saved');
                $url = $this->generateUrl('oro_dataflow_connector_run', array('id' => $entity->getId()));

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
     * Run
     *
     * @param Connector $connector
     *
     * @Route("/run/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template()
     *
     * @return array
     */
    public function runAction(Connector $entity)
    {
        if ('POST' === $this->get('request')->getMethod()) {

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
