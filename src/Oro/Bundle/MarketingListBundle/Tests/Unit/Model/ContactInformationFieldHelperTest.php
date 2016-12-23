<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Model;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;

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

    /**
     * @var array
     */
    protected $fieldMappings = array(
        'one' => [
            'fieldName'  => 'one',
            'columnName' => 'col1',
        ],
        'two' => [
            'fieldName'  => 'two',
            'columnName' => 'col2',
        ],
        'three' => [
            'fieldName'  => 'three',
            'columnName' => 'col3',
        ],
        'four' => [
            'fieldName'  => 'four',
            'columnName' => 'col4',
        ],
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

    public function testGetContactInformationFieldsNoDefinition()
    {
        $this->assertEmpty($this->helper->getQueryContactInformationFields($this->queryDesigner));
    }

    public function testGetContactInformationFieldsNoColumns()
    {
        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will($this->returnValue(json_encode(array('columns' => array()))));
        $this->assertEmpty($this->helper->getQueryContactInformationFields($this->queryDesigner));
    }

    public function testGetContactInformationFields()
    {
        $entity = \stdClass::class;

        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will(
                $this->returnValue(
                    json_encode(
                        array(
                            'columns' => array(
                                array('name' => 'one'),
                                array('name' => 'two'),
                                array('name' => 'three'),
                                array('name' => 'four')
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
                'email' => array(array('name' => 'one')),
                'phone' => array(array('name' => 'two'))
            ),
            $this->helper->getQueryContactInformationFields($this->queryDesigner)
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
                        array($entity, 'one', true),
                        array($entity, 'two', true),
                        array($entity, 'three', true),
                        array($entity, 'four', false)
                    )
                )
            );

        $entityConfig = $this->getConfig(
            'contact_information',
            array('email' => array(array('fieldName' => 'one')))
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
                        array($entity, 'one', $fieldNoInfoConfig),
                        array($entity, 'two', $fieldWithInfoConfig),
                        array($entity, 'three', $fieldNoInfoConfig)
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
            array('one', 'email'),
            array('two', 'phone'),
            array('three', null),
            array('four', null),
        );
    }

    public function testgetEntityContactInformationFields()
    {
        $entity = '\stdClass';

        $metadata = new ClassMetadataInfo($entity);
        $metadata->fieldMappings = $this->fieldMappings;

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityMetadata')
            ->with($entity)
            ->will($this->returnValue($metadata));

        $this->assertContactInformationConfig($entity);
        $this->assertEquals(
            array('one' => 'email', 'two' => 'phone'),
            $this->helper->getEntityContactInformationFields($entity)
        );
    }

    public function testGetEntityContactInformationFieldsInfo()
    {
        $entity = '\stdClass';

        $metadata = new ClassMetadataInfo($entity);
        $metadata->fieldMappings = $this->fieldMappings;

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityMetadata')
            ->with($entity)
            ->will($this->returnValue($metadata));

        $this->assertContactInformationConfig($entity);

        $fields = array(
            array(
                'name' => 'one',
                'label' => 'One label'
            ),
            array(
                'name' => 'two',
                'label' => 'Two label'
            ),
            array(
                'name' => 'three',
                'label' => 'Three label'
            ),
            array(
                'name' => 'four',
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
                    'name' => 'one',
                    'label' => 'One label',
                    'contact_information_type' => 'email'
                ),
                array(
                    'name' => 'two',
                    'label' => 'Two label',
                    'contact_information_type' => 'phone'
                )
            ),
            $this->helper->getEntityContactInformationFieldsInfo($entity)
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
        $config->expects($this->any())
            ->method('get')
            ->with($key)
            ->will($this->returnValue($data));

        return $config;
    }
}
