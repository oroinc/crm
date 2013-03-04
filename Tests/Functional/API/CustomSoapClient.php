<?php
namespace Oro\Bundle\UserBundle\Tests\Functional\API;

class CustomSoapClient extends \SoapClient
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    public $client;

    /**
     * Overridden constructor
     *
     * @param string $wsdl
     * @param array $options
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     */
    public function __construct($wsdl, $options, $client)
    {
        // save custom transport layer
        $this->client = $client;
        //save wsdl as temporary file
        $file=tempnam(sys_get_temp_dir(), date("Ymd") . '_') . '.xml';
        $fl = fopen($file, "w");
        fwrite($fl, $wsdl);
        fclose($fl);
        //parent constructor
        parent::__construct($file, $options);
        //delete temporary file
        unlink($file);
    }

    /**
     * Overridden _doRequest method
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     *
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        //ob_start();
        //save directly in _SERVER array
        $_SERVER['HTTP_SOAPACTION'] = $action;
        $_SERVER['CONTENT_TYPE'] = 'application/soap+xml';
        //make POST request
        $this->client->request('POST', $location, array(), array(), array(), $request);
        //ob_end_clean();
        unset($_SERVER['HTTP_SOAPACTION']);
        unset($_SERVER['CONTENT_TYPE']);
        return $this->client->getResponse()->getContent();
    }
}
