<?php

namespace Oro\Bundle\SoapBundle\Entity;

use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

class RequestFix
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     *
     * @param Request       $request
     * @param ObjectManager $om      Entity manager to handle flexible entities
     */
    public function __construct(Request $request, ObjectManager $om)
    {
        $this->request = $request;
        $this->om      = $om;
    }

    /**
     * Fix Request object so forms can be handled correctly
     *
     * @param string $name Form name
     */
    public function fix($name)
    {
        $data = $this->request->get($name);

        if (!is_object($data)) {
            return;
        }

        $fields = array();

        foreach ((array) $data as $field => $value) {
            // special case for ordered arrays
            if ($value instanceof \stdClass && isset($value->item) && is_array($value->item)) {
                $value = (array) $value->item;
            }

            if (!is_null($value)) {
                $fields[preg_replace('/[^a-z]+/i', '', $field)] = $value;
            }
        }

        $entity  = str_replace('Soap', '', get_class($data));
        $attrDef = $this->om->getRepository('OroFlexibleEntityBundle:Attribute')->findBy(array('entityType' => $entity));

        if (isset($fields['attributes'])) {
            $attrVal = $fields['attributes'];

            $fields['attributes'] = array();
        } else {
            $attrVal = array();
        }

        // transform SOAP array notation into FlexibleType format
        foreach ($attrDef as $i => $attr) {
            /* @var $attr \Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute */
            if ($attr->getBackendType() == 'options') {
                $type    = 'option';
                $default = $attr->getOptions()->offsetGet(0)->getId();
            } else {
                $type    = 'data';
                $default = null;
            }

            $fields['attributes'][$i]        = array();
            $fields['attributes'][$i]['id']  = $attr->getId();
            $fields['attributes'][$i][$type] = $default;

            foreach ($attrVal as $field) {
                if ($attr->getCode() == (string) $field->code) {
                    $fields['attributes'][$i][$type] = (string) $field->value;

                    break;
                }
            }
        }

        $this->request->request->set($name, $fields);
    }
}
