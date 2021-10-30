<?php
import('classes.article.AuthorDAO');

class CustomAuthorDAO extends AuthorDAO
{
  // too handle __setState in original class
  public function newDataObject()
  {
    import('plugins.generic.ojtRocket.classes.article.CustomAuthor');
    return new CustomAuthor();
  }
}
