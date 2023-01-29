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


                    <div class="breadcrumbs">
                        <div class="breadcrumbs-inner">
                            <div class="row m-0">
                                <div class="col-sm-4">
                                    <div class="page-header float-left">
                                        <div class="page-title">
                                            <h1>Edit Event</h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <div class="page-header float-right">
                                        <div class="page-title">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    </br>

                    <div class="card">
                        <div class="card-header">
                            <strong class="card-title">{{ $event->title }}</strong>
                        </div>

                        <div class="card-body">
                            <form>

                                <div class="mb-3">
                                    <label>Title</label>
                                    <input type="text" class="form-control" name="title" value="{{ $event->title }}">
                                </div>


                                <button class="btn btn-primary btn-sm">Submit</button>

                            </form>

                            <small class="text-secondary py-3">
                                Other actions*
                            </small>

                            <div class="button-group">
                            <button class="btn btn-sm btn-danger">
                                Suspend Event
                            </button>
                            </div>


                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- .animated -->
@endsection
