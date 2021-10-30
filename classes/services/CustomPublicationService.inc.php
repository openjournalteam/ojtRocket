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
        $publicationQO = $this->getQueryBuilder($args)->getQuery();

        import('plugins.generic.ojtRocket.classes.publication.CustomPublicationDAO');

        $customPublicationDAO = new CustomPublicationDAO();
        $result = $customPublicationDAO->retrieveRange($publicationQO->toSql(), $publicationQO->getBindings(), $range);

        if ($eagerLoad && isset($args['submissionIds'])) {
            $publicationIds = [];
            $publications = $result->getAll();
            $result->MoveFirst();

            foreach ($publications as $publication) {
                $publicationIds[] = $publication['publication_id'];
            }

            $params['authors'] = $this->getAuthors($publicationIds);
            $params['galleys'] = $this->getGalley($publicationIds);

            // dd($params);
            // $params['keywords']  = $this->getKeywords($publicationIds);
            // $params['subjects']  = false;
            // $params['disciplines']  = false;
            // $params['languages']  = false;
            // $params['supportingAgencies']  = false;
            // $params['categoryIds']  = false;

            // dd($params);

            // $publications = iterator_to_array(Services::get('publication')->getMany(['submissionIds' => $submissionIds]));

            // $params['publications'] = $publications;
            // echo '<pre>';
            // var_dump($publications);
            // echo '</pre>';
            // exit;
        }

        $queryResults = new DAOResultFactory($result, $customPublicationDAO, '_fromRow', [], $params);
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
