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
                                    <input type="text" class="form-control" name="q" placeholder="Enter a keyword"value="@php if($search) echo $search @endphp">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-search"></i></span>
                                  </div>

                                  <button class="btn btn-primary">
                                    Search
                                  </button>
                            </form>

                            </div>
                        </div>
                    </div>

                </br>
                @if($search)
                <div class="search-info py-2 h5 fw-normal text-center text-dark">
                    Results for search query "{{ $search }}"
                </div>
                @endif
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Recent Events</strong>
                        </div>
                        <div class="table-stats order-table ov-h">
                            <table class="table ">
                                <thead>
                                    <tr>
                                        <th class="serial">#</th>
                                        <th class="avatar"></th>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Tickets</th>
                                        <th>Attending</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($events as $event)
                                        <tr>
                                            <td>{{ $event->id }}</td>
                                            <td class="avatar">
                                                <div class="round-img">
                                                    <a href="#"><img class="rounded-circle"
                                                            src="{{ $event->cover_image }}" alt=""></a>
                                                </div>
                                            </td>
                                            <td>{{ show_date($event->startDate) }}</td>
                                            <td>{{ $event->title }}</td>
                                            <td>{{ sizeOf($event->tickets) }}</td>
                                            <td>{{ 44 }}</td>
                                            <td>{{ $event->status }}</td>
                                            <td>

                                                <a class="btn btn-sm btn-warning" href="{{ route("admin.edit-event",['id' => $event->id]) }}">
                                                    Edit <i class="fa fa-pencil"></i>
                                                </a>

                                                <button class="btn btn-primary btn-sm">
                                                    View <i class="fa fa-eye"></i>
                                                </button>


                                            </td>
                                            <td>
                                            {{-- {{ View event page }} --}}
                                                <a href="#" class="btn btn-success btn-sm">View <i class="fa fa-eye"></i> </a>
                                                <a href="{{ route("suspend-event") }}" class="btn btn-danger btn-sm">Suspend <i class="fa fa-not-allowed"></i> </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div> <!-- /.table-stats -->
                    </div>
                </div>

            </div>
        </div>
    </div><!-- .animated -->
@endsection
