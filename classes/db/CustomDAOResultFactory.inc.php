<?php

import('lib.pkp.classes.db.DAOResultFactory');

use Illuminate\Support\Enumerable;


class CustomDAOResultFactory extends DAOResultFactory
{
  function __construct($records, $dao, $functionName, $idFields = [], $sql = null, $params = [], $rangeInfo = null, $functionParams = [])
  {
    $this->functionName = $functionName;
    $this->dao = $dao;
    $this->idFields = $idFields;
    $this->records = $records;
    $this->sql = $sql;
    $this->params = $params;
    $this->rangeInfo = $rangeInfo;
    $this->functionParams = $functionParams;
  }

  function next()
  {
    if ($this->records == null) return $this->records;

    $row = null;
    $functionName = $this->functionName;
    $dao = $this->dao;

    if ($this->records instanceof Generator) {
      $row = (array) $this->records->current();
      $this->records->next();
    } elseif ($this->records instanceof Enumerable) {
      $row = (array) $this->records->shift();
    } else throw new Exception('Unsupported record set type (' . join(', ', class_implements($this->records)) . ')');
    if (!$row) return null;
    return $dao->$functionName($row, $this->functionParams);
  }
}
