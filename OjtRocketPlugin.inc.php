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
			if ($this->getSetting($this->getCurrentContextId(), 'infinite_scroll')) {
				HookRegistry::register('Templates::Common::Footer::PageFooter', [$this, 'appendFooter']);
			}

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

	function appendFooter($hookName, $args)
	{
		$templateMgr = $args[1];
		$currentPage = $templateMgr->getTemplateVars('requestedPage') . '/' . $templateMgr->getTemplateVars('requestedOp');

		$acceptPages = [
			'/index',
			'index/index',
			'issue/view',
		];

		if (!in_array($currentPage, $acceptPages)) return false;
		if (!$issue = $templateMgr->getTemplateVars('issue')) return false;
		if (!$journal = $templateMgr->getTemplateVars('currentContext')) return false;

		$dao = new DAO;
		$sql = 'SELECT s.section_id, COALESCE(o.seq, s.seq) AS section_seq FROM sections s LEFT JOIN custom_section_orders o ON (s.section_id = o.section_id AND o.issue_id = ?)  WHERE s.journal_id = ? ORDER BY section_seq';
		$result = $dao->retrieve($sql, [$issue->getId(), $journal->getId()]);
		$sectionIds = [];

		foreach ($result as $row) {
			$sectionIds[] = $row->section_id;
		}

		$request = $this->getRequest();

		$paginationUrl = $request->getDispatcher()->url($request, ROUTE_PAGE, $request->getContext()) . '/issue/pagination/' . $issue->getId();


		$templateMgr->assign('paginationUrl', $paginationUrl);
		$templateMgr->assign('sectionIds', htmlspecialchars(json_encode($sectionIds), ENT_QUOTES, 'UTF-8'));
		$templateMgr->assign('ojtRocket', $this);


		$args[2]  = $templateMgr->fetch('frontend/components/footer_extend.tpl');
		return true;
	}

	public function getAssetUrl($asset)
	{
		return $this->getRequest()->getBaseUrl() . '/' . $this->getPluginPath() . '/assets/' . $asset;
	}

	/**
	 * Add a settings action to the plugin's entry in the
	 * plugins list.
	 *
	 * @param Request $request
	 * @param array $actionArgs
	 * @return array
	 */
	// public function getActions($request, $actionArgs)
	// {

	// 	// Get the existing actions
	// 	$actions = parent::getActions($request, $actionArgs);

	// 	// Only add the settings action when the plugin is enabled
	// 	if (!$this->getEnabled()) {
	// 		return $actions;
	// 	}

	// 	// Create a LinkAction that will make a request to the
	// 	// plugin's `manage` method with the `settings` verb.
	// 	$router = $request->getRouter();
	// 	import('lib.pkp.classes.linkAction.request.AjaxModal');
	// 	$linkAction = new LinkAction(
	// 		'settings',
	// 		new AjaxModal(
	// 			$router->url(
	// 				$request,
	// 				null,
	// 				null,
	// 				'manage',
	// 				null,
	// 				[
	// 					'verb' => 'settings',
	// 					'plugin' => $this->getName(),
	// 					'category' => 'generic'
	// 				]
	// 			),
	// 			$this->getDisplayName()
	// 		),
	// 		__('manager.plugins.settings'),
	// 		null
	// 	);

	// 	// Add the LinkAction to the existing actions.
	// 	// Make it the first action to be consistent with
	// 	// other plugins.
	// 	array_unshift($actions, $linkAction);

	// 	return $actions;
	// }

	/**
	 * Show and save the settings form when the settings action
	 * is clicked.
	 *
	 * @param array $args
	 * @param Request $request
	 * @return JSONMessage
	 */
	public function manage($args, $request)
	{
		switch ($request->getUserVar('verb')) {
			case 'settings':

				// Load the custom form
				$this->import('OjtRocketSettingsForm');
				$form = new OjtRocketSettingsForm($this);

				// Fetch the form the first time it loads, before
				// the user has tried to save it
				if (!$request->getUserVar('save')) {
					$form->initData();
					return new JSONMessage(true, $form->fetch($request));
				}

				// Validate and save the form data
				$form->readInputData();
				if ($form->validate()) {
					$form->execute();
					return new JSONMessage(true);
				}
		}


		return parent::manage($args, $request);
	}
}
