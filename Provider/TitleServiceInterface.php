<?php

namespace Oro\Bundle\NavigationBundle\Provider;

interface TitleServiceInterface
{
    /**
     * Return rendered translated title
     *
     * @param array $params
     * @param null $title
     * @param null $prefix
     * @param null $suffix
     * @return $this
     */
    public function render($params = array(), $title = null, $prefix = null, $suffix = null);

    /**
     * Render serialized title
     *
     * @param string $titleData
     * @return string
     */
    public function renderStored($titleData);

    /**
     * Load title template from database
     *
     * @param string $route
     */
    public function loadByRoute($route);

    /**
     * Return serialized title data
     *
     * @return string
     */
    public function getSerialized();
}
