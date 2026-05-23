<div class="grid-4">
  {foreach $metrics as $metric}
    <article class="metric">
      <strong>{$metric.value}</strong>
      <p>{$metric.text}</p>
    </article>
  {/foreach}
</div>
