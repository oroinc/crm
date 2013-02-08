<?php
namespace Oro\Bundle\DataFlowBundle\Source;

use Ddeboer\DataImport\Source\Http;

/**
 * Extended class for Http data-import implementation.
 * Fix authentication problem.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class HttpSource extends Http
{

    /**
     * {@inheritdoc}
     */
    public function downloadFile($target = null)
    {
        // define temporary file if necessary
        if (!$target) {
            $target = tempnam('/tmp', 'data_import');
        }

        // prepare context for authentication
        if ($this->username && $this->password) {
            $context = stream_context_create(array(
                'http' => array(
                    'header'  => "Authorization: Basic ". base64_encode("{$this->username}:{$this->password}")
                )
            ));
        }

        // get remote content
        $content = (isset($context)) ? file_get_contents($this->url, false, $context) : file_get_contents($this->url);

        // put content in target file
        file_put_contents($target, $content);

        return new \SplFileObject($target);
    }

}
