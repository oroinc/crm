<?php

namespace OroCRM\Bundle\CallBundle\Controller;

use OroCRM\Bundle\CallBundle\Entity\Call;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class CallController extends Controller
{
    /**
     * @Route("/view/{id}", name="orocrm_call_view", requirements={"id"="\d+"})
     * @Template
     */
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $call = $em->getRepository('OroCRMCallBundle:Call')->find($id);

        return array('call' => $call);
    }

    /**
     * @Route("/new", name="orocrm_call_new")
     * @Template
     */
    public function newAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $call = new Call();

        return array(
            'form'     => $this->get('orocrm_call.form.call')->createView()
        );
    }
}

