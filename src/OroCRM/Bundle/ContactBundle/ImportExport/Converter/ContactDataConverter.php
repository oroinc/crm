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
        'Owner First Name'       => 'owner:firstName',
        'Owner Last Name'        => 'owner:lastName',
        'Assigned To First Name' => 'assignedTo:firstName',
        'Assigned To Last Name'  => 'assignedTo:lastName',
        // contact typed addresses (OroCRMContactBundle:ContactAddress)
        'Address Label'       => 'addresses:0:label',
        'Address First Name'  => 'addresses:0:firstName',
        'Address Last Name'   => 'addresses:0:lastName',
        'Address Street'      => 'addresses:0:street',
        'Address Street2'     => 'addresses:0:street2',
        'Address City'        => 'addresses:0:city',
        'Address Region'      => 'addresses:0:region',
        'Address Region Text' => 'addresses:0:regionText',
        'Address Country'     => 'addresses:0:country',
        'Address Postal Code' => 'addresses:0:postalCode',
        'Address Type'        => 'addresses:0:types:0',
        'Address Numeric Type' => array(
            self::FRONTEND_TO_BACKEND => array('Address Type (\d+)', 'addresses:0:types:$1'),
            self::BACKEND_TO_FRONTEND => array('addresses:0:types:(\d+)', 'Address Type $1'),
        ),
        'Numeric Address Label' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Label', 'addresses:$1:label'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):label', 'Address $1 Label'),
        ),
        'Numeric Address First Name' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) First Name', 'addresses:$1:firstName'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):firstName', 'Address $1 First Name'),
        ),
        'Numeric Address Last Name' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Last Name', 'addresses:$1:lastName'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):lastName', 'Address $1 Last Name'),
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
        'Numeric Address Type' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Type', 'addresses:$1:types:0'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):types:0', 'Address $1 Type'),
        ),
        'Numeric Address Numeric Type' => array(
            self::FRONTEND_TO_BACKEND => array('Address (\d+) Type (\d+)', 'addresses:$1:types:$2'),
            self::BACKEND_TO_FRONTEND => array('addresses:(\d+):types:(\d+)', 'Address $1 Type $2'),
        ),
        // contact emails (OroCRMContactBundle:ContactEmail)
        'Email' => 'emails:0',
        'Numeric Email' => array(
            self::FRONTEND_TO_BACKEND => array('Email (\d+)', 'emails:$1'),
            self::BACKEND_TO_FRONTEND => array('emails:(\d+)', 'Email $1'),
        ),
        // contact phones (OroCRMContactBundle:ContactPhone)
        'Phone' => 'phones:0',
        'Numeric Phone' => array(
            self::FRONTEND_TO_BACKEND => array('Phone (\d+)', 'phones:$1'),
            self::BACKEND_TO_FRONTEND => array('phones:(\d+)', 'Phone $1'),
        ),
        // contact groups (OroCRMContactBundle:Group)
        'Group' => 'groups:0',
        'Numeric Group' => array(
            self::FRONTEND_TO_BACKEND => array('Group (\d+)', 'groups:$1'),
            self::BACKEND_TO_FRONTEND => array('groups:(\d+)', 'Group $1'),
        ),
        // accounts (OroCRMAccountBundle:Account)
        'Account' => 'accounts:0',
        'Numeric Account' => array(
            self::FRONTEND_TO_BACKEND => array('Account (\d+)', 'accounts:$1'),
            self::BACKEND_TO_FRONTEND => array('accounts:(\d+)', 'Account $1'),
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
        return $this->headerConversionRules;
    }

    /**
     * {@inheritDoc}
     */
    protected function getBackendHeader()
    {
        return $this->headerProvider->getHeader();
    }
}
