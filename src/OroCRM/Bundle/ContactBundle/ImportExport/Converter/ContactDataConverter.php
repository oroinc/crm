<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Converter;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;
use Oro\Bundle\ImportExportBundle\Converter\QueryBuilderAwareInterface;
use OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactHeaderProvider;

class ContactDataConverter extends AbstractTableDataConverter implements QueryBuilderAwareInterface
{
    /**
     * @var ContactHeaderProvider
     */
    protected $headerProvider;

    /**
     * @var array
     */
    protected $headerConversionRules = array(
        // plain fields
        'ID'          => 'id',
        'Name Prefix' => 'namePrefix',
        'First Name'  => 'firstName',
        'Middle Name' => 'middleName',
        'Last Name'   => 'lastName',
        'Name Suffix' => 'nameSuffix',
        'Gender'      => 'gender',
        'Birthday'    => 'birthday',
        'Description' => 'description',
        'Job Title'   => 'jobTitle',
        'Fax'         => 'fax',
        'Skype'       => 'skype',
        'Twitter'     => 'twitter',
        'Facebook'    => 'facebook',
        'GooglePlus'  => 'googlePlus',
        'LinkedIn'    => 'linkedIn',
        'Source'      => 'source',
        'Method'      => 'method',
        // users (OroUserBundle:User)
        'Owner Username'       => 'owner:username',
        'Owner'                => 'owner:fullName',
        'Assigned To Username' => 'assignedTo:username',
        'Assigned To'          => 'assignedTo:fullName',
        // contact typed addresses (OroCRMContactBundle:ContactAddress)
        'Primary Address Label'       => 'addresses:0:label',
        'Primary Address Organization' => 'addresses:0:organization',
        'Primary Address Name Prefix'  => 'addresses:0:namePrefix',
        'Primary Address First Name'  => 'addresses:0:firstName',
        'Primary Address Middle Name'  => 'addresses:0:middleName',
        'Primary Address Last Name'   => 'addresses:0:lastName',
        'Primary Address Name Suffix'  => 'addresses:0:nameSuffix',
        'Primary Address Street'      => 'addresses:0:street',
        'Primary Address Street2'     => 'addresses:0:street2',
        'Primary Address City'        => 'addresses:0:city',
        'Primary Address Region'      => 'addresses:0:region',
        'Primary Address Region Text' => 'addresses:0:regionText',
        'Primary Address Country'     => 'addresses:0:country',
        'Primary Address Postal Code' => 'addresses:0:postalCode',
        'Numeric Address Label' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Label', 'addresses:$1:label'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):label', 'Address $1 Label'),
        ),
        'Numeric Address Organization' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Organization', 'addresses:$1:organization'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):organization', 'Address $1 Organization'),
        ),
        'Numeric Address Name Prefix' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Name Prefix', 'addresses:$1:namePrefix'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):namePrefix', 'Address $1 Name Prefix'),
        ),
        'Numeric Address First Name' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) First Name', 'addresses:$1:firstName'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):firstName', 'Address $1 First Name'),
        ),
        'Numeric Address Middle Name' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Middle Name', 'addresses:$1:middleName'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):middleName', 'Address $1 Middle Name'),
        ),
        'Numeric Address Last Name' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Last Name', 'addresses:$1:lastName'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):lastName', 'Address $1 Last Name'),
        ),
        'Numeric Address Name Suffix' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Name Suffix', 'addresses:$1:nameSuffix'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):nameSuffix', 'Address $1 Name Suffix'),
        ),
        'Numeric Address Street' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Street', 'addresses:$1:street'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):street', 'Address $1 Street'),
        ),
        'Numeric Address Street2' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Street2', 'addresses:$1:street2'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):street2', 'Address $1 Street2'),
        ),
        'Numeric Address City' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) City', 'addresses:$1:city'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):city', 'Address $1 City'),
        ),
        'Numeric Address Region' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Region', 'addresses:$1:region'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):region', 'Address $1 Region'),
        ),
        'Numeric Address Region Text' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Region Text', 'addresses:$1:regionText'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):regionText', 'Address $1 Region Text'),
        ),
        'Numeric Address Country' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Country', 'addresses:$1:country'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):country', 'Address $1 Country'),
        ),
        'Numeric Address Postal Code' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Postal Code', 'addresses:$1:postalCode'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):postalCode', 'Address $1 Postal Code'),
        ),
        // contact emails (OroCRMContactBundle:ContactEmail)
        'Primary Email' => 'emails:0',
        'Numeric Email' => array(
            self::FRONTEND_TO_BACKEND => array('Email (\d+)', 'emails:$1'),
            self::BACKEND_TO_FRONTEND => array('emails:(\d+)', 'Email $1'),
        ),
        // contact phones (OroCRMContactBundle:ContactPhone)
        'Primary Phone' => 'phones:0',
        'Numeric Phone' => array(
            self::FRONTEND_TO_BACKEND => array('Phone (\d+)', 'phones:$1'),
            self::BACKEND_TO_FRONTEND => array('phones:(\d+)', 'Phone $1'),
        ),
    );

    /**
     * @param ContactHeaderProvider $headerProvider
     */
    public function __construct(ContactHeaderProvider $headerProvider)
    {
        $this->headerProvider = $headerProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->headerProvider->setQueryBuilder($queryBuilder);
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        $complexConversionRules = array(
            // contact typed addresses (OroCRMContactBundle:ContactAddress)
            'Primary Address Type' => array(
                self::FRONTEND_TO_BACKEND => array(
                    'Primary Address Type (\d+)',
                    function (array $matches) {
                        return 'addresses:0:types:' . ($matches[1] - 1);
                    }
                ),
                self::BACKEND_TO_FRONTEND => array(
                    'addresses:0:types:(\d+)',
                    function (array $matches) {
                        return 'Primary Address Type ' . ($matches[1] + 1);
                    }
                ),
            ),
            'Numeric Address Type' => array(
                self::FRONTEND_TO_BACKEND => array(
                    'Address (\d+) Type (\d+)',
                    function (array $matches) {
                        return 'addresses:' . $matches[1] . ':types:' . ($matches[2] - 1);
                    }
                ),
                self::BACKEND_TO_FRONTEND => array(
                    'addresses:(\d+):types:(\d+)',
                    function (array $matches) {
                        return 'Address ' . $matches[1] . ' Type ' . ($matches[2] + 1);
                    }
                ),
            ),
            // contact groups (OroCRMContactBundle:Group)
            'Numeric Group' => array(
                self::FRONTEND_TO_BACKEND => array(
                    'Group (\d+)',
                    function (array $matches) {
                        return 'groups:' . ($matches[1] - 1);
                    }
                ),
                self::BACKEND_TO_FRONTEND => array(
                    'groups:(\d+)',
                    function (array $matches) {
                        return 'Group ' . ($matches[1] + 1);
                    }
                ),
            ),
            // accounts (OroCRMAccountBundle:Account)
            'Numeric Account' => array(
                self::FRONTEND_TO_BACKEND => array(
                    'Account (\d+)',
                    function (array $matches) {
                        return 'accounts:' . ($matches[1] - 1);
                    }
                ),
                self::BACKEND_TO_FRONTEND => array(
                    'accounts:(\d+)',
                    function (array $matches) {
                        return 'Account ' . ($matches[1] + 1);
                    }
                ),
            ),
        );

        return array_merge($this->headerConversionRules, $complexConversionRules);
    }

    /**
     * {@inheritDoc}
     */
    protected function getBackendHeader()
    {
        return $this->headerProvider->getHeader();
    }
}
