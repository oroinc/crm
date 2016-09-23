<?php

namespace Oro\Bundle\MagentoBundle\Utils;

class WSIUtils
{
    /**
     * Do modifications with response for collection requests
     * Fix issues related to specific results in WSI mode
     *
     * @param mixed $response
     *
     * @return array
     */
    public static function processCollectionResponse($response)
    {
        if (!is_array($response)) {
            if ($response && is_object($response)) {
                // response is object, but might be empty in case when no data in WSI mode
                $data = get_object_vars($response);
                if (empty($data)) {
                    $response = [];
                } else {
                    // single result in WSI mode
                    $response = [$response];
                }
            } else {
                // for empty results in Soap V2
                $response = [];
            }
        }

        return $response;
    }

    /**
     * @param array $response
     * @return array
     */
    public static function convertResponseToMultiArray($response)
    {
        if (is_array($response) && array_key_exists(0, $response) === false && count($response) > 0) {
            return [$response];
        }

        return $response;
    }

    /**
     * Parse WSI response and nested data
     *
     * @param mixed $result
     * @param bool  $defaultNull if not found in result node return null
     *
     * @return null
     */
    public static function parseWSIResponse($result, $defaultNull = true)
    {
        if (is_object($result)) {
            if (!empty($result->result)) {
                $result = $result->result;
            }

            if (isset($result->complexObjectArray)) {
                $result = $result->complexObjectArray;
            }

            $objectArray = is_array($result) ? $result : [$result];
            foreach ($objectArray as $singleObject) {
                if (is_object($singleObject)) {
                    $vars = get_object_vars($singleObject);

                    foreach ($vars as $var => $value) {
                        $singleObject->$var = self::parseWSIResponse($singleObject->$var, false);
                    }
                }
            }
        } elseif ($defaultNull) {
            $result = null;
        }

        return $result;
    }
}
