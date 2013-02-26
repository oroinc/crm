<?php
namespace Oro\Bundle\SearchBundle\Twig;

class HighlightExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'highlight' => new \Twig_Filter_Method($this, 'highlight'),
        );
    }

    public function highlight($text, $searchString)
    {
        $text = strip_tags($text);
        $searchArray = explode(' ', $searchString);
        foreach ($searchArray as $searchWord) {
            $text = preg_replace("/\w*?$searchWord\w*/i", "<strong>$0</strong>", $text);
        }
        return $text;
    }

    public function getName()
    {
        return 'highlight_extension';
    }
}