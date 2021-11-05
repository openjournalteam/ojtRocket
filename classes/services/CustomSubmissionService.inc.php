<?php

import('classes.services.SubmissionService');

class CustomSubmissionService extends \App\Services\SubmissionService
{
  /**
   * Get publications
   *
   * @param array $args {
   *		@option int|array submissionIds
   * 		@option int count
   * 		@option int offset
   * }
   * @return Iterator
   */
  public function getMany($args = [])
  {
    $functionParams = [];
    $range = null;


    if (isset($args['count'])) {
      import('lib.pkp.classes.db.DBResultRange');
      $range = new \DBResultRange($args['count'], null, isset($args['offset']) ? $args['offset'] : 0);
    }
    // Pagination is handled by the DAO, so don't pass count and offset
    // arguments to the QueryBuilder.
    if (isset($args['count'])) unset($args['count']);
    if (isset($args['offset'])) unset($args['offset']);
    $submissionListQO = $this->getQueryBuilder($args)->getQuery();
    import('plugins.generic.ojtRocket.classes.submission.CustomSubmissionDAO');

    $submissionDao = new CustomSubmissionDAO(); /* @var $submissionDao SubmissionDAO */
    $result = $submissionDao->retrieveRange($sql = $submissionListQO->toSql(), $params = $submissionListQO->getBindings(), $range);
    $submissionIds = [];

    $list = $submissionDao->retrieveRange($sql, $params, $range);
    foreach ($list as $submission) {
      $submissionIds[] = $submission->submission_id;
    }
    import('plugins.generic.ojtRocket.classes.services.CustomPublicationService');

    $publications = (new CustomPublicationService())->getMany(['submissionIds' => $submissionIds]);
    $submissionPublications = [];
    foreach ($publications as $publication) {
      $submissionPublications[$publication->getData('submissionId')][] = $publication;
    }

    $functionParams['publications'] = $submissionPublications;

    import('plugins.generic.ojtRocket.classes.db.CustomDAOResultFactory');
    $queryResults = new CustomDAOResultFactory($result, $submissionDao, '_fromRow', [], $sql, $params, null, $functionParams);

    return $queryResults->toIterator();
  }
}
