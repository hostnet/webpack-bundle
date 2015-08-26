<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Node;
use Hostnet\Bundle\WebpackBundle\Twig\TwigExtension;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class WebpackNode extends \Twig_Node
{
    /** {@inheritdoc} */
    public function compile(\Twig_Compiler $compiler)
    {
        foreach ($this->getAttribute('files') as $file) {
            $compiler->write('$context["asset"] = "'. $file .'";');
            $this->nodes[0]->compile($compiler);
        }
    }
}
