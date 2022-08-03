<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Merchant</th>
      <th>Funded Date</th>
      <th>Default Date</th>
      <th>Default Invested Amount</th>
      <th>Default RTR Amount</th>
    </tr>
  </thead>
  <tbody>
    <?php $total_default_amount=$total_default_investor_rtr = 0;?>
    <?php foreach ($Self as $key => $value): ?>
      <?php $total_default_amount = $total_default_amount+$value->default_amount;
      $total_default_investor_rtr = $total_default_investor_rtr+$value->investor_rtr;
      ?>
      <tr>
        <td>{{ $value->id }} </td>
        <td>{{ $value->name }} </td>
        <td>{{ FFM::date($value->date_funded) }} </td>
        <td>{{ FFM::datetime($value->last_status_updated_date) }} </td>
        <td>{{ FFM::dollar($value->default_amount) }} </td>
        <td>{{ FFM::dollar($value->investor_rtr) }} </td>
      </tr>
    <?php endforeach; ?>
    <tr>
        <td> </td>
        <td></td>
        <td> </td>
        <td> </td>
        <td>{{ FFM::dollar($total_default_amount) }} </td>
        <td>{{ FFM::dollar($total_default_investor_rtr) }} </td>
      </tr>
  </tbody>
</table>
