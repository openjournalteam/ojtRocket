<?php

/**
 * @file classes/publication/PublicationDAO.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationDAO
 * @ingroup core
 * @see DAO
 *
 * @brief Add OJS-specific functions for PKPPublicationDAO
 */

use Illuminate\Database\Capsule\Manager as Capsule;


import('classes.publication.PublicationDAO');

class CustomPublicationDAO extends PublicationDAO
{
    /**
     * @copydoc SchemaDAO::_fromRow()
     */
    public function _fromRow($primaryRow, $args = [])
    {
        $publication = $this->_parentFromRow($primaryRow);

        // Set the primary locale from the submission
        $locale = Capsule::table('submissions as s')
            ->where('s.submission_id', '=', $publication->getData('submissionId'))
            ->value('locale');
        $publication->setData('locale', $locale);

        $publication->setData('galleys', $args['galleys'][$publication->getId()] ?? []);
        $publication->setData('authors', $args['authors'][$publication->getId()] ?? []);

        // not optimized yet
        // Get controlled vocab metadata
        $submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO'); /* @var $submissionKeywordDao SubmissionKeywordDAO */
        $publication->setData('keywords', $submissionKeywordDao->getKeywords($publication->getId()));
        $submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO'); /* @var $submissionSubjectDao SubmissionSubjectDAO */
        $publication->setData('subjects', $submissionSubjectDao->getSubjects($publication->getId()));
        $submissionDisciplineDao = DAORegistry::getDAO('SubmissionDisciplineDAO'); /* @var $submissionDisciplineDao SubmissionDisciplineDAO */
        $publication->setData('disciplines', $submissionDisciplineDao->getDisciplines($publication->getId()));
        $submissionLanguageDao = DAORegistry::getDAO('SubmissionLanguageDAO'); /* @var $submissionLanguageDao SubmissionLanguageDAO */
        $publication->setData('languages', $submissionLanguageDao->getLanguages($publication->getId()));
        $submissionAgencyDao = DAORegistry::getDAO('SubmissionAgencyDAO'); /* @var $submissionAgencyDao SubmissionAgencyDAO */
        $publication->setData('supportingAgencies', $submissionAgencyDao->getAgencies($publication->getId()));

        // Get categories
        $categoryDao = DAORegistry::getDAO('CategoryDAO'); /* @var $categoryDao CategoryDAO */
        $publication->setData('categoryIds', array_map(
            function ($category) {
                return (int) $category->getId();
            },
            $categoryDao->getByPublicationId($publication->getId())->toArray()
        ));
        return $publication;
    }

    private function _parentFromRow($primaryRow)
    {
        $schemaService = Services::get('schema');
        $schema = $schemaService->get($this->schemaName);

        $object = $this->newDataObject();

        foreach ($this->primaryTableColumns as $propName => $column) {
            if (isset($primaryRow[$column])) {
                $object->setData(
                    $propName,
                    $this->convertFromDb($primaryRow[$column], $schema->properties->{$propName}->type)
                );
            }
        }

        $result = $this->retrieve(
            "SELECT * FROM $this->settingsTableName WHERE $this->primaryKeyColumn = ?",
            array($primaryRow[$this->primaryKeyColumn])
        );

        foreach ($result as $settingRow) {
            $settingRow = (array) $settingRow;
            if (!empty($schema->properties->{$settingRow['setting_name']})) {
                $object->setData(
                    $settingRow['setting_name'],
                    $this->convertFromDB(
                        $settingRow['setting_value'],
                        $schema->properties->{$settingRow['setting_name']}->type
                    ),
                    empty($settingRow['locale']) ? null : $settingRow['locale']
                );
            }
        }

        return $object;
    }

    // too handle __setState in original class
    public function newDataObject()
    {
        import('plugins.generic.ojtRocket.classes.publication.CustomPublication');
        return new CustomPublication();
    }
}
