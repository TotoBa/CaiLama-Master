<div class="{$grid_class|default:'grid-3'}">
  {foreach $cards as $card}
    <article class="card">
      {if isset($card.tag)}<span class="tag {$card.tag_class|default:''}">{$card.tag}</span>{/if}
      <h3>{$card.title}</h3>
      <p>{$card.text}</p>
    </article>
  {/foreach}
</div>
