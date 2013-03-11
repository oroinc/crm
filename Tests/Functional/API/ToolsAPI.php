<?php
namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

class ToolsAPI extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for REST/SOAP API tests
     *
     * @param $folder
     *
     * @return array
     */
    public static function requestsApi($folder)
    {
        $parameters = array();
        $testFiles = new \RecursiveDirectoryIterator(
            __DIR__ . DIRECTORY_SEPARATOR . $folder,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        foreach ($testFiles as $fileName => $object) {
            $parameters[$fileName] = Yaml::parse($fileName);
            if (is_null($parameters[$fileName]['response'])) {
                unset($parameters[$fileName]['response']);
            }
        }
        return
            $parameters;
    }

    /**
     * Test API response
     *
     * @param array $response
     * @param array $result
     */
    public static function assertEqualsResponse($response, $result)
    {
        self::assertEquals($response['return'], $result);
    }

    /**
     * Convert stdClass to array
     *
     * @param $class
     * @return array
     */
    public static function classToArray($class)
    {
        return json_decode(json_encode($class), true);
    }
}
