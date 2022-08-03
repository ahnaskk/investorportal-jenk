<table>
  <thead>
    <tr>
      <th>Category</th>
      <th>Method</th>
      <th>Type</th>
      <th>Amount</th>
      <th>Date</th>
      <!-- <th>AccountNo</th> -->
    </tr>
  </thead>
  <tbody>
    <?php foreach ($Self as $key => $value): ?>
      <tr>
        <td>{{$value->transaction_category}}</td>
        <td>{{ $value->TransactionMethod}}</td>
        <td>{{ $value->TransactionType}}</td>
        <td>{{ $value->amount }}</td>
        <td>{{ $value->date }}</td>
        <!-- <td>{{ $value->account_no }}</td> -->
      </tr>
    <?php endforeach; ?>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td>{{ $total_amount }}</td>
      <td></td>
      <!-- <td></td> -->
    </tr>
  </tbody>
</table>
