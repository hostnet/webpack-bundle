<?php
namespace Hostnet\Component\WebpackBridge\Asset;

use Hostnet\Component\WebpackBridge\Profiler\Profiler;
use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinderInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Asset Tracker
 *
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
class Tracker
{
    /**
     * @var Profiler
     */
    private $profiler;

    /**
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
     * @var string
     */
    private $cache_file;

    /**
     * @var string
     */
    private $root_dir;

    /**
     * Collection of tracked paths. This consists of directories as well as files.
     *
     * @var string[]
     */
    private $paths;

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * Asset directory to resolve assets from. This directory is resolved relative from the bundle path.
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
     * A list of untracked templates that can't be resolved to an absolute path. These will be shown in the profiler
     * as a warning to the developer.
     *
     * @var string[]
     */
    private $untracked_templates = [];

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * @param Profiler                $profiler
     * @param TemplateFinderInterface $finder
     * @param string                  $cache_dir
     * @param string                  $root_dir
     * @param string                  $asset_dir
     * @param array                   $bundle_paths
     */
    public function __construct(
        Profiler                $profiler,
        TemplateFinderInterface $finder,
        /* string */            $cache_dir,
        /* string */            $root_dir,
        /* string */            $asset_dir,
        array                   $bundle_paths = []
    ) {
        $this->profiler     = $profiler;
        $this->finder       = $finder;
        $this->cache_file   = rtrim($cache_dir, "\\/") . DIRECTORY_SEPARATOR . 'webpack.asset_tracker.cache';
        $this->root_dir     = $root_dir;
        $this->asset_dir    = $asset_dir;
        $this->bundle_paths = $bundle_paths;
    }

    /**
     * @param  string $path
     * @return Tracker
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
     * @return bool
     */
    public function isOutdated()
    {
        $this->boot();

        // If there is no cache file, presume the cache is outdated.
        if (! file_exists($this->cache_file)) {
            $this->profiler->set('tracker.reason', 'Cache data not present.');

            return true;
        }

        // The cache holds last modified timestamps indexed by absolute file path.
        $cache = json_decode(file_get_contents($this->cache_file), true);
        $files = [];

        foreach ($this->paths as $path) {
            $files = array_merge($files, $this->scan($path));
        }

        // If the length of the arrays don't match; something has changed.
        if (count($cache) !== count($files)) {
            $this->profiler->set('tracker.reason', 'A file has been added, moved or deleted.');

            return true;
        }

        // Iterate over the files and cross-reference their modification times with the cached entries.
        foreach ($files as $file => $mtime) {
            // Is the file new?
            if (! isset($cache[$file])) {
                $this->profiler->set('tracker.reason', 'The file "' . $file . '" has been added.');

                return true;
            }

            // Is the file modified recently?
            if ($mtime > $cache[$file]) {
                $this->profiler->set('tracker.reason', 'The file "' . $file . '" has been modified.');

                return true;
            }
        }
        $this->profiler->set('tracker.reason', false);

        return false;
    }

    /**
     * Rebuilds the tracker cache.
     */
    public function rebuild()
    {
        $this->boot();

        $files = [];

        foreach ($this->paths as $path) {
            $files = array_merge($files, $this->scan($path));
        }

        $this->profiler->set('tracker.file_count', count($files));
        file_put_contents($this->cache_file, json_encode($files));
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Returns a list of twig templates that are being tracked.
     *
     * @return string[]
     */
    public function getTemplates()
    {
        $this->boot();

        return $this->templates;
    }

    /**
     * Returns an associative array of file modification times indexed by absolute file paths.
     *
     * @return array
     */
    public function getCacheEntries()
    {
        return file_exists($this->cache_file)
            ? json_decode(file_get_contents($this->cache_file), true)
            : [];
    }

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
     * Returns an associative array of file modification times indexed by absolute filename.
     *
     * @param  string $dir
     * @param  array  $files
     * @return array
     */
    private function scan($dir, $files = [])
    {
        if (is_file($dir)) {
            return [$dir => filemtime($dir)];
        }

        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $file) {
            if (is_file($file) && is_readable($file)) {
                $files[$file] = filemtime($file);
                continue;
            }

            $files = array_merge($files, $this->scan($file));
        }

        return $files;
    }

    /**
     * @param  string $path
     * @return bool|string
     */
    public function resolvePath($path)
    {
        // Find and replace the @BundleName with the absolute path to the bundle.
        preg_match('/@(\w+)/', $path, $matches);
        if (isset($matches[0]) && isset($matches[1])) {
            $resolved_path = realpath(str_replace($matches[0], $this->bundle_paths[$matches[1]], $path));
            return $resolved_path;
        }

        // The path doesn't contain a bundle name. In this case it must exist in %kernel.root_dir%/Resources
        $path2 = $this->root_dir . DIRECTORY_SEPARATOR . trim($this->asset_dir, "\\/") . DIRECTORY_SEPARATOR . $path;
        if (file_exists($path2 )) {
            return $path2;
        }

        return false;
    }

    public function resolveResourcePath($path)
    {
        preg_match('/@(\w+)/', $path, $matches);
        if (isset($matches[0]) && isset($matches[1])) {
            $template = realpath(str_replace(
                $matches[0],
                $this->bundle_paths[$matches[1]] . DIRECTORY_SEPARATOR . trim($this->asset_dir, "\\/"),
                $path
            ));
            return $template;
        }

        return $path;
    }

    /**
     * Adds twig templates to the tracker.
     *
     * @param  TemplateReferenceInterface $reference
     * @return Tracker
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

        // Can't resolve the template. This shouldn't happen, unless somebody placed a template in a very weird place.
        $this->untracked_templates[] = $reference->getPath();

        return $this;
    }
}
