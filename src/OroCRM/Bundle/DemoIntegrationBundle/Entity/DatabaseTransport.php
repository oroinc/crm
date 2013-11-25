<?php

namespace OroCRM\Bundle\DemoIntegrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Class DatabaseTransport
 *
 * @package OroCRM\Bundle\DemoIntegrationBundle\Entity
 * @ORM\Entity
 */
class DatabaseTransport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255, nullable=false)
     */
    protected $host;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=255, nullable=false)
     */
    protected $login;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="db_name", type="string", length=255, nullable=false)
     */
    protected $dbName;

    /**
     * @param string $dbName
     *
     * @return $this
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $login
     *
     * @return $this
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        return new ParameterBag(
            [
                'host'          => $this->getHost(),
                'login'         => $this->getLogin(),
                'password'      => $this->getPassword(),
                'database_name' => $this->getDbName(),
            ]
        );
    }
}
