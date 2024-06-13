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
                                        <input type="text" class="form-control" name="q"
                                            placeholder="Enter a keyword"value="@php if($search) echo $search @endphp">
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
                    @if ($search)
                        <div class="search-info py-2 h5 fw-normal text-center text-dark">
                            Results for search query "{{ $search }}"
                        </div>
                    @endif
                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">Recent Events</strong>
                        </div>
                        <div class="table-stats order-table ov-h">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="avatar"></th>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Tickets</th>
                                        <th>Sales</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($events as $event)
                                        <tr>
                                            <td class="avatar">
                                                <div class="">
                                                    <a href="#"><img class="rounded-2" style="object-fit: cover"
                                                            src="{{ asset('storage/' . $event->cover_image) }}"
                                                            alt="" width="70" height="70"></a>
                                                </div>
                                            </td>
                                            <td>{{ formatDateTime($event->startDate) }}</td>
                                            <td>{{ $event->title }}</td>
                                            <td>{{ sizeOf($event->tickets) }}</td>
                                            <td>{{ $event->attendees->count() }}
                                            </td>
                                            <td>
                                                @if ($event->status == 'PENDING')
                                                    <small class="badge badge-success">PENDING</small>
                                                @elseif($event->status == 'FINISHED')
                                                    <small class="badge bg-dark">
                                                        FINISHED
                                                    </small>
                                                @elseif($event->status == 'REVIEWING')
                                                    <small class="badge bg-warning">
                                                        REVIEWING
                                                    </small>
                                                @endif
                                            <td>

                                                    <a href="{{ route('admin.event-details',$event->id) }}" class="btn btn-sm btn-primary ">
                                                    View Event <i class="fa fa-eye"></i>
                                                </a>
                                                </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                            {{ $events->links() }}
                        </div> <!-- /.table-stats -->
                    </div>
                </div>

            </div>
        </div>
    </div><!-- .animated -->
@endsection
