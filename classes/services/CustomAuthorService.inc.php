<?php
import('lib.pkp.classes.services.PKPAuthorService');
class CustomAuthorService extends \PKP\Services\PKPAuthorService
{
  public function getMany($args = array())
  {
    import('plugins.generic.ojtRocket.classes.article.CustomAuthorDAO');
    $authorQO = $this->getQueryBuilder($args)->getQuery();
    $authorDao = new CustomAuthorDAO(); /* @var $authorDao AuthorDAO */
    $result = $authorDao->retrieveRange($authorQO->toSql(), $authorQO->getBindings());

    import('plugins.generic.ojtRocket.classes.db.CustomDAOResultFactory');
    $queryResults = new CustomDAOResultFactory($result, $authorDao, '_fromRow');

    return $queryResults->toIterator();
  }
}
