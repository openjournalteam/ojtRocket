<?php

/**
 * @file classes/journal/SectionDAO.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SectionDAO
 * @ingroup journal
 * @see Section
 *
 * @brief Operations for retrieving and modifying Section objects.
 */

import('classes.journal.Section');
import('classes.journal.SectionDAO');

class CustomSectionDAO extends SectionDAO
{
  /**
   * Retrieve all sections in which articles are currently published in
   * the given issue.
   * @param $issueId int Issue ID
   * @return array
   */
  function getByIssueId($issueId)
  {
    import('classes.submission.Submission'); // import STATUS_* constants
    $issue = Services::get('issue')->get($issueId);
    $allowedStatuses = [STATUS_PUBLISHED];
    if (!$issue->getPublished()) {
      $allowedStatuses[] = STATUS_SCHEDULED;
    }
    $submissionsIterator = Services::get('submission')->getMany([
      'contextId' => $issue->getJournalId(),
      'issueIds' => $issueId,
      'status' => $allowedStatuses,
    ]);
    $sectionIds = [];
    foreach ($submissionsIterator as $submission) {
      $sectionIds[] = $submission->getCurrentPublication()->getData('sectionId');
    }
    if (empty($sectionIds)) {
      return [];
    }
    $sectionIds = array_unique($sectionIds);
    $result = $this->retrieve(
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
      $returner[] = $this->_fromRow($row);
      $result->MoveNext();
    }

    $result->Close();
    return $returner;
  }
}
