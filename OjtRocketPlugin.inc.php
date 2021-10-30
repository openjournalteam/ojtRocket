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
			HookRegistry::register('Templates::Common::Footer::PageFooter', [$this, 'appendFooter']);
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

		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$sectionIds[] = $row['section_id'];
			$result->MoveNext();
		}

		$request = $this->getRequest();

		$paginationUrl = $request->getDispatcher()->url($request, ROUTE_PAGE, $request->getContext()) . '/issue/pagination/' . $issue->getId();


		$templateMgr->assign('paginationUrl', $paginationUrl);
		$templateMgr->assign('sectionIds', htmlspecialchars(json_encode($sectionIds), ENT_QUOTES, 'UTF-8'));
		$templateMgr->assign('ojtRocket', $this);


		$args[2]  = $templateMgr->fetch($this->getTemplateResource('components/footer_extend.tpl'));
		return true;
	}

	public function getAssetUrl($asset)
	{
		return $this->getRequest()->getBaseUrl() . '/' . $this->getPluginPath() . '/assets/' . $asset;
	}
}
