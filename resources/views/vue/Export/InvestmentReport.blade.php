<table>
  <thead>
    <tr>
      <th>Merchant Name</th>
      <th>Funded Date</th>
      <th>Funded Amount</th>
      <th>RTR</th>
      <!-- <th>Commission</th> -->
      <th>Share (%)</th>
      <th>Syndication Fee</th>
      <th>Total Invested</th>
      <!-- <th>Under Writing Fee</th> -->
      <th>Management Fee</th>
      <th>Advance Type</th>
      <!-- <th>Created On</th> -->
    </tr>
  </thead>
  <tbody>
    <?php foreach ($Self as $key => $value): ?>
      <?php 
      $advance_type = $advanceTypes[$value->advance_type];
      
      ?>
      <tr>
        <td>{{ $value->Merchant }}</td>
        <td>{{ $value->date_funded }}</td>
        <td>{{ $value->i_amount }}</td>
        <td>{{ $value->i_rtr }}</td>
        <!-- <td>{{ $value->commission_amount }}</td> -->
        <td>{{ $value->share_t }}</td>
        <td>{{ $value->pre_paid }}</td>
        <td>{{ $value->invested_amount }}</td>
        <!-- <td>{{ $value->under_writing_fee }}</td> -->
        <td>{{ $value->mgmnt_fee }}</td>
        <td>{{ $advance_type }}</td>
        <!-- <td>{{ $value->created_at }}</td> -->
      </tr>
    <?php endforeach; ?>
    <tr>
      <td></td>
      <td></td>
      <td>{{ $datasTotal->i_amount }}</td>
      <td>{{ $datasTotal->i_rtr }}</td>
      <!-- <td>{{ $datasTotal->commission_amount }}</td> -->
      <td></td>
      <td>{{ $datasTotal->pre_paid }}</td>
      <td>{{ $datasTotal->invested_amount }}</td>
      <!-- <td>{{ $datasTotal->under_writing_fee }}</td> -->
      <td>{{ $datasTotal->mgmnt_fee }}</td>
      <!-- <td></td> -->
    </tr>
  </tbody>
</table>
