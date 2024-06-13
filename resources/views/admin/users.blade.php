@extends('layouts.admin')
@section('content')
    <div class="content">
        <div class="animated fadeIn">
            <div class="row">
                {{-- <div class="breadcrumbs">
            <div class="breadcrumbs-inner">
                <div class="row m-0">
                    <div class="col-sm-4">
                        <div class="page-header float-left">
                            <div class="page-title">
                                <h1>Dashboard</h1>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="page-header float-right">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="#">Dashboard</a></li>
                                    <li><a href="#">Table</a></li>
                                    <li class="active">Data table</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

                <div class="col-md-12">

                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Search Event</strong>
                        </div>

                        <div class="card-body">
                            <div class="row">


                                <form class="col-md-5" action="" method="GET">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" placeholder="Enter user email,name or ID" name="q">
                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></span>
                                    </div>

                                    <button class="btn btn-primary">
                                        Search
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>

                    @if($search)
                    <div class="search-info py-2 h5 fw-normal text-center text-dark">
                        Results for search query "{{ $search }}"
                    </div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Recent Users</strong>
                        </div>
                        <div class="table-stats order-table ov-h">
                            <table class="table ">
                                <thead>
                                    <tr>
                                        <th class="serial">#</th>
                                        <th class="avatar"></th>
                                        <th>Name</th>
                                        <th>E-mail Address</th>
                                        <th>Phone Number</th>
                                        <th>Joined</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td class="avatar">
                                                <div class="round-img">
                                                    <a href="#"><img class="rounded-circle"
                                                            src="{{ $user->profile_image }}" alt=""></a>
                                                </div>
                                            </td>
                                            <td>{{ $user->firstname }} {{ $user->lastname }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->phone }}</td>
                                            <td>{{ diffFormat($user->created_at) }}</td>
                                            <td>
                                                <button class="btn btn-primary btn-sm">
                                                    View <i class="fa fa-eye"></i>
                                                </button>


                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                            {{ $users->links() }}
                        </div> <!-- /.table-stats -->
                    </div>
                </div>

            </div>
        </div>
    </div><!-- .animated -->
@endsection
