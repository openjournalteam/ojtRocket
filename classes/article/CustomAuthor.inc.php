<?php
import('classes.article.Author');

class CustomAuthor extends Author
{
  public static function __set_state($an_array)
  {
    $obj = new CustomAuthor();
    $obj->_data = $an_array['_data'];
    return $obj;
  }
}
