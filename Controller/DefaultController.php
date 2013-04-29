<?php

namespace Oro\Bundle\LogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('OroLogBundle:Default:index.html.twig', array('name' => $name));
    }
}
