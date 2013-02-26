<?php

namespace Oro\Bundle\SoapBundle\Form;

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
            if (!is_null($value)) {
                $fields[preg_replace('/[^a-z]+/i', '', $field)] = $value;
            }
        }

        $entity = str_replace('Soap', '', get_class($data));

        // check if entity has flexible attributes
        if (array_key_exists('attributes', $fields)) {
            $attrDef = $this->om->getRepository('OroFlexibleEntityBundle:Attribute')->findBy(array('entityType' => $entity));
            $attrVal = new \SimpleXMLElement($this->request->getSoapMessage());
            $attrVal = $attrVal->xpath('//attributes/*');
            $i       = 0;

            $fields['attributes'] = array();

            if (!empty($attrVal)) {
                // transform simple notation into FlexibleType format
                foreach ($attrVal as $field) {
                    foreach ($attrDef as $attr) {
                        if ($attr->getCode() == $field->getName()) {
                            $fields['attributes'][$i]['id']   = $attr->getId();
                            $fields['attributes'][$i]['data'] = (string) $field;

                            $i++;
                        }
                    }
                }
            }
        }

        $this->request->request->set($name, $fields);
    }
}
