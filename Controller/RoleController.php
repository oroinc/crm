<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Form\Type\RoleType;

/**
 * @Route("/role")
 */
class RoleController extends Controller
{
   /**
    * Create role form
    *
    * @Route("/create", name="oro_user_role_create")
    * @Template("OroUserBundle:Role:edit.html.twig")
    */
    public function createAction()
    {
        return $this->editAction(new Role());
    }

   /**
    * Edit role form
    *
    * @Route("/edit/{id}", name="oro_user_role_edit", requirements={"id"="\d+"}, defaults={"id"=0})
    * @Template
    */
    public function editAction(Role $entity)
    {
        $request = $this->getRequest();
        $form    = $this->createForm(new RoleType(), $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Role successfully saved');

                return $this->redirect($this->generateUrl('oro_user_role_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

   /**
    * @Route("/remove/{id}", name="oro_user_role_remove", requirements={"id"="\d+"})
    */
    public function removeAction(Role $user)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($user);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Role successfully removed');

        return $this->redirect($this->generateUrl('oro_user_role_index'));
    }

    /**
     * @Route("/{page}/{limit}", name="oro_user_role_index", requirements={"page"="\d+","limit"="\d+"}, defaults={"page"=1,"limit"=20})
     * @Template
     */
    public function indexAction($page, $limit)
    {
        $query = $this
            ->getDoctrine()
            ->getEntityManager()
            ->createQuery('SELECT r FROM OroUserBundle:Role r');

        return array(
            'pager'  => $this->get('knp_paginator')->paginate($query, $page, $limit),
        );
    }
}