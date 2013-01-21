<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Scopable container interface, implemented by class which can't be scoped but contains
 * some other scopable content, for instance, a flexible entity is not scopable itself
 * but its values should be
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface ScopableContainerInterface
{

    /**
     * Get used scope
     * @return string $scope
     */
    public function getScope();

    /**
     * Set used scope
     * @param string $scope
     *
     * @return ScopableContainerInterface
     */
    public function setScope($scope);

}
