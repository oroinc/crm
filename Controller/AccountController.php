<?php

namespace Oro\Bundle\AccountBundle\Controller;

use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Source\StreamSource;
use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Writer\CallbackWriter;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Query;

use Oro\Bundle\AccountBundle\Entity\Value\AccountValue;
use Oro\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Security\Acl\Exception\Exception;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Datagrid\AccountDatagridManager;

use Ddeboer\DataImport\Writer\CsvWriter;
use Ddeboer\DataImport\Reader\CsvReader;

/**
 * @Acl(
 *      id="oro_account_account",
 *      name="Account manipulation",
 *      description="Account manipulation",
 *      parent="root"
 * )
 */
class AccountController extends Controller
{
    /**
     * @Route("/view/{id}", name="oro_account_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_account_account_view",
     *      name="View Account",
     *      description="View account",
     *      parent="oro_account_account"
     * )
     */
    public function viewAction(Account $account)
    {
        return array(
            'account' => $account,
        );
    }

    /**
     * Create account form
     *
     * @Route("/create", name="oro_account_create")
     * @Template("OroAccountBundle:Account:update.html.twig")
     * @Acl(
     *      id="oro_account_account_create",
     *      name="Create Account",
     *      description="Create account",
     *      parent="oro_account_account"
     * )
     */
    public function createAction()
    {
        /** @var Account $account */
        $account = $this->getManager()->createEntity();
        return $this->updateAction($account);
    }

    /**
     * Edit user form
     *
     * @Route("/update/{id}", name="oro_account_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_account_account_update",
     *      name="Edit Account",
     *      description="Edit account",
     *      parent="oro_account_account"
     * )
     */
    public function updateAction(Account $entity)
    {
        $backUrl = $this->generateUrl('oro_account_index');

        if ($this->get('oro_account.form.handler.account')->process($entity)) {
            $this->getFlashBag()->add('success', 'Account successfully saved');
            return $this->redirect($backUrl);
        }

        return array(
            'form' => $this->get('oro_account.form.account')->createView(),
        );
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_account_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_account_account_list",
     *      name="View List of Accounts",
     *      description="View list of accounts",
     *      parent="oro_account_account"
     * )
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager AccountDatagridManager */
        $gridManager = $this->get('oro_account.account.datagrid_manager');
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'OroAccountBundle:Account:index.html.twig';
        }

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * @Route(
     *      "/export",
     *      name="oro_account_export"
     * )
     * @Acl(
     *      id="oro_account_account_export",
     *      name="Export Accounts",
     *      description="Export accounts",
     *      parent="oro_account_account"
     * )
     */
    public function exportAction()
    {
        $filename = 'accounts.csv';
        $dir = $this->get('kernel')->getRootDir() . '/../web/export/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . $filename;

        /** @var FlexibleEntityRepository $repo */
        $repo = $this->getManager()->getFlexibleManager()->getFlexibleRepository();

        $attrs = $this->getManager()->getFlexibleManager()->getAttributeRepository()
            ->findBy(array('entityType' => 'Oro\\Bundle\\AccountBundle\\Entity\\Account'));

        /** @var FlexibleQueryBuilder $qb */
        $qb = $repo->findByWithAttributesQB();
        $data = $qb->getQuery()->getResult();
        $file = new \SplFileObject($path, 'w');
        $writer = new CsvWriter($file);

        /** @var Account $account */
        $rows = array();
        $header = array('name');
        foreach ($attrs as $attr) {
            $rowName = array('attribute' => $attr->getCode());
            if ($attr instanceof TranslatableInterface) {
                $rowName['locale'] = $attr->getLocale();
            }
            if ($attr instanceof ScopableInterface) {
                $rowName['scope'] = $attr->getScope();
            }
            $header[] = $this->getAttributeRowName($rowName);
        }
        $rows[] = $header;
        foreach ($data as $account) {
            $row = array($account->getName());
            /* @var AbstractEntityAttribute $attr */
            foreach ($attrs as $attr) {
                $value = null;
                $attrValue = $account->getValue($attr->getCode());
                if ($attrValue) {
                    $val = $attrValue->getData();
                    if (is_string($val)) {
                        $value = $val;
                    }
                }
                $row[] = $value;

            }
            $rows[] = $row;
        }
        $reader = new ArrayReader($rows);

        $workflow = new Workflow($reader);
        $workflow->addWriter($writer);
        $workflow->process();

        $content = file_get_contents($path);
        //unlink($path);
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename);
        $response->setContent($content);
        return $response;
    }

    protected function getAttributeRowName($data)
    {
        $name = array();
        foreach ($data as $key => $val) {
            $name[] = $key . '__' . $val;
        }
        return implode(':', $name);
    }

    protected function getAttributeDataByName($name)
    {
        $data = array();
        $name = explode(':', $name);
        foreach ($name as $row) {
            list($key, $val) = explode('__', $row);
            $data[$key] = $val;
        }
        return $data;
    }

    /**
     * @Route(
     *      "/import",
     *      name="oro_account_import"
     * )
     * @Acl(
     *      id="oro_account_account_import",
     *      name="Import Accounts",
     *      description="Import accounts",
     *      parent="oro_account_account"
     * )
     */
    public function importAction()
    {
        $filename = 'accounts.csv';
        $dir = $this->get('kernel')->getRootDir() . '/../web/export/';
        $path = $dir . $filename;
        $source = new StreamSource($path);

        $reader = new CsvReader($source->getFile());
        $reader->setHeaderRowNumber(0);

        $em = $this->getManager();
        $writer = new CallbackWriter(
            function ($row) use ($em) {
                $double = $em->getRepository('OroAccountBundle:Account')->findBy(array('name' => $row['name']));
                if (!$double) {
                    $entity = new Account();
                    foreach ($row as $property => $val) {
                        if (!$val) {
                            continue;
                        }
                        $method = 'set' . ucwords($property);
                        if (method_exists($entity, $method)) {
                            $entity->$method($val);
                        } else {
                            $attrData = $this->getAttributeDataByName($property);
                            if ($attrData) {
                                /** @var FlexibleEntityRepository $repo */
                                $fm = $em->getFlexibleManager();
                                $repo = $fm->getFlexibleRepository();
                                /** @var Attribute $attribute */
                                $attribute = $repo->findAttributeByCode($attrData['attribute']);
                                /** @var AbstractFlexibleValue $value */
                                $value = $fm->createFlexibleValue();
                                $value->setAttribute($attribute);
                                $value->setData($val);
                                if ($attribute instanceof TranslatableInterface && array_key_exists('locale', $attrData)) {
                                    $value->setLocale($attrData['locale']);
                                }
                                if ($attribute instanceof ScopableInterface && array_key_exists('scope', $attrData)) {
                                    $value->setScope($attrData['scope']);
                                }
                                $entity->addValue($value);
                            }
                        }
                    }

                    $em->getObjectManager()->persist($entity);
                }
            }
        );

        $workflow = new Workflow($reader);
        $workflow->addWriter($writer);
        $workflow->process();

        $em->getObjectManager()->flush();

        return new Response('OK');
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return ApiFlexibleEntityManager
     */
    protected function getManager()
    {
        return $this->get('oro_account.account.manager.api');
    }
}
