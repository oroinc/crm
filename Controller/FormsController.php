<?php

namespace Oro\Bundle\WindowsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\WindowsBundle\Form\ContactType;

/**
 * @Route("/forms")
 */
class FormsController extends Controller
{
    /**
     * Contact form
     *
     * @Route("/test")
     * @Template()
     */
    public function testAction()
    {
        $request = $this->getRequest();
        $form = $this->createForm(new ContactType());
        if ($request->getMethod() == 'POST') {
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Text window
     *
     * @Route("/test2")
     * @Template("OroWindowsBundle:Forms:test2custom.html.twig")
     */
    public function test2Action()
    {
        return array();
    }

    /**
     * Text window
     *
     * @Route("/testGet/{id}")
     * @Template()
     */
    public function testGetAction($id)
    {
        $request = $this->getRequest();
        return array("requestId" => $id);
    }
}
