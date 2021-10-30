<?php
import('classes.publication.Publication');

class CustomPublication extends Publication
{
  public static function __set_state($an_array)
  {
    $obj = new CustomPublication();
    $obj->_data = $an_array['_data'];
    return $obj;
  }
}
