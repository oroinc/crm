<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Model;

use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;

class ContactInformationFieldHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryDesigner;

    /**
     * @var ContactInformationFieldHelper
     */
    protected $helper;

    protected function setUp()
    {
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryDesigner = $this
            ->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->helper = new ContactInformationFieldHelper($this->configProvider);
    }

    public function testGetContactInformationColumnsNoDefinition()
    {
        $this->assertEmpty($this->helper->getContactInformationColumns($this->queryDesigner));
    }

    public function testGetContactInformationColumnsNoColumns()
    {
        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will($this->returnValue(json_encode(array('columns' => array()))));
        $this->assertEmpty($this->helper->getContactInformationColumns($this->queryDesigner));
    }

    public function testGetContactInformationColumns()
    {
        $entity = 'Entity';
        $fieldOne = 'col1'; // Entity level config, CI=email
        $fieldTwo = 'col2'; // Field level config, CI=phone
        $fieldThree = 'col3'; // Field level config, no CI
        $fieldFour = 'col4'; // Not configurable

        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will(
                $this->returnValue(
                    json_encode(
                        array(
                            'columns' => array(
                                array('name' => $fieldOne),
                                array('name' => $fieldTwo),
                                array('name' => $fieldThree),
                                array('name' => $fieldFour)
                            )
                        )
                    )
                )
            );

        $this->queryDesigner->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $this->configProvider->expects($this->atLeastOnce())
            ->method('hasConfig')
            ->will(
                $this->returnValueMap(
                    array(
                        array($entity, null, true),
                        array($entity, $fieldOne, true),
                        array($entity, $fieldTwo, true),
                        array($entity, $fieldThree, true),
                        array($entity, $fieldFour, false)
                    )
                )
            );

        $entityConfig = $this->getConfig(
            'contact_information',
            array('email' => array(array('fieldName' => $fieldOne)))
        );
        $fieldWithInfoConfig = $this->getConfig(
            'contact_information',
            'phone'
        );
        $fieldNoInfoConfig = $this->getConfig(
            'contact_information',
            null
        );
        $this->configProvider->expects($this->atLeastOnce())
            ->method('getConfig')
            ->will(
                $this->returnValueMap(
                    array(
                        array($entity, null, $entityConfig),
                        array($entity, $fieldOne, $fieldNoInfoConfig),
                        array($entity, $fieldTwo, $fieldWithInfoConfig),
                        array($entity, $fieldThree, $fieldNoInfoConfig)
                    )
                )
            );

        $this->assertEquals(
            array('email' => array(array('name' => $fieldOne)), 'phone' => array(array('name' => $fieldTwo))),
            $this->helper->getContactInformationColumns($this->queryDesigner)
        );
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfig($key, $data)
    {
        $config = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $config->expects($this->atLeastOnce())
            ->method('get')
            ->with($key)
            ->will($this->returnValue($data));

        return $config;
    }
}
