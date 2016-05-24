<?php
namespace Hostnet\Component\Webpack\Asset;

use Hostnet\Component\Webpack\Profiler\Profiler;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Asset Tracker
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class Tracker
{

    /**
     * The key-value store used to present 'logging' in the symfony-profiler bar.
     *
     * @var Profiler
     */
    private $profiler;

    /**
     * Sevice used for finding all the templates which needs to be tracked.
     *
     * @var TemplateFinderInterface
     */
    private $finder;

    /**
     * Associative mapping between bundle names and their absolute paths.
     *
     * @var array
     */
    private $bundle_paths;

    /**
     * The '%kernel.root_dir%' root directory of the application the tracker is used in.
     *
     * @var string
     */
    private $root_dir;

    /**
     * Collection of tracked paths, this consists of directories as well as files.
     *
     * @var string[]
     */
    private $paths;

    /**
     *
     * @var array
     */
    private $aliases = [];

    /**
     * Asset directory to resolve assets from, this directory is resolved relative from the bundle path.
     *
     * @var string
     */
    private $asset_dir;

    /**
     * A list of twig templates in absolute paths.
     *
     * @var string[]
     */
    private $templates = [];

    /**
     * Is this Tracker 'runtime'-initialized.
     *
     * @var bool
     */
    private $booted = false;

    /**
     * The directory where the compiled resources are stored, used to determine the latest compilation-run time.
     *
     * @var string
     */
    private $output_dir;

    /**
     * Create new Tracker.
     *
     * @param Profiler $profiler key-value store used to present 'logging' in the symfony-profiler bar.
     * @param TemplateFinderInterface $finder used to find all the templates which needs to be tracked.
     * @param string $root_dir '%kernel.root_dir%' root directory of the application.
     * @param string $asset_dir directory to resolve assets, directory is resolved relative from the bundle path.
     * @param string $output_dir The directory where the compiled resources are stored.
     * @param array $bundle_paths the optional associative mapping between bundle names and their absolute paths.
     */
    public function __construct(
        Profiler $profiler,
        TemplateFinderInterface $finder,
        /* string */ $root_dir,
        /* string */ $asset_dir,
        /* string */ $output_dir,
        array        $bundle_paths = []
    ) {
        $this->profiler     = $profiler;
        $this->finder       = $finder;
        $this->root_dir     = $root_dir;
        $this->asset_dir    = $asset_dir;
        $this->output_dir   = $output_dir;
        $this->bundle_paths = $bundle_paths;
    }

    /**
     * Add a path to the list of tracked paths (this can be both dir's or files).
     *
     * @param string $path the path to track.
     * @return Tracker this instance.
     */
    public function addPath($path)
    {
        if (empty($path) || false === ($real_path = realpath($path))) {
            throw new FileNotFoundException(null, 0, null, $path);
        }
        $this->paths[] = $real_path;

        return $this;
    }

    /**
     * Returns true if the cache is outdated.
     *
     * @return bool true, cache is outdated of non exsitant.
     */
    public function isOutdated()
    {
        $this->boot();

        $compiled_tracked_files = new TrackedFiles([$this->output_dir]);
        $current_tracked_files  = new TrackedFiles($this->paths);

        if ($current_tracked_files->modifiedAfter($compiled_tracked_files)) {
            $this->profiler->set('tracker.reason', 'One of the tracked files has been modified.');
            return true;
        }

        $this->profiler->set('tracker.reason', false);
        return false;
    }

    /**
     * Get the tracked aliases
     *
     * @return array the tracked aliases.
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Returns a list of twig templates that are being tracked.
     *
     * @return string[] list of twig templates.
     */
    public function getTemplates()
    {
        $this->boot();

        return $this->templates;
    }

    /**
     * Runtime initialize this tracker.
     */
    private function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        foreach ($this->finder->findAllTemplates() as $reference) {
            $this->addTemplate($reference);
        }

        foreach (array_keys($this->bundle_paths) as $name) {
            if (false !== ($resolved_path = $this->resolveResourcePath('@' . $name))) {
                $this->aliases['@' . $name] = $resolved_path;
                $this->addPath($resolved_path);
            }
        }

        $this->profiler->set('bundles', $this->aliases);
        $this->profiler->set('templates', $this->templates);
    }

    /**
     * Find the full path to a requested path, this can be bundle configurations like @BundleName/
     *
     * @param string $path the path to resolv.
     * @return string the full path to the requested resource or false if not found.
     */
    private function resolvePath($path)
    {
        // Find and replace the @BundleName with the absolute path to the bundle.
        $matches = [];
        preg_match('/@(\w+)/', $path, $matches);
        if (isset($matches[0], $matches[1], $this->bundle_paths[$matches[1]])) {
            return realpath(str_replace($matches[0], $this->bundle_paths[$matches[1]], $path));
        }

        // The path doesn't contain a bundle name. In this case it must exist in %kernel.root_dir%/Resources/views
        $path = $this->root_dir . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . $path;
        if (file_exists($path)) {
            return $path;
        }

        return false;
    }

    /**
     * Find the full path to a requested resource, this can be bundle configurations like @BundleName/resource.twig
     *
     * @param string $path the path resolve
     * @return string the full path to the requested resource or false if not found.
     */
    public function resolveResourcePath($path)
    {
        $matches = [];
        preg_match('/@(\w+)/', $path, $matches);
        if (isset($matches[0], $matches[1])) {
            if (!isset($this->bundle_paths[$matches[1]])) {
                return false;
            }
            $template = realpath(
                str_replace(
                    $matches[0],
                    $this->bundle_paths[$matches[1]] . DIRECTORY_SEPARATOR . trim($this->asset_dir, "\\/"),
                    $path
                )
            );
            return $template;
        }

        return $path;
    }

    /**
     * Adds twig templates to the tracker.
     *
     * @param TemplateReferenceInterface $reference the reference to the twig template to be added.
     * @return Tracker this instance
     */
    private function addTemplate(TemplateReferenceInterface $reference)
    {
        if ($reference->get('engine') !== 'twig') {
            return $this;
        }

        if (false !== ($path = $this->resolvePath($reference->getPath()))) {
            $this->templates[] = $path;
            return $this->addPath($path);
        }
        return $this;
    }
}
