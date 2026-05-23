<div class="table-wrap">
  <table>
    <thead>
      <tr>
        {foreach $headers as $header}
          <th>{$header}</th>
        {/foreach}
      </tr>
    </thead>
    <tbody>
      {foreach $rows as $row}
        <tr>
          {foreach $row as $cell}
            <td>{$cell}</td>
          {/foreach}
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
