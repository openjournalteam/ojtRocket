<?php
import('classes.article.ArticleGalley');

class CustomArticleGalley extends ArticleGalley
{
  public static function __set_state($an_array)
  {
    $obj = new CustomArticleGalley();
    $obj->_data = $an_array['_data'];
    return $obj;
  }
}
