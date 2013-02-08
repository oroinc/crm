<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Configuration;

use Oro\Bundle\DataFlowBundle\Configuration\DbalConfiguration;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class DbalConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Data provider
     *
     * @return multitype:multitype:multitype:multitype:string
     *
     * @static
     */
    public static function provider()
    {
        return array(
            array(self::prepareConfig('pdo_mysql', 'root', 'password', 'localhost', 'database', '3306')),
            array(self::prepareConfig('pdo_mysql', 'admin_mysql', '', 'host', 'dbname', '9999')),
            array(self::prepareConfig('pdo_mysql', 'admin', 'rootroot', '127.0.0.1', 'bap_db')),
            array(self::prepareConfig('pdo_mysql', 'admin', 'rootroot', '127.0.0.1', 'bap_db', '3306', 'UTF-8', 'TCP')),
            array(self::prepareConfig('pdo_sqlite', 'admin', 'rootroot', '127.0.0.1', 'bap_db', null, null, null, '/etc/sqlite/db/oro.sqlite')),
            array(self::prepareConfig('pdo_sqlite', 'root', '', '127.0.0.1', 'bap_db', null, null, null, null, true)),
            array(self::prepareConfig('pdo_pgsql', 'root', 'password', 'localhost', 'database', '5432')),
            array(self::prepareConfig('pdo_pgsql', 'admin_mysql', '', 'host', 'dbname', '9999')),
            array(self::prepareConfig('pdo_pgsql', 'admin', 'rootroot', '127.0.0.1', 'bap_db')),
            array(self::prepareConfig('pdo_oci', 'root', 'password', 'localhost', 'database', '1521')),
            array(self::prepareConfig('oci8', 'admin_mysql', '', 'host', 'dbname', '9999')),
            array(self::prepareConfig('pdo_oci', 'admin', 'rootroot', '127.0.0.1', 'bap_db')),
            array(self::prepareConfig('oci8', 'admin', 'rootroot', '127.0.0.1', 'bap_db', '1521', 'UTF-8')),
            array(self::prepareConfig('pdo_sqlsrv', 'root', 'password', 'localhost', 'database', '1433')),
            array(self::prepareConfig('pdo_sqlsrv', 'admin_mysql', '', 'host', 'dbname', '9999')),
            array(self::prepareConfig('pdo_sqlsrv', 'admin', 'rootroot', '127.0.0.1', 'bap_db')),
        );
    }

    /**
     * Data provider for exception
     * @return multitype:multitype:multitype:multitype:string
     */
    public static function exceptionProvider()
    {
        return array(
            array(self::prepareConfig('unknown_driver', 'root', 'rootroot')),
            array(self::prepareConfig('pdo_mysql', '', 'rootroot'))
        );
    }

    /**
     * Prepare data for data provider
     * @param string  $driver   Driver name
     * @param string  $username Username
     * @param string  $password Password
     * @param string  $host     Host
     * @param string  $dbname   Database name
     * @param string  $port     Port
     * @param string  $charset  Charset used by database
     * @param string  $socket   Unix socket
     * @param string  $path     Path for sqlite database
     * @param boolean $memory   Use memory for sqlite database
     *
     * @return multitype:multitype:multitype:string
     */
    protected static function prepareConfig($driver, $username, $password,
            $host = null, $dbname = null, $port = null,
            $charset = null, $socket = null, $path = null,
            $memory = null)
    {
        // define default parameters
        $parameters = self::prepareDefaultConfig($driver, $username, $password);

        if ($host !== null) {
            $parameters['host'] = $host;
        }
        if ($port !== null) {
            $parameters['port'] = $port;
        }
        if ($dbname !== null) {
            $parameters['dbname'] = $dbname;
        }
        if ($charset !== null) {
            $parameters['charset'] = $charset;
        }
        if ($socket !== null) {
            $parameters['unix_socket'] = $socket;
        }
        if ($path !== null) {
            $parameters['path'] = $path;
        }
        if ($memory !== null) {
            $parameters['memory'] = $memory;
        }

        return
            array(
                'database' => $parameters
        );
    }

    /**
     * Prepare default configuration for database
     * @param string $driver   Driver name
     * @param string $username Username
     * @param string $password Password
     *
     * @return multitype:string
     */
    protected static function prepareDefaultConfig($driver, $username, $password)
    {
        return array(
            'driver' => $driver,
            'username' => $username,
            'password' => $password
        );
    }

    /**
     * Test some configurations
     *
     * @param multitype $parameters
     *
     * @dataProvider provider
     */
    public function testValidation($parameters)
    {
        $dbalConfig = new DbalConfiguration($parameters);
        $dbalConfig->process();
    }

    /**
     * Test configuration exceptions
     *
     * @param multitype $parameters
     *
     * @dataProvider exceptionProvider
     *
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testConfigurationException($parameters)
    {
        $dbalConfig = new DbalConfiguration($parameters);
        $dbalConfig->process();
    }

}
