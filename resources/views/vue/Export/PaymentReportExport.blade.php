<table>
  <thead>
    <tr>
      <th>Merchant</th>
      <th>Funded Date</th>
      <th>Merchant Id</th>
      <th>Debited</th>
      <th>Total Payments</th>
      <th>Management Fee</th>
      <th>Net amount</th>
      <!-- <th>Principal</th>
      <th>Profit</th>
      <th>Last Rcode</th> -->
      <th>Last Successful Payment Date</th>
      <!-- <th>Last Payment Amount</th>
      <th>Participant RTR</th>
      <th>Participant RTR Balance</th> -->
    </tr>
  </thead>
  <tbody>
    <?php foreach ($Self as $key => $value): ?>
      <tr>
        <td>{{ $value['name']??"--" }}</td>
        <td>{{ $value['date_funded'] }}</td>
        <td>{{ $value['id']??"--" }}</td>
        <td>{{ $value['debited'] }}</td>
        <td>{{ $value['participant_share'] }}</td>
        <td>{{ $value['mgmnt_fee'] }}</td>
        <td>{{ $value['net_participant_payment'] }}</td>
        <!-- <td>{{ $value['principal'] }}</td>
        <td>{{ $value['profit'] }}</td>
        <td>{{ $value['code']??"--" }}</td> -->
        <td>{{ $value['last_payment_date'] }}</td>
        <!-- <td>{{ $value['last_payment_amount'] }}</td>
        <td>{{ $value['participant_rtr'] }}</td>
        <td>{{ $value['participant_rtr_balance'] }}</td> -->
      </tr>
    <?php endforeach; ?>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td>{{ $totals['total_participant_share'] }}</td>
      <td>{{ $totals['total_mgmnt_fee'] }}</td>
      <td>{{ $totals['total_net_participant_payment'] }}</td>
      <!-- <td>{{ $totals['total_pricipal'] }}</td>
      <td>{{ $totals['total_profit'] }}</td>
      <td></td> -->
      <td></td>
      <!-- <td></td>
      <td></td>
      <td></td> -->
    </tr>
  </tbody>
</table>
