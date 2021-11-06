<?php

import('classes.issue.IssueAction');
import('pages.issue.IssueHandler');
import('plugins.generic.ojtRocket.classes.journal.CustomSectionDAO');

class IssuePageHandler extends IssueHandler
{
  /**
   * View an issue.
   * @param $args array
   * @param $request PKPRequest
   */
  function view($args, $request)
  {
    $issue = $this->getAuthorizedContextObject(ASSOC_TYPE_ISSUE);
    $this->setupTemplate($request);
    $templateMgr = TemplateManager::getManager($request);
    $journal = $request->getJournal();

    if (($galley = $this->getGalley()) && $this->userCanViewGalley($request)) {
      if (!HookRegistry::call('IssueHandler::view::galley', array(&$request, &$issue, &$galley))) {
        $request->redirect(null, null, 'download', array($issue->getBestIssueId($journal), $galley->getBestGalleyId($journal)));
      }
    } else {
      static::_setupIssueTemplate($request, $issue, $request->getUserVar('showToc') ? true : false);
      $templateMgr->assign('issueId', $issue->getBestIssueId());

      // consider public identifiers
      $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
      $templateMgr->assign('pubIdPlugins', $pubIdPlugins);
      $templateMgr->display('frontend/pages/issue.tpl');
    }
  }
  /**
   * Given an issue, set up the template with all the required variables for
   * frontend/objects/issue_toc.tpl to function properly (i.e. current issue
   * and view issue).
   * @param $issue object The issue to display
   * @param $showToc boolean iff false and a custom cover page exists,
   * 	the cover page will be displayed. Otherwise table of contents
   * 	will be displayed.
   */
  static function _setupIssueTemplate($request, $issue, $showToc = false)
  {
    $journal = $request->getJournal();
    $user = $request->getUser();
    $templateMgr = TemplateManager::getManager($request);

    $templateMgr->assign(array(
      'issueIdentification' => $issue->getIssueIdentification(),
      'issueTitle' => $issue->getLocalizedTitle(),
      'issueSeries' => $issue->getIssueIdentification(array('showTitle' => false)),
    ));

    $locale = AppLocale::getLocale();

    $templateMgr->assign(array(
      'locale' => $locale,
    ));

    $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO'); /* @var $issueGalleyDao IssueGalleyDAO */

    $genreDao = DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
    $primaryGenres = $genreDao->getPrimaryByContextId($journal->getId())->toArray();
    $primaryGenreIds = array_map(function ($genre) {
      return $genre->getId();
    }, $primaryGenres);

    $issueSubmissionsInSection = static::getCachedIssueSubmissionsInSection($issue, $journal);
    $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
    $templateMgr->assign(array(
      'issue' => $issue,
      'issueGalleys' => $issueGalleyDao->getByIssueId($issue->getId()),
      'publishedSubmissions' => $issueSubmissionsInSection,
      'primaryGenreIds' => $primaryGenreIds,
    ));

    // Subscription Access
    import('classes.issue.IssueAction');
    $issueAction = new IssueAction();
    $subscriptionRequired = $issueAction->subscriptionRequired($issue, $journal);
    $subscribedUser = $issueAction->subscribedUser($user, $journal);
    $subscribedDomain = $issueAction->subscribedDomain($request, $journal);

    if ($subscriptionRequired && !$subscribedUser && !$subscribedDomain) {
      $templateMgr->assign('subscriptionExpiryPartial', true);

      // Partial subscription expiry for issue
      $partial = $issueAction->subscribedUser($user, $journal, $issue->getId());
      if (!$partial) $issueAction->subscribedDomain($request, $journal, $issue->getId());
      $templateMgr->assign('issueExpiryPartial', $partial);

      // Partial subscription expiry for articles
      $articleExpiryPartial = array();
      foreach ($issueSubmissions as $issueSubmission) {
        $partial = $issueAction->subscribedUser($user, $journal, $issue->getId(), $issueSubmission->getId());
        if (!$partial) $issueAction->subscribedDomain($request, $journal, $issue->getId(), $issueSubmission->getId());
        $articleExpiryPartial[$issueSubmission->getId()] = $partial;
      }
      $templateMgr->assign('articleExpiryPartial', $articleExpiryPartial);
    }

    $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO'); /* @var $completedPaymentDao OJSCompletedPaymentDAO */
    $templateMgr->assign(array(
      'hasAccess' => !$subscriptionRequired ||
        $issue->getAccessStatus() == ISSUE_ACCESS_OPEN ||
        $subscribedUser || $subscribedDomain ||
        ($user && $completedPaymentDao->hasPaidPurchaseIssue($user->getId(), $issue->getId()))
    ));

    import('classes.payment.ojs.OJSPaymentManager');
    $paymentManager = Application::getPaymentManager($journal);
    if ($paymentManager->onlyPdfEnabled()) {
      $templateMgr->assign('restrictOnlyPdf', true);
    }
    if ($paymentManager->purchaseArticleEnabled()) {
      $templateMgr->assign('purchaseArticleEnabled', true);
    }
  }

  static function getCachedIssueSubmissionsInSection($issue, $journal)
  {
    import('plugins.generic.ojtRocket.classes.submission.CustomSubmission');
    import('plugins.generic.ojtRocket.classes.publication.CustomPublication');
    import('plugins.generic.ojtRocket.classes.article.CustomAuthor');
    import('plugins.generic.ojtRocket.classes.article.CustomArticleGalley');
    $allowedStatuses = [STATUS_PUBLISHED];
    if (!$issue->getPublished()) {
      $allowedStatuses[] = STATUS_SCHEDULED;
    }
    $cache = CacheManager::getManager()->getCache(
      'issue_submission',
      $issue->getId() . '-' . $journal->getId() . '-' . $sectionId,
      function ($cache, $issueId) use ($issue, $journal, $allowedStatuses, $sectionId) {
        import('plugins.generic.ojtRocket.classes.services.CustomSubmissionService');
        $submissionService = new CustomSubmissionService();
        $issueSubmissions = iterator_to_array($submissionService->getMany([
          'contextId' => $journal->getId(),
          'issueIds' => [$issueId],
          'status' => $allowedStatuses,
          'orderBy' => 'seq',
          'orderDirection' => 'ASC',
        ]));

        $sectionDao = Application::get()->getSectionDao();
        $sections = self::getSectionsByIssueSubmissions($sectionDao, $issue->getId(), $issueSubmissions);
        $issueSubmissionsInSection = [];
        foreach ($sections as $section) {
          $issueSubmissionsInSection[$section->getId()] = [
            'title' => $section->getLocalizedTitle(),
            'articles' => [],
          ];
        }
        foreach ($issueSubmissions as $submission) {
          if (!$sectionId = $submission->getCurrentPublication()->getData('sectionId')) {
            continue;
          }
          $issueSubmissionsInSection[$sectionId]['articles'][] = $submission;
        }


        $cache->setEntireCache([
          $issueId => $issueSubmissionsInSection
        ]);

        return $issueSubmissionsInSection;
      }
    );

    if (time() - $cache->getCacheTime() > 60 * 60 * 6) {
      $cache->flush();
    }

    return $cache->get($issue->getId());
  }

  static function getSectionsByIssueSubmissions($sectionDao, $issueId, $issueSubmissions)
  {
    $sectionIds = [];
    foreach ($issueSubmissions as $submission) {
      $sectionIds[] = $submission->getCurrentPublication()->getData('sectionId');
    }
    if (empty($sectionIds)) {
      return [];
    }
    $sectionIds = array_unique($sectionIds);
    $result = $sectionDao->retrieve(
      'SELECT s.*, COALESCE(o.seq, s.seq) AS section_seq
				FROM sections s
				LEFT JOIN custom_section_orders o ON (s.section_id = o.section_id AND o.issue_id = ?)
				WHERE s.section_id IN (' . substr(str_repeat('?,', count($sectionIds)), 0, -1) . ')
				ORDER BY section_seq',
      array_merge([(int) $issueId], $sectionIds)
    );

    $returner = array();
    while (!$result->EOF) {
      $row = $result->GetRowAssoc(false);
      $returner[] = $sectionDao->_fromRow($row);
      $result->MoveNext();
    }

    $result->Close();
    return $returner;
  }
}
