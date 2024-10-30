@extends('layouts.admin')
@section('content')
    <div class="content">
        <div class="container">
            <h2 class="text-secondary">
                Send a Broadcast
            </h2>

            <div class="row mt-4">
                <div class="col-md-10">
                    <form action="{{ route('admin.sendBroadcast') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="body">Body</label>
                            <textarea class="form-control" id="body" name="body" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Send Broadcast</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        CKEDITOR.replace('body');
    </script>
@endsection
