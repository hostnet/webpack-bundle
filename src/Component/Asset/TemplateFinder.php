<?php
/**
 * @copyright 2019-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\Webpack\Asset;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

class TemplateFinder
{
    private $kernel;
    private $root_dir;
    private $templates;

    public function __construct(KernelInterface $kernel, string $root_dir)
    {
        $this->kernel   = $kernel;
        $this->root_dir = $root_dir;
    }

    /**
     * @return TemplateReferenceInterface[]
     */
    public function findAllTemplates(): array
    {
        if (null !== $this->templates) {
            return $this->templates;
        }

        $templates = [];

        foreach ($this->kernel->getBundles() as $bundle) {
            $templates = array_merge($templates, $this->findTemplatesInBundle($bundle));
        }

        $templates = array_merge($templates, $this->findTemplatesInFolder($this->root_dir . '/views'));

        return $this->templates = $templates;
    }

    /**
     * @param string $directory
     *
     * @return TemplateReferenceInterface[]
     */
    private function findTemplatesInFolder(string $directory): array
    {
        $templates = [];

        if (false === is_dir($directory)) {
            return $templates;
        }

        /** @var SplFileInfo $file */
        foreach ((new Finder())->files()->followLinks()->in($directory) as $file) {
            $template = $this->parse($file->getRelativePathname());
            if (false !== $template) {
                $templates[] = $template;
            }
        }

        return $templates;
    }

    /**
     * @param BundleInterface $bundle
     *
     * @return TemplateReferenceInterface[]
     */
    private function findTemplatesInBundle(BundleInterface $bundle): array
    {
        $name      = $bundle->getName();
        $templates = array_unique(array_merge(
            $this->findTemplatesInFolder($bundle->getPath() . '/Resources/views'),
            $this->findTemplatesInFolder($this->root_dir . '/' . $name . '/views')
        ));

        /** @var TemplateReferenceInterface $template */
        foreach ($templates as $i => $template) {
            $templates[$i] = $template->set('bundle', $name);
        }

        return $templates;
    }

    /**
     * @param string $file_name
     *
     * @return TemplateReference|false
     */
    private function parse(string $file_name)
    {
        $parts = explode('/', str_replace('\\', '/', $file_name));

        $elements = explode('.', array_pop($parts));
        if (3 > \count($elements)) {
            return false;
        }

        $engine = array_pop($elements);
        $format = array_pop($elements);

        return new TemplateReference('', implode('/', $parts), implode('.', $elements), $format, $engine);
    }
}
