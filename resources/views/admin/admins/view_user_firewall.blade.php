@extends('layouts.admin.admin_lte')

@section('content')

    <div class="inner admin-dsh header-tp">
        <h3><i class="fa fa-angle-right" aria-hidden="true"></i>{{ isset($title) ? $title : '' }} </h3>
        <a href="#" class="help-link">
            <i class="fa fa-question-circle" aria-hidden="true"></i>
            <div class="tool-tip">Add or Delete IP to be whitelisted for the User</div>
        </a>
    </div>
    {{ Breadcrumbs::render('firewallEdit') }}
    <div class="col-md-12">
        <!-- general form elements -->
        <div class="box ">
            <div class="box-head ">
                @include('layouts.admin.partials.lte_alerts')
            </div>

            <div class="box-body ">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="name-wrapper">
                            <div class="col-sm-4 name-box">
                                <div class="name-box-in">
                                    <label> <span class="title">Name: </span><span class="value">{{ $user->name }}
                                        </span></label>
                                </div>
                            </div>
                            <div class="col-sm-4 name-box">
                                <div class="name-box-in">
                                    <label> <span class="title">Email: </span><span
                                            class="value">{{ $user->email }}</span></label>
                                </div>
                            </div>
                            <div class="col-sm-12 name-box">
                                <div class="ip-wrap">
                                    {!! Form::open(['route' => ['admin::firewall::add'], 'method' => 'POST']) !!}
                                    {!! Form::hidden('user_id', $user->id) !!}
                                    {!! Form::hidden('add', 'yes') !!}
                                    <div class="ip-btn">{!! Form::submit('Add IP', ['class' => 'btn btn-primary']) !!}</div>
                                    <div class="ip-box">
                                        <input type="text" name="ip_address" required class="form-control" id="ip-address"
                                            placeholder="Enter IP address" value="{{ old('ip_address') }}">
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @if ($user->firewalls_count > 0)
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>IP</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->firewalls as $key => $ip)
                                        <tr>
                                            <td>{{ ++$key }}</td>
                                            <td> {{ $ip->ip_address }} </td>
                                            <td>
                                                <form method="POST" action="{{ route('admin::firewall::delete') }}"
                                                    onsubmit="return confirm('Are you sure want to delete?')">
                                                    @csrf
                                                    {!! Form::hidden('user_id', $user->id) !!}
                                                    {!! Form::hidden('ip_address', $ip->ip_address) !!}
                                                    <input class="btn btn-xs btn-danger" type="submit" value="Delete">
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <p style="color:#FF0000;margin:0;">All IPs are whitelisted</p>
                    </div>
                @endif
                <!-- /.box-body -->
            </div>
        </div>
        <!-- /.box -->
    </div>
@stop
@section('scripts')

    <script src='{{ asset('js/jquery_validate_min.js') }}' type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            $('#ip_form').validate({ // initialize the plugin
                errorClass: 'errors',
                rules: {
                    ip_address: {
                        required: true
                    }
                },
                messages: {
                    ip_address: "Enter IP to whitelist"
                },
            });
        });

    </script>
@stop
@section('styles')
    <link href="{{ asset('/css/optimized/create_new_editor.css?ver=5') }}" rel="stylesheet" type="text/css" />
@stop
