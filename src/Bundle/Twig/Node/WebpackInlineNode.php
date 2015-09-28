<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Node;

/**
 * @author Yannick de Lange <yannick.l.88@gmail.com>
 */
class WebpackInlineNode extends \Twig_Node
{
    /**
     * @param array  $attributes An array of attributes (should not be nodes)
     * @param int    $lineno     The line number
     * @param string $tag        The tag name associated with the Node
     */
    public function __construct(array $attributes = array(), $lineno = 0, $tag = null)
    {
        parent::__construct([], $attributes, $lineno, $tag);
    }

    /** {@inheritdoc} */
    public function compile(\Twig_Compiler $compiler)
    {
        if (false !== ($file = $this->getAttribute('js_file'))) {
            $compiler
                ->write('echo ')
                ->string('<script type="text/javascript" src="'. $file .'"></script>')
                ->raw(";\n");
        }
        if (false !== ($file = $this->getAttribute('css_file'))) {
            $compiler
                ->write('echo ')
                ->string('<link rel="stylesheet" href="'. $file .'">')
                ->raw(";\n");
        }
    }
}
