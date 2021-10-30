<?php

import('classes.submission.Submission');

class CustomSubmission extends Submission
{
  public static function __set_state($an_array)
  {
    $obj = new CustomSubmission();
    $obj->_data = $an_array['_data'];
    return $obj;
  }
}
