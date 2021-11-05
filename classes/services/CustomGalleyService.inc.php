<?php

use APP\Services\GalleyService;

class CustomGalleyService extends \APP\Services\GalleyService
{
  public function getMany($args = [])
  {
    import('plugins.generic.ojtRocket.classes.article.CustomArticleGalleyDAO');
    $galleyQO = $this->getQueryBuilder($args)->getQuery();
    $galleyDao = new CustomArticleGalleyDAO(); /* @var $galleyDao ArticleGalleyDAO */
    $result = $galleyDao->retrieveRange($galleyQO->toSql(), $galleyQO->getBindings());

    import('plugins.generic.ojtRocket.classes.db.CustomDAOResultFactory');
    $queryResults = new CustomDAOResultFactory($result, $galleyDao, '_fromRow');

    return $queryResults->toIterator();
  }
}
