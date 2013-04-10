<?php
/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Franklinsports_Tiers
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

namespace Oro\Bundle\NavigationBundle\Twig;


class TitleNode extends \Twig_Node
{
    public function __construct(\Twig_Node $expr = null, $lineno = 0, $tag = null)
    {
        parent::__construct(array('expr' => $expr), array(), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $node = $this->getNode('expr');

//        foreach ($node as $argument) {
//
//        }

//        $string = $this->compileString($this->getNode('expr'));

////        $compiler
//            ->write('$this->env->getExtension("oro_title")->set(array("params" => array("%user%" => "FROM TOKEN")));')
//            ->raw("\n");
    }
}
