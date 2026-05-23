<div class="timeline">
  {foreach $items as $item}
    <article class="step">
      <strong>{$item.title}</strong>
      <p>{$item.text}</p>
    </article>
  {/foreach}
</div>
