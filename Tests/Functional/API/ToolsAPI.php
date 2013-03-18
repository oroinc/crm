<?php
namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Component\Yaml\Yaml;

class ToolsAPI extends \PHPUnit_Framework_TestCase
{
    protected static $random = null;
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
        //generate unique value
        if (is_null(self::$random)) {
            self::$random = self::randomGen(5);
        }

        foreach ($parameters as $key => $value) {
            array_walk(
                $parameters[$key]['request'],
                array(get_called_class(), 'replace'),
                self::$random
            );
            array_walk(
                $parameters[$key]['response'],
                array(get_called_class(), 'replace'),
                self::$random
            );
        }

        return
            $parameters;
    }

    /**
     * Test API response
     *
     * @param array $response
     * @param array $result
     * @param $debugInfo
     */
    public static function assertEqualsResponse($response, $result, $debugInfo = '')
    {
        self::assertEquals($response['return'], $result, $debugInfo);
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

    /**
     * @param $length
     * @return string
     */
    public static function randomGen($length)
    {
        $random= "";
        srand((double) microtime()*1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890-_";
        // Add the special characters to $char_list if needed

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($char_list, (rand()%(strlen($char_list))), 1);
        }
        self::$random = $random;

        return $random;
    }

    /**
     * @param $value
     * @param $key
     * @param $random
     */
    public static function replace(&$value, $key, $random)
    {
        if (!is_null($value)) {
            $value = str_replace('%str%', $random, $value);
        }
    }
}
