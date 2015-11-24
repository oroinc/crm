<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use ReflectionClass;
use ReflectionProperty;

use Doctrine\Common\Util\Inflector;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class AttributesConverterHelper
{
    const ATTRIBUTES_KEY = 'attributes';
    const KEY = 'key';
    const VALUE = 'value';
    const ID_MARK = '_id';
    const CHANNEL_KEY = 'channel';
    const ENTITY_NAME_KEY = 'entityName';

    /**
     * @param array $importedRecord
     * @param ContextInterface|null $context
     * @return array
     */
    public static function addUnknownAttributes(array $importedRecord, ContextInterface $context = null)
    {
        $channelId = null;
        if ($context && $context->hasOption(self::CHANNEL_KEY)) {
            $channelId = $context->getOption(self::CHANNEL_KEY);
        }

        $addedAttributes = [];
        if (!empty($importedRecord[self::ATTRIBUTES_KEY])) {
            foreach ($importedRecord[self::ATTRIBUTES_KEY] as $attribute) {
                $name = $attribute[self::KEY];
                $value = $attribute[self::VALUE];

                $isIdentifier = substr($name, -strlen(self::ID_MARK)) === self::ID_MARK;
                if ($isIdentifier && $channelId) {
                    $name = Inflector::camelize($name);
                    $importedRecord = self::addAttribute($importedRecord, $name, $value);
                    $addedAttributes[] = $name;

                    $name = substr($name, 0, strlen($name) - strlen(self::ID_MARK) + 1);
                    $value = ['originId' => $value, self::CHANNEL_KEY => ['id' => $channelId]];
                }

                $name = Inflector::camelize($name);
                $importedRecord = self::addAttribute($importedRecord, $name, $value);
                $addedAttributes[] = $name;
            }
            unset($importedRecord[self::ATTRIBUTES_KEY]);
        }

        return static::normalizeProperties($importedRecord, $addedAttributes, $context);
    }

    /**
     * @param array $importedRecord
     * @param string $name
     * @param string $value
     * @return array
     */
    public static function addAttribute(array $importedRecord, $name, $value)
    {
        if (!array_key_exists($name, $importedRecord)) {
            $importedRecord[$name] = $value;
        }

        return $importedRecord;
    }

    /**
     * Converts properties into correct case
     *
     * @param array $record
     * @param array $properties
     * @param ContextInterface|null $context
     *
     * @return array Normalized record
     */
    public static function normalizeProperties(array $record, array $properties, ContextInterface $context = null)
    {
        if (!$properties || !$context || !$context->hasOption(static::ENTITY_NAME_KEY)) {
            return $record;
        }

        $class = $context->getOption(static::ENTITY_NAME_KEY);
        $classRef = new ReflectionClass($class);
        $classProperties = array_map(
            function (ReflectionProperty $prop) {
                return $prop->getName();
            },
            $classRef->getProperties()
        );

        $classPropertyMap = array_combine(array_map('strtolower', $classProperties), $classProperties);

        $result = [];
        foreach ($record as $key => $value) {
            $lkey = strtolower($key);
            if (in_array($key, $properties) && array_key_exists($lkey, $classPropertyMap)) {
                $result[$classPropertyMap[$lkey]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
