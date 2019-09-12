<?php
namespace Sitegeist\Bitzer\Infrastructure;

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\PackageManager;
use Neos\Utility\Files;

/**
 * A Fusion view for Bitzer
 */
class FusionView extends \Neos\Fusion\View\FusionView
{
    protected $autoIncludeFusionPattern = 'resource://%s/Private/Fusion/Root.fusion';

    /**
     * @Flow\InjectConfiguration(path="fusion.autoInclude")
     * @var array
     */
    protected $autoloadedPlugins;

    /**
     * @Flow\Inject
     * @var PackageManager
     */
    protected $packageManager;

    protected $fallbackView;

    /**
     * @var boolean
     */
    protected $fallbackViewEnabled = false;

    protected function loadFusion(): void
    {
        $mergedFusionCode = '';
        $fusionPathPatterns = $this->getOption('fusionPathPatterns');
        ksort($fusionPathPatterns);
        foreach ($fusionPathPatterns as $fusionPathPattern) {
            $fusionPathPattern = str_replace('@package', $this->getPackageKey(), $fusionPathPattern);
            $filePaths = array_merge(Files::readDirectoryRecursively($fusionPathPattern, '.fusion'), Files::readDirectoryRecursively($fusionPathPattern, '.ts2'));
            sort($filePaths);
            foreach ($filePaths as $filePath) {
                $mergedFusionCode .= PHP_EOL . file_get_contents($filePath) . PHP_EOL;
            }
        }

        // we iterate over the available packages first to maintain composer loading order
        foreach (array_keys($this->packageManager->getAvailablePackages()) as $packageKey) {
            if (isset($this->autoloadedPlugins[$packageKey]) && $this->autoloadedPlugins[$packageKey] === true) {
                $autoIncludeFusionFile = sprintf($this->autoIncludeFusionPattern, $packageKey);
                if (is_file($autoIncludeFusionFile)) {
                    $mergedFusionCode .= 'include: ' . $autoIncludeFusionFile . PHP_EOL;
                }
            }
        }

        $this->parsedFusion = $this->fusionParser->parse($mergedFusionCode);
    }
}
