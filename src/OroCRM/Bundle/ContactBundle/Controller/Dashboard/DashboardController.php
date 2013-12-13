<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Dashboard;

use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Routing\Router;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/my_contacts/{_format}",
     *      name="orocrm_contact_my_contacts",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("OroCRMContactBundle:Dashboard:myContacts.html.twig")
     */
    public function myContactsAction()
    {
        /*$history = $this->getDoctrine()->getRepository('OroNavigationBundle:NavigationHistoryItem')
            ->createQueryBuilder('h')
            ->select('h')
            ->where('h.url LIKE :urlPart')
            ->andWhere('h.user = :user')
            ->setParameter('urlPart', '%contact%')
            ->setParameter('user', $this->getUser())
            ->setMaxResults(20)
            ->orderBy('h.visitedAt', 'DESC')
            ->getQuery()
            ->getResult();

        $records = [];
        $id = [];
        $dates = [];

        if (!empty($history)) {
            foreach ($history as $historyItem) {
                $url = str_replace('/app_dev.php', '', $historyItem->getUrl());
                try {
                    $resultRoute = $this->get('router')->match($url);
                    $item = ['visited' => $historyItem->getVisitedAt()];
                    if (isset($resultRoute['id'])) {
                        $item['id']  = $resultRoute['id'];
                        $id[] = $item['id'];
                    } else {
                        $dates[] = $historyItem->getVisitedAt()->format('Y-m-d H:i:s');
                    }
                    $records[] = $item;
                } catch (\Exception $e) {
                    continue;
                }
            }

        }




        $contacts = $this->getDoctrine()->getRepository('OroCRMContactBundle:Contact')
            ->createQueryBuilder('c')
            ->select('c')
            ->where('(c.id in (:cid) or c.createdAt in (:created))')
            //->andWhere('c.owner = :user')
            ->setParameter('cid', $id)
            ->setParameter('created', $dates)
            //->setParameter('user', $this->getUser())
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();



        $result = $this->get('router')->match('/user/role/update/3');
        var_dump($result);
        die;*/
        return [];
    }
}
