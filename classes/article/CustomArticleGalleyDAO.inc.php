<?php
import('classes.article.ArticleGalleyDAO');

class CustomArticleGalleyDAO extends ArticleGalleyDAO
{
  public function newDataObject()
  {
    import('plugins.generic.ojtRocket.classes.article.CustomArticleGalley');
    return new CustomArticleGalley();
  }
}
