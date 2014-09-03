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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldProvider;

    /**
     * @var ContactInformationFieldHelper
     */
    protected $helper;

    protected $fields = array(
        'one' => 'col1',
        'two' => 'col2',
        'three' => 'col3',
        'four' => 'col4'
    );

    protected function setUp()
    {
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryDesigner = $this
            ->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldProvider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityFieldProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new ContactInformationFieldHelper(
            $this->configProvider,
            $this->doctrineHelper,
            $this->fieldProvider
        );
    }

    public function testGetContactInformationColumnsNoDefinition()
    {
        $this->assertEmpty($this->helper->getQueryContactInformationColumns($this->queryDesigner));
    }

    public function testGetContactInformationColumnsNoColumns()
    {
        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will($this->returnValue(json_encode(array('columns' => array()))));
        $this->assertEmpty($this->helper->getQueryContactInformationColumns($this->queryDesigner));
    }

    public function testGetContactInformationColumns()
    {
        $entity = 'Entity';

        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will(
                $this->returnValue(
                    json_encode(
                        array(
                            'columns' => array(
                                array('name' => $this->fields['one']),
                                array('name' => $this->fields['two']),
                                array('name' => $this->fields['three']),
                                array('name' => $this->fields['four'])
                            )
                        )
                    )
                )
            );

        $this->queryDesigner->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $this->assertContactInformationConfig($entity);

        $this->assertEquals(
            array(
                'email' => array(array('name' => $this->fields['one'])),
                'phone' => array(array('name' => $this->fields['two']))
            ),
            $this->helper->getQueryContactInformationColumns($this->queryDesigner)
        );
    }

    protected function assertContactInformationConfig($entity)
    {
        $this->configProvider->expects($this->atLeastOnce())
            ->method('hasConfig')
            ->will(
                $this->returnValueMap(
                    array(
                        array($entity, null, true),
                        array($entity, $this->fields['one'], true),
                        array($entity, $this->fields['two'], true),
                        array($entity, $this->fields['three'], true),
                        array($entity, $this->fields['four'], false)
                    )
                )
            );

        $entityConfig = $this->getConfig(
            'contact_information',
            array('email' => array(array('fieldName' => $this->fields['one'])))
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
                        array($entity, $this->fields['one'], $fieldNoInfoConfig),
                        array($entity, $this->fields['two'], $fieldWithInfoConfig),
                        array($entity, $this->fields['three'], $fieldNoInfoConfig)
                    )
                )
            );
    }

    /**
     * @dataProvider fieldTypesDataProvider
     * @param string $field
     * @param string $expectedType
     */
    public function testGetContactInformationFieldType($field, $expectedType)
    {
        $entity = '\stdClass';
        $this->assertContactInformationConfig($entity);
        $this->assertEquals($expectedType, $this->helper->getContactInformationFieldType($entity, $field));
    }

    public function fieldTypesDataProvider()
    {
        return array(
            array($this->fields['one'], 'email'),
            array($this->fields['two'], 'phone'),
            array($this->fields['three'], null),
            array($this->fields['four'], null),
        );
    }

    public function testGetEntityContactInformationColumns()
    {
        $entity = '\stdClass';
        $columns = array($this->fields['one'], $this->fields['two'], $this->fields['three'], $this->fields['four']);

        $this->assertEntityMetadataCall($entity, $columns);
        $this->assertContactInformationConfig($entity);
        $this->assertEquals(
            array($this->fields['one'] => 'email', $this->fields['two'] => 'phone'),
            $this->helper->getEntityContactInformationColumns($entity)
        );
    }

    public function testGetEntityContactInformationColumnsInfo()
    {
        $entity = '\stdClass';
        $columns = array($this->fields['one'], $this->fields['two'], $this->fields['three'], $this->fields['four']);

        $this->assertEntityMetadataCall($entity, $columns);
        $this->assertContactInformationConfig($entity);

        $fields = array(
            array(
                'name' => $this->fields['one'],
                'label' => 'One label'
            ),
            array(
                'name' => $this->fields['two'],
                'label' => 'Two label'
            ),
            array(
                'name' => $this->fields['three'],
                'label' => 'Three label'
            ),
            array(
                'name' => $this->fields['four'],
                'label' => 'Four label'
            ),
        );
        $this->fieldProvider->expects($this->once())
            ->method('getFields')
            ->with($entity, false, true)
            ->will($this->returnValue($fields));
        $this->assertEquals(
            array(
                array(
                    'name' => $this->fields['one'],
                    'label' => 'One label',
                    'contact_information_type' => 'email'
                ),
                array(
                    'name' => $this->fields['two'],
                    'label' => 'Two label',
                    'contact_information_type' => 'phone'
                )
            ),
            $this->helper->getEntityContactInformationColumnsInfo($entity)
        );
    }

    protected function assertEntityMetadataCall($entity, $columns)
    {
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getColumnNames')
            ->will($this->returnValue($columns));
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityMetadata')
            ->with($entity)
            ->will($this->returnValue($metadata));
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfig($key, $data)
    {
        $config = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $config->expects($this->any())
            ->method('get')
            ->with($key)
            ->will($this->returnValue($data));

        return $config;
    }
}
