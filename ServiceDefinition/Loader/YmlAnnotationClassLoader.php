<?php

namespace Oro\Bundle\SearchBundle\ServiceDefinition\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocatorInterface;
use Doctrine\Common\Annotations\Reader;

use BeSimple\SoapBundle\ServiceDefinition\Annotation;
use BeSimple\SoapBundle\ServiceDefinition as Definition;

class YmlAnnotationClassLoader extends FileLoader
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $reader;

    /**
     * @param \Symfony\Component\Config\FileLocatorInterface $locator
     * @param \Doctrine\Common\Annotations\Reader            $reader
     */
    public function __construct(FileLocatorInterface $locator, Reader $reader)
    {
        $this->reader = $reader;
        parent::__construct($locator);
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource
     * @param null  $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Loads a ServiceDefinition from annotations from a yml file.
     *
     * @param string  $file
     * @param string  $type
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\ServiceDefinition
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function load($file, $type = null)
    {
        $yml = Yaml::parse($file);

        $definition = new Definition\ServiceDefinition();

        foreach ($yml['classes'] as $class) {
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
            }

            $class = new \ReflectionClass($class);

            $serviceMethodHeaders = array();
            foreach ($this->reader->getClassAnnotations($class) as $annotation) {
                if ($annotation instanceof Annotation\Header) {
                    $serviceMethodHeaders[$annotation->getValue()] = $annotation;
                }
            }

            foreach ($class->getMethods() as $method) {
                $serviceArguments = $serviceHeaders = array();
                $serviceMethod = $serviceReturn = null;

                foreach ($serviceMethodHeaders as $annotation) {
                    $serviceHeaders[$annotation->getValue()] = new Definition\Header(
                        $annotation->getValue(),
                        $this->getArgumentType($method, $annotation)
                    );
                }

                foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                    if ($annotation instanceof Annotation\Header) {
                        $serviceHeaders[$annotation->getValue()] = new Definition\Header(
                            $annotation->getValue(),
                            $this->getArgumentType($method, $annotation)
                        );
                    } elseif ($annotation instanceof Annotation\Param) {
                        $serviceArguments[] = new Definition\Argument(
                            $annotation->getValue(),
                            $this->getArgumentType($method, $annotation)
                        );
                    } elseif ($annotation instanceof Annotation\Method) {
                        if ($serviceMethod) {
                            throw new \LogicException(sprintf(
                                '@Soap\Method defined twice for "%s".',
                                $method->getName()
                            ));
                        }

                        $serviceMethod = new Definition\Method(
                            $annotation->getValue(),
                            $this->getController($class, $method, $annotation)
                        );
                    } elseif ($annotation instanceof Annotation\Result) {
                        if ($serviceReturn) {
                            throw new \LogicException(sprintf(
                                '@Soap\Result defined twice for "%s".',
                                $method->getName()
                            ));
                        }

                        $serviceReturn = new Definition\Type($annotation->getPhpType(), $annotation->getXmlType());
                    }
                }

                if (!$serviceMethod && (!empty($serviceArguments) || $serviceReturn)) {
                    throw new \LogicException(sprintf('@Soap\Method non-existent for "%s".', $method->getName()));
                }

                if ($serviceMethod) {
                    $serviceMethod->setArguments($serviceArguments);
                    $serviceMethod->setHeaders($serviceHeaders);

                    if (!$serviceReturn) {
                        throw new \LogicException(sprintf('@Soap\Result non-existent for "%s".', $method->getName()));
                    }

                    $serviceMethod->setReturn($serviceReturn);

                    $definition->getMethods()->add($serviceMethod);
                }
            }
        }

        return $definition;
    }

    /**
     * @param \ReflectionMethod                                       $method
     * @param \BeSimple\SoapBundle\ServiceDefinition\Annotation\Param $annotation
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Type
     */
    private function getArgumentType(\ReflectionMethod $method, Annotation\Param $annotation)
    {
        $phpType = $annotation->getPhpType();
        $xmlType = $annotation->getXmlType();

        if (null === $phpType) {
            foreach ($method->getParameters() as $param) {
                if ($param->name === $annotation->getName()) {
                    $phpType = $param->getClass()->name;

                    break;
                }
            }
        }

        return new Definition\Type($phpType, $xmlType);
    }

    /**
     * @param \ReflectionClass                                         $class
     * @param \ReflectionMethod                                        $method
     * @param \BeSimple\SoapBundle\ServiceDefinition\Annotation\Method $annotation
     *
     * @return string
     */
    private function getController(\ReflectionClass $class, \ReflectionMethod $method, Annotation\Method $annotation)
    {
        if (null !== $annotation->getService()) {
            return $annotation->getService() . ':' . $method->name;
        } else {
            return $class->name . '::' . $method->name;
        }
    }

}
