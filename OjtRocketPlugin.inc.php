<?php

/**
 * @file OjtRocketPlugin.inc.php
 *
 * Copyright (c) 2021 openjournaltheme.com
 * Modify, redistribute or make commercial copy of this part or whole of this code is prohibited without written permission from openjournaltheme.com
 *
 * @class OjtRocketPlugin
 * @ingroup plugins_generic_ojtRocket
 *
 * @brief OjtRocketPlugin plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');
class OjtRocketPlugin extends GenericPlugin
{

	/**
	 * @copydoc GenericPlugin::register()
	 */
	public function register($category, $path, $mainContextId = NULL)
	{
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
			// Display the publication statement on the article details page
			HookRegistry::register('LoadHandler', [$this, 'setPageHandler']);

			// Allow plugin to override plugin template files
			HookRegistry::register('TemplateResource::getFilename', array($this, '_overridePluginTemplates'));
		}
		return $success;
	}

	public function setPageHandler($hookName, $args)
	{
		$page = &$args[0];
		$op = &$args[1];

		if ($page === '' || $page === 'index') {
			switch ($op) {
				case 'index':
					define('HANDLER_CLASS', 'IndexPageHandler');
					$this->import('pages.IndexPageHandler');
					return true;
					break;
			}
		}

		if ($page === 'issue') {
			switch ($op) {
				case 'index':
				case 'current':
				case 'view':
				case 'pagination':
					define('HANDLER_CLASS', 'IssuePageHandler');
					$this->import('pages.IssuePageHandler');
					return true;
					break;
			}
		}

		return false;
	}

	/**
	 * Provide a name for this plugin
	 *
	 * The name will appear in the Plugin Gallery where editors can
	 * install, enable and disable plugins.
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return 'OJT Rocket Plugin';
	}

	/**
	 * Provide a description for this plugin
	 *
	 * The description will appear in the Plugin Gallery where editors can
	 * install, enable and disable plugins.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return 'Reduce load time to your Journal site';
	}

	public function getAssetUrl($asset)
	{
		return $this->getRequest()->getBaseUrl() . '/' . $this->getPluginPath() . '/assets/' . $asset;
	}
}
