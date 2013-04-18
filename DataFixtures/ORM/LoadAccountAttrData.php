<?php

namespace Oro\Bundle\AccountBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionSimpleSelectType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\MoneyType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextAreaType;

use Oro\Bundle\AccountBundle\Entity\Manager\AccountManager;

class LoadAccountAttrData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var AccountManager
     */
    protected $fm;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $sm;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->fm = $this->container->get('oro_account.account.manager');
        $this->sm = $this->fm->getStorageManager();
    }

    /**
     * Load sample user group data
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->addAttributes(
            array(
                'website',
                'office_phone',
                'office_fax',
                array(
                    'code' => 'description',
                    'type' => new TextAreaType()
                ),
                array(
                    'code' => 'annual_revenue',
                    'type' => new MoneyType()
                ),
                'employees',
                'ownership',
                'ticker_symbol',
                'rating'
            )
        );

        $this->sm->flush();
    }

    protected function addAttributes(array $attributes)
    {
        foreach ($attributes as $data) {
            if (is_string($data)) {
                $data = array('code' => $data);
            }
            if (!array_key_exists('code', $data)) {
                throw new \InvalidArgumentException('Code is required for attribute');
            }
            if (!array_key_exists('type', $data)) {
                $data['type'] = new TextType();
            }
            if (is_string($data)) {
                $data['type'] = new $data['type'];
            }
            if (!array_key_exists('options', $data)) {
                $data['options'] = null;
            }
            $this->addAttribute($data['code'], $data['type'], $data['options']);
        }
    }

    protected function addAttribute($name, $class, $options = null)
    {
        $attr = $this->fm
            ->createAttribute($class)
            ->setCode($name);

        if ($options) {
            foreach ($options as $option) {
                $attr->addOption(
                    $this->fm->createAttributeOption()->addOptionValue(
                        $this->fm->createAttributeOptionValue()->setValue($option)
                    )
                );
            }
        }
        $this->sm->persist($attr);
    }
}
