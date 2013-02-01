<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Type\UserType;

class UserController extends Controller
{
   /**
    * @Route("/show/{id}", name="oro_user_show", requirements={"id"="\d+"})
    * @Template("OroUserBundle:User:edit.html.twig")
    */
    public function showAction(User $user)
    {
        return $this->editAction($user);
    }

   /**
    * Create user form
    *
    * @Route("/create", name="oro_user_create")
    * @Template("OroUserBundle:User:edit.html.twig")
    */
    public function createAction()
    {
        $user = $this->get('oro_user.flexible_manager')->createFlexible();

        return $this->editAction($user);
    }

   /**
    * Edit user form
    *
    * @Route("/edit/{id}", name="oro_user_edit", requirements={"id"="\d+"}, defaults={"id"=0})
    * @Template
    */
    public function editAction(User $entity)
    {
        $request = $this->getRequest();
        $form    = $this->createForm('oro_user_form', $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->get('oro_user.flexible_manager')->getStorageManager();
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User successfully saved');

                return $this->redirect($this->generateUrl('oro_user_index'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

   /**
    * @Route("/remove/{id}", name="oro_user_remove", requirements={"id"="\d+"})
    */
    public function removeAction(User $entity)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'User successfully removed');

        return $this->redirect($this->generateUrl('oro_user_index'));
    }

    /**
     * @Route("/{page}/{limit}", name="oro_user_index", requirements={"page"="\d+","limit"="\d+"}, defaults={"page"=1,"limit"=20})
     * @Template
     */
    public function indexAction($page, $limit)
    {
        $query = $this
            ->getDoctrine()
            ->getEntityManager()
            ->createQuery('SELECT u FROM OroUserBundle:User u');

        return array(
            'pager'  => $this->get('knp_paginator')->paginate($query, $page, $limit),
        );
    }
}