<?php
namespace Oro\Bundle\DataFlowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\DataFlowBundle\Entity\Source;

/**
 * @Route("/source")
 */
class SourceController extends Controller
{
    /**
     * Create source form
     *
     * @Route("/create", name="oro_dataflow_source_create")
     * @Template("OroDataFlowBundle:Source:edit.html.twig")
     */
    public function createAction()
    {
        return $this->editAction(new Source());
    }

    /**
     * Edit source form
     *
     * @Route("/edit/{id}", name="oro_dataflow_source_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function editAction(Source $entity)
    {
        if ($this->get('oro_dataflow.form.handler.source')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Source successfully saved');

            return $this->redirect($this->generateUrl('oro_dataflow_source_index'));
        }

        return array(
                'form' => $this->get('oro_dataflow.form.source')->createView(),
        );
    }

    /**
     * @Route("/remove/{id}", name="oro_dataflow_source_remove", requirements={"id"="\d+"})
     */
    public function removeAction(Source $entity)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Source successfully removed');

        return $this->redirect($this->generateUrl('oro_dataflow_source_index'));
    }

    /**
     * @Route("/{page}/{limit}", name="oro_dataflow_source_index", requirements={"page"="\d+","limit"="\d+"}, defaults={"page"=1,"limit"=20})
     * @Template
     */
    public function indexAction($page, $limit)
    {
        $query = $this
            ->getDoctrine()
            ->getEntityManager()
            ->createQuery('SELECT r FROM OroDataFlowBundle:Source r');

        return array(
            'pager'  => $this->get('knp_paginator')->paginate($query, $page, $limit),
        );
    }
}