<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Doctrine\Common\Util\Inflector;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class AttributesConverterHelper
{
    const ATTRIBUTES_KEY = 'attributes';
    const KEY = 'key';
    const VALUE = 'value';
    const ID_MARK = '_id';
    const CHANNEL_KEY = 'channel';

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

        if (!empty($importedRecord[self::ATTRIBUTES_KEY])) {
            foreach ($importedRecord[self::ATTRIBUTES_KEY] as $attribute) {
                $name = $attribute[self::KEY];
                $value = $attribute[self::VALUE];

                $isIdentifier = substr($name, -strlen(self::ID_MARK)) === self::ID_MARK;
                if ($isIdentifier && $channelId) {
                    $name = Inflector::camelize($name);
                    $importedRecord = self::addAttribute($importedRecord, $name, $value);

                    $name = substr($name, 0, strlen($name) - strlen(self::ID_MARK) + 1);
                    $value = ['originId' => $value, self::CHANNEL_KEY => ['id' => $channelId]];
                }

                $name = Inflector::camelize($name);
                $importedRecord = self::addAttribute($importedRecord, $name, $value);
            }
            unset($importedRecord[self::ATTRIBUTES_KEY]);
        }

        return $importedRecord;
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
}
