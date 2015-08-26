<?php
namespace Hostnet\Bundle\WebpackBundle\Twig\Token;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class JavascriptTokenParser extends TokenParser
{
    /**
     * Constant declaration for easy accessibility by the TwigParser component.
     *
     * @var string
     */
    const TAG = 'webpack_javascripts';

    /** {@inheritdoc} */
    public function getTag()
    {
        return self::TAG;
    }

    /** {@inheritdoc} */
    public function getAssetExtension()
    {
        return 'js';
    }
}
