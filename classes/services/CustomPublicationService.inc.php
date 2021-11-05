<?php

import('classes.services.PublicationService');

class CustomPublicationService extends \App\Services\PublicationService
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
        $publicationQO = $this->getQueryBuilder($args)->getQuery();

        import('plugins.generic.ojtRocket.classes.publication.CustomPublicationDAO');

        $customPublicationDAO = new CustomPublicationDAO();
        $result = $customPublicationDAO->retrieveRange($sql = $publicationQO->toSql(), $params = $publicationQO->getBindings(), $range);

        $list = $customPublicationDAO->retrieveRange($publicationQO->toSql(), $publicationQO->getBindings(), $range);
        $publicationIds = [];


        foreach ($list as $publication) {
            $publicationIds[] = $publication->publication_id;
        }

        $functionParams['authors'] = $this->getAuthors($publicationIds);
        $functionParams['galleys'] = $this->getGalley($publicationIds);

        import('plugins.generic.ojtRocket.classes.db.CustomDAOResultFactory');
        $queryResults = new CustomDAOResultFactory($result, $customPublicationDAO, '_fromRow', [], $sql, $params, null, $functionParams);
        return $queryResults->toIterator();
    }

    private function getAuthors($publicationIds)
    {
        import('plugins.generic.ojtRocket.classes.services.CustomAuthorService');
        $authorService = new CustomAuthorService();
        $authors = $authorService->getMany(['publicationIds' => $publicationIds]);
        $publicationAuthor = [];
        foreach ($authors as $author) {
            $publicationAuthor[$author->getData('publicationId')][] = $author;
        }

        return $publicationAuthor;
    }

    private function getGalley($publicationIds)
    {
        import('plugins.generic.ojtRocket.classes.services.CustomGalleyService');
        $customGalleyService = new CustomGalleyService();
        $galleys = $customGalleyService->getMany(['publicationIds' => $publicationIds]);

        // dd($galleys);

        $publicationGalley = [];
        foreach ($galleys as $galley) {
            $publicationGalley[$galley->getData('publicationId')][] = $galley;
        }

        return $publicationGalley;
    }

    private function getKeywords($publicationIds)
    {
        $keywords = Services::get('keyword')->getMany(['publicationIds' => $publicationIds]);
    }
}
