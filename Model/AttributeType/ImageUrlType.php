<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\AttributeType;

/**
 * Image attribute type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ImageUrlType extends FileUrlType
{

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->name     = 'Image Url';
    }
}
