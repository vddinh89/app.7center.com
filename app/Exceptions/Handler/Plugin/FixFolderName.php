<?php

namespace App\Exceptions\Handler\Plugin;

trait FixFolderName
{
	/**
	 * Try to fix broken plugins installation
	 * Try to fix plugin folder name issue
	 *
	 * @param $message
	 * @return string|null
	 */
	public function tryToFixPluginDirName($message): ?string
	{
		if (empty($message)) {
			return null;
		}
		
		// Get the broken plugin name
		$matches = [];
		preg_match('|/extras/plugins/([^/]+)/|ui', $message, $matches);
		$brokenPluginName = $matches[1] ?? null;
		
		if (empty($brokenPluginName)) {
			return null;
		}
		
		$issueFixed = false;
		$pluginsBasePath = config('larapen.core.plugin.path');
		
		// Load all the plugins' services provider
		$pluginsFoldersNames = [];
		try {
			$pluginsFoldersNames = scandir($pluginsBasePath);
			$pluginsFoldersNames = array_diff($pluginsFoldersNames, ['..', '.']);
		} catch (\Throwable $e) {
		}
		
		if (empty($pluginsFoldersNames)) {
			return null;
		}
		
		foreach ($pluginsFoldersNames as $pluginFolder) {
			$spFiles = glob($pluginsBasePath . $pluginFolder . '/*ServiceProvider.php');
			foreach ($spFiles as $spFilePath) {
				$matches = [];
				preg_match('|/extras/plugins/([^/]+)/([a-zA-Z0-9]+)ServiceProvider|ui', $spFilePath, $matches);
				if (empty($matches[1]) || empty($matches[2])) {
					continue;
				}
				
				$folderName = $matches[1];
				$pluginName = strtolower($matches[2]);
				if ($folderName == $pluginName) {
					continue;
				}
				
				$nsFolderName = $this->getFolderFromTheServiceProviderContent($spFilePath);
				if ($folderName == $nsFolderName) {
					continue;
				}
				
				$oldFolderPath = $pluginsBasePath . $pluginFolder;
				$newFolderPath = $pluginsBasePath . $pluginName;
				
				// Continue if the new folder name already exists
				if (file_exists($newFolderPath)) {
					continue;
				}
				
				// Renames the broken plugin directory
				try {
					if (is_dir($oldFolderPath)) {
						rename($oldFolderPath, $newFolderPath);
						$issueFixed = true;
					}
				} catch (\Throwable $e) {
				}
			}
		}
		
		if ($issueFixed) {
			// Customize and Redirect to the previous URL
			$previousUrl = url()->previous();
			$baseUrl = url('/');
			
			// Check if redirection is allowed
			// That avoids infinite redirections and redirections to external URLs
			$isRedirectionAllowed = (
				request()->input('pluginsFolderFixedBy') != $brokenPluginName
				&& str_starts_with($previousUrl, $baseUrl)
			);
			
			if ($isRedirectionAllowed) {
				// Customize and Redirect to the previous URL
				$previousUrl = url()->previous();
				
				// Add the plugin name to query parameters
				$previousUrl = urlQuery($previousUrl)
					->setParameters(['pluginsFolderFixedBy' => $brokenPluginName])
					->toString();
				
				// Redirect
				redirectUrl($previousUrl, 301, config('larapen.core.noCacheHeaders'));
			} else {
				$errorMessage = 'The "<code>%s</code>" plugin was broken due to the name of the folder that contains it.
				The script tried to fix this issue... By refreshing this page, the issue should be resolved.
				If it is not the case, please reread the documentation on the installation of this plugin, in order to fix the issue manually.';
				
				return sprintf($errorMessage, $brokenPluginName);
			}
		}
		
		return null;
	}
	
	// PRIVATE
	
	/**
	 * @param $path
	 * @return string|null
	 */
	private function getFolderFromTheServiceProviderContent($path): ?string
	{
		if (!file_exists($path)) {
			return null;
		}
		
		try {
			$content = file_get_contents($path);
			
			$matches = [];
			preg_match('|namespace\s+extras\\\plugins\\\([^;]+);|ui', $content, $matches);
			$nsFolderName = (!empty($matches[1])) ? trim($matches[1]) : null;
			
			return !empty($nsFolderName) ? $nsFolderName : null;
		} catch (\Throwable $e) {
		}
		
		return null;
	}
}
