<?php
namespace Oro\Bundle\NavigationBundle\Title\TitleReader;

use Doctrine\Common\Annotations\Reader as CommonAnnotationsReader;

use JMS\DiExtraBundle\Finder\PatternFinder;
use Symfony\Component\HttpKernel\KernelInterface;

class AnnotationsReader extends Reader
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    const ANNOTATION_CLASS = 'Oro\Bundle\NavigationBundle\Annotation\TitleTemplate';

    public function __construct(KernelInterface $kernel, CommonAnnotationsReader $reader)
    {
        parent::__construct($kernel);

        $this->reader = $reader;
    }

    /**
     * Get Route/Title information from controller annotations
     *
     * @return array()
     */
    public function getData()
    {
        $directories = $this->getScanDirectories();
        if (!$directories) {
            return array();
        }


        $finder = new PatternFinder(self::ANNOTATION_CLASS, '*.php');
        $files = $finder->findFiles($directories);

        foreach ($files as $index => $file) {
            if (strpos($file, 'AnnotationsReader') !== false || strpos($file, 'Annotation') !== false) {
                unset($files[$index]);
            }
        }

        return $this->findTitlesAnnotations($files);
    }

    /**
     * Get array with titles from annotations
     *
     * @param array $files
     *
     * @return array()
     */
    private function findTitlesAnnotations(array $files)
    {
        $titles = array();

        foreach ($files as $file) {
            $className = $this->getClassName($file);
            $reflection = new \ReflectionClass($className);

            //read annotations from methods
            foreach ($reflection->getMethods() as $reflectionMethod) {
                $title = $this->reader->getMethodAnnotation($reflectionMethod, self::ANNOTATION_CLASS);
                if (is_object($title)) {
                    $titles[$this->getDefaultRouteName($reflection, $reflectionMethod)] = $title->getTitleTemplate();
                }
            }
        }

        return $titles;
    }

    /**
     * Gets the default route name for a class method.
     *
     * @param \ReflectionClass  $class
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    private function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $name = strtolower(str_replace('\\', '_', $class->name).'_'.$method->name);

        return preg_replace(
            array(
                '/(bundle|controller)_/',
                '/action(_\d+)?$/',
                '/__/'
            ),
            array(
                '_',
                '\\1',
                '_'
            ),
            $name
        );
    }

    /**
     * Only supports one namespaced class per file
     *
     * @throws \RuntimeException if the class name cannot be extracted
     *
     * @param string $filename
     *
     * @return string the fully qualified class name
     */
    private function getClassName($filename)
    {
        $src = file_get_contents($filename);

        if (!preg_match('/\bnamespace\s+([^;]+);/s', $src, $match)) {
            throw new \RuntimeException(sprintf('Namespace could not be determined for file "%s".', $filename));
        }
        $namespace = $match[1];

        if (!preg_match('/\bclass\s+([^\s]+)\s+(?:extends|implements|{)/s', $src, $match)) {
            throw new \RuntimeException(sprintf('Could not extract class name from file "%s".', $filename));
        }

        return $namespace . '\\' . $match[1];
    }
}
