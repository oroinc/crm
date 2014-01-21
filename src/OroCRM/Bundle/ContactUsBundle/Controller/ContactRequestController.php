<?php

namespace OroCRM\Bundle\ContactUsBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContactRequestController extends Controller
{
    /**
     * @Route(name="oro_contact_request_list")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
} 
