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
  public function getMany($args = [], $eagerLoad = true)
  {
    $params = [];
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
    // dd($submissionListQO->toSql(), $submissionListQO->getBindings());

    import('plugins.generic.ojtRocket.classes.submission.CustomSubmissionDAO');

    $submissionDao = new CustomSubmissionDAO(); /* @var $submissionDao SubmissionDAO */
    $result = $submissionDao->retrieveRange($submissionListQO->toSql(), $submissionListQO->getBindings(), $range);

    if ($eagerLoad && isset($args['issueIds'])) {
      $submissionIds = [];

      $submissions = $result->getAll();

      $result->MoveFirst();

      foreach ($submissions as $submission) {
        $submissionIds[] = $submission['submission_id'];
      }


      import('plugins.generic.ojtRocket.classes.services.CustomPublicationService');
      $customPublicationServices = new CustomPublicationService();
      $publications = iterator_to_array($customPublicationServices->getMany(['submissionIds' => $submissionIds]));

      $submissionPublications = [];
      foreach ($publications as $publication) {
        $submissionPublications[$publication->getData('submissionId')][] = $publication;
      }

      $params['submissionPublications'] = $submissionPublications;
    }


    $queryResults = new DAOResultFactory($result, $submissionDao, '_fromRow', [], $params);
    return $queryResults->toIterator();
  }
}
