<!DOCTYPE html>

<html>

<head>

    <title>Import Export Excel to database </title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />

</head>

<body>

   

<div class="container">

     @include('layouts.admin.partials.lte_alerts')

    <div class="card bg-light mt-3">

        <div class="card-header">

         Import Export Excel to database

        </div>

        <div class="card-body">

            <form action="{{ route('crm.import') }}" method="POST" enctype="multipart/form-data">

                @csrf

                <input type="file" name="file" class="form-control">

                <br>

                <button class="btn btn-success">Import User Data</button>

               

            </form>

        </div>

    </div>

</div>

   

</body>

</html>