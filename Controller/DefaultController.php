<?php

namespace Oro\Bundle\OroDataAuditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('OroDataAuditBundle:Default:index.html.twig', array('name' => $name));
    }
}
