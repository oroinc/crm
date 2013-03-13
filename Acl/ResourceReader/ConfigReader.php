<?php
namespace Oro\Bundle\UserBundle\Acl\ResourceReader;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\UserBundle\Annotation\Acl;

class ConfigReader
{
    /**
     * @var array
     */
    private $bundles;

    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * Get ACL Resources array from config files
     *
     * @return \Oro\Bundle\UserBundle\Annotation\Acl[]
     */
    public function getConfigResources()
    {
        $aclResources = array();
        $aclConfig = $this->getConfigAclArray();
        if (count($aclConfig)) {
            foreach ($aclConfig as $id => $acl) {
                $aclObject = new Acl(
                    array(
                         'id'          => $id,
                         'name'        => $acl['name'],
                         'description' => $acl['description'],
                         'parent'      => isset($acl['parent']) ? $acl['parent'] : false
                    )
                );
                $aclObject->setMethod($acl['method']);
                $aclObject->setClass($acl['class']);
                $aclResources[$id] = $aclObject;
            }
        }

        return $aclResources;
    }

    /**
     * Get
     *
     * @param string $className
     * @param string $methodName
     *
     * @return bool|string
     */
    public function getMethodAclId($className, $methodName)
    {
        $aclConfig = $this->getConfigAclArray();
        foreach ($aclConfig as $id => $acl) {
            if ($acl['class'] == $className && $acl['method'] == $methodName) {
                return $id;
            }
        }

        return false;
    }

    /**
     * Get ACL array from config files
     *
     * @return array
     */
    protected function getConfigAclArray()
    {
        $aclConfig = array();
        foreach ($this->bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/acl.yml')) {
                $aclConfig += Yaml::parse(realpath($file));
            }
        }

        return $aclConfig;
    }
}
