@extends('layouts.admin')
@section('content')

        <div class="content">
            <!-- Animated -->
            <div class="animated fadeIn">
                <!-- Widgets  -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-widget-five">
                                    <div class="stat-icon dib flat-color-1">
                                        <i class="pe-7s-cash"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-text">
                                                ₦<span class="count">{{ $revenue }}</span>
                                            </div>
                                            <div class="stat-heading">Revenue</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-widget-five">
                                    <div class="stat-icon dib flat-color-2">
                                        <i class="pe-7s-cart"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-text">
                                                <span class="count">{{ $ticketsPurchased }}</span>
                                            </div>
                                            <div class="stat-heading">Tickets bought</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-widget-five">
                                    <div class="stat-icon dib flat-color-3">
                                        <i class="pe-7s-browser"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-text">
                                                <span class="count">{{ $users }}</span>
                                            </div>
                                            <div class="stat-heading">Registered Users</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="stat-widget-five">
                                    <div class="stat-icon dib flat-color-4">
                                        <i class="pe-7s-users"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="text-left dib">
                                            <div class="stat-text">
                                                <span class="count">{{ $events }}</span>
                                            </div>
                                            <div class="stat-heading">Events Posted</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Widgets -->
                <!--  Traffic  -->

                <!--  /Traffic -->
                <div class="clearfix"></div>
                <!-- Orders -->
                {{-- <div class="orders">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="box-title">Recent Events</h4>
                                </div>
                                <div class="card-body--">
                                    <div class="table-stats order-table ov-h">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="serial">#</th>
                                                    <th class="avatar"></th>
                                                    <th>Date</th>
                                                    <th>Title</th>
                                                    <th>Tickets</th>
                                                    <th>Attending</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($recentEvents as $event )

                                                <tr>
                                                    <td class="serial">{{ $event->id }}</td>
                                                    <td class="avatar">
                                                        <div class="round-img">
                                                            <a href="#"><img class="rounded-circle"
                                                                    src="{{ asset('storage/media'.$event->cover_image) }}" alt="" /></a>
                                                        </div>
                                                    </td>
                                                    <td>{{ show_date($event->startDate) }}</td>
                                                    <td><span class="name">{{ $event->title }}</span></td>
                                                    <td><span class="product">{{ sizeOf($event->tickets) }}</span></td>
                                                    <td><span class="count">{{ 402 }}</span></td>
                                                    <td>
                                                        @if($event->status == "PENDING")
                                                        <span class="badge badge-pending">Pending</span>
                                                        @elseif($event->status == "LIVE")
                                                        <span class="badge badge-suspended">Live</span>
                                                        @else

                                                        <span class="badge badge-complete">Complete</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.table-stats -->
                                </div>
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col-lg-8 -->
                    </div>
                </div> --}}

            </div>
            <!-- .animated -->
        </div>

        @endsection
