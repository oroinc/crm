<?php
namespace Oro\Bundle\DataFlowBundle\Source;

use Oro\Bundle\DataFlowBundle\Exception\CurlException;

/**
 * Get content from a file with HTTP protocol
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class HttpSource extends Source
{

    /**
     * Url from which download content
     * @var string
     */
    protected $url;

    /**
     * Pathfile where store remote content
     * @var string
     */
    protected $path;

    /**
     * Username if authentication is needed
     * @var string
     */
    protected $username;

    /**
     * Password if authentication is needed
     * @var string
     */
    protected $password;

    /**
     * Constructor
     * @param string $url      HTTP url where retrieving content wanted
     * @param string $path     Path file to write url content
     * @param string $username Username if authentication is required
     * @param string $password Password if authentication is required
     */
    public function __construct($url, $path, $username = null, $password = null)
    {
        $this->url = $url;
        $this->path = $path;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Getter path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        $this->preFilter();

        $this->download();

        $this->postFilter();
    }

    /**
     * Download content from url and write it to locale file defined
     * @throws \Exception
     *
     * @return \SplFileObject
     */
    protected function download()
    {
//         $handle = fopen($this->path, 'w+');

//         $curl = curl_init($this->url);
//         if (!$curl) {
//             throw new CurlException('Curl not initialize. Verify curl library is enabled or requested url is correct');
//         }

//         // if authentication required
//         if ($this->username && $this->password) {
//             curl_setopt($curl, CURLOPT_USERPWD, $this->username .':'. $this->password);
//         }

//         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//         curl_setopt($curl, CURLOPT_FILE, $handle);
//         $data = curl_exec($curl);

//         if ($data === false) {
//             throw new CurlException('Curl error : '. curl_error($curl));
//         }

//         curl_close($curl);
//         fclose($handle);

        if (!$this->url) {
            throw new \Exception('url not defined');
        }

        $target = tempnam('/tmp', 'data_import');

        if ($this->username && $this->password) {
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic "
                    . base64_encode("{$this->username}:{$this->password}")
                )
            ));
        }

        file_put_contents($target, file_get_contents($this->url, false, $context));

        return new \SplFileObject($target);
    }

}