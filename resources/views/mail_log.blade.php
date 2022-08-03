<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">
    table td {
      padding: 0;
    }

    body {
      margin: 0;
    }

    .wrapper {
      width: 100%;
      table-layout: fixed;
    }

    .wrapper-inner {
      width: 100%;
      background-color: #eee;
      max-width: 85%;
      margin: 0 auto;
    }

    a {
      word-break: break-all;
    }

    /*--- Media Queries --*/
    @media screen and (max-width: 460px) {

      td.logo,
      td.top-right {
        width: 100%;
        display: inline-block;
        text-align: center !important;
      }

      td.logo {
        padding: 30px 0 0 !important;
      }

      td.top-right {
        padding: 5px 0 10px !important;
      }

      td.top-right p {
        text-align: center !important;
        color: #585858 !important;
        margin: 10px 0 10px !important;
      }

      .content {
        padding: 0 20px !important;
      }

      .footer-top {
        padding: 0 20px !important;
      }

      .footer {
        padding: 25px 20px !important;
      }

      .main-wrap {
        padding: 0 !important
      }

      .name {
        font-size: 18px !important;
      }
    }

    @media screen and (min-width: 401px) and (max-width: 400px) {}

    @media screen and (max-width:768px) {}

    .alert {
      padding: 20px;
      background-color: #f44336;
      color: white;
      opacity: 1;
      transition: opacity 0.6s;
      margin-bottom: 15px;
    }

    .alert.success {
      background-color: #4CAF50;
    }

    .alert.info {
      background-color: #2196F3;
    }

    .alert.warning {
      background-color: #ff9800;
    }

    .closebtn {
      margin-left: 15px;
      color: white;
      font-weight: bold;
      float: right;
      font-size: 22px;
      line-height: 20px;
      cursor: pointer;
      transition: 0.3s;
    }

    .closebtn:hover {
      color: black;
    }

    input[type=text],
    select {
      width: 100%;
      padding: 12px 20px;
      margin: 8px 0;
      display: inline-block;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }

    input[type=submit] {

      background-color: #4CAF50;
      color: white;
      padding: 14px 20px;
      margin: 8px 0;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    input[type=submit]:hover {
      background-color: #45a049;
    }

    .dt_filters {
      border-radius: 5px;
      background-color: #f2f2f2;
      padding: 20px;
    }
  </style>


</head>


<body>

  <div class="wrapper">
    <div class="wrapper-inner">
      @if (Session::has('error_message'))
      <div class="alert">
        <span class="closebtn">&times;</span>
        <strong>Failed!</strong> {!! Session::get('error_message') !!}.
      </div>
      @endif
      @if (Session::has('success_message'))
      <div class="alert success">
        <span class="closebtn">&times;</span>
        <strong>Success!</strong> {!! Session::get('success_message') !!}.
      </div>
      @endif


      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background: #fff" align="center">
        <tbody>
          <tr>
            <td class="main-wrap" align="center" valign="top" style=" background: #fff; padding: 0;">
              <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0" class="mobile-shell"
                style="background: #fff; ">
                <tbody>
                  <tr>
                    <td class="td" align="center"
                      style="width:100%; min-width:100%; font-size:0pt; line-height:0pt; background: #fff; padding:0; margin:0; font-weight:normal;">
                      <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0">
                        <tbody>
                          <tr>
                            <td style="padding: 40px 0px;">
                              <div class="dt_filters">
                                <form action="/calicut78io/debug/mail-log" method="POST">
                                  <label for="fname"
                                    style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: bold;  color: #000;padding: 30px 20px;">Merchants</label>
                                  {!! Form::select('merchants', $merchant_list, $merchant_id, ['class' =>
                                  'form-control']) !!}

                                  @csrf

                                  <input type="submit" value="Submit">
                                </form>
                              </div>
                            </td>
                          </tr>


                          <tr>
                            <td style="padding: 0 0 0px;">
                              <!-- Header -->
                              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                  <table width="100%;" style="border-collapse: collapse;">
                                    <tbody>
                                      <tr style="background-color: #f2f2f2;padding:10px 40px;">
                                        <th
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: bold;  color: #000;padding: 30px 20px;border: solid 1px #000;">
                                          No
                                        </th>

                                        <th
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: bold;  color: #000;padding: 30px 20px;border: solid 1px #000;">
                                          Merchant Name
                                        </th>
                                        <th
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: bold;  color: #000;padding: 30px 20px;border: solid 1px #000;">
                                          Days
                                        </th>
                                        <th
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: bold;  color: #000;padding: 30px 20px;border: solid 1px #000;">
                                          Date funded
                                        </th>
                                        <th
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: bold;  color: #000;padding: 30px 20px;border: solid 1px #000;">
                                          Email
                                        </th>
                                        <th
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: bold;  color: #000;padding: 30px 20px;border: solid 1px #000;">
                                          Action
                                        </th>


                                      </tr>
                                      <?php $i=1;?>
                                      @if(count($merchants)>0)
                                      @foreach($merchants as $mer)

                                      <tr>
                                        <td
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: normal;  color: #000;padding: 15px;border: solid 1px #000;">
                                          {{$i}}</td>
                                        <td
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: normal;  color: #000;padding: 15px;border: solid 1px #000;">
                                          {{$mer['name']}}</td>
                                        <td
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: normal;  color: #000;padding: 15px;border: solid 1px #000;">
                                          {{$mer['days']}}</td>
                                        <td
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: normal;  color: #000;border: solid 1px #000;padding: 15px;">
                                          {{$mer['date_funded']}}</td>
                                        <td
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: normal;  color: #000;border: solid 1px #000;padding: 15px;">
                                          {{$mer['email']}}</td>
                                        <td
                                          style=" font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size:18px;font-weight: normal;  color: #000;border: solid 1px #000;padding: 15px;">
                                          <a href="{{URL::to('calicut78io/debug/resend-reconciliation')}}?mer_id={{$mer['id']}}"
                                            id='send_mail'
                                            style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #56d46f; font-size: 16px; font-weight: bold;  display: inline-block; margin: 0 5px; ">Resend</a>
                                        </td>
                                      </tr>
                                      <?php $i++;?>

                                      @endforeach
                                      @else
                                      <b>No data found</b>
                                      @endif
                                    </tbody>
                                  </table>

                                </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>

                </tbody>
              </table>
            </td>
          </tr>
        </tbody>

      </table>
    </div>
  </div>
</body>

</html>
<script>
  var close = document.getElementsByClassName("closebtn");
var i;

for (i = 0; i < close.length; i++) {
  close[i].onclick = function(){
    var div = this.parentElement;
    div.style.opacity = "0";
    setTimeout(function(){ div.style.display = "none"; }, 600);
  }
}
</script>