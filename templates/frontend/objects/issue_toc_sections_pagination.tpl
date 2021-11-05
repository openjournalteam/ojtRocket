{if !$heading}
  {assign var="heading" value="h2"}
{/if}
{assign var="articleHeading" value="h3"}
{if $heading == "h3"}
  {assign var="articleHeading" value="h4"}
{elseif $heading == "h4"}
  {assign var="articleHeading" value="h5"}
{elseif $heading == "h5"}
  {assign var="articleHeading" value="h6"}
{/if}
{foreach name=sections from=$publishedSubmissions item=section}
  <div class="section">
    {if $section.articles}
      {if $section.title}
        <{$heading}>
          {$section.title|escape}
        </{$heading}>
      {/if}
      <ul class="cmp_article_list articles">
        {foreach from=$section.articles item=article}
          <li>
            {include file="frontend/objects/article_summary.tpl" heading=$articleHeading}
          </li>
        {/foreach}
      </ul>
    {/if}
  </div>
{/foreach}