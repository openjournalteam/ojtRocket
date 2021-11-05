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

import('classes.submission.Submission');
import('classes.submission.SubmissionDAO');

class CustomSubmissionDAO extends SubmissionDAO
{
  function _fromRow($row, $args = [])
  {
    $submission = $this->_parentFromRow($row);
    if (isset($args['publications']) && isset($args['publications'][$submission->getId()])) {
      $submission->setData('publications', $args['publications'][$submission->getId()]);
    }

    return $submission;
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

  public function newDataObject()
  {
    import('plugins.generic.ojtRocket.classes.submission.CustomSubmission');
    return new CustomSubmission();
  }
}
