@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <div class="row mt-4">
        <div class="col-md-12">
            <h3 class="mb-4">{{ $event->title }}</h3>
            <div class="card">
                <img src="{{ asset('storage/'.$event->cover_image) }}" class="card-img-top" alt="Event Image" />
                <div class="card-body">
                    <h5 class="card-title mb-4">{{ $event->title }}</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Description:</strong> {{ $event->description }}
                        </li>
                        <li class="list-group-item">
                            <strong>Start Date:</strong> {{ $event->startDate }}
                        </li>
                        <li class="list-group-item">
                            <strong>End Date:</strong> {{ $event->endDate }}
                        </li>
                        <li class="list-group-item">
                            <strong>Location:</strong> {{ $event->location }}
                        </li>
                        <li class="list-group-item">
                            <strong>Status:</strong> <span class="badge badge-primary">{{ $event->status }}</span>
                        </li>
                        <li class="list-group-item">
                            <strong>Tickets Sold:</strong> {{ $event->attendees->count() }}
                        </li>

                        <!-- Add other event details as needed -->
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">Event Tickets</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Capacity</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eventTickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->name }}</td>
                                    <td>${{ $ticket->price }}</td>
                                    <td>{{ $ticket->capacity }}</td>
                                    <td>{{ $ticket->description }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">

            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">Purchase Ticket</h3>
                    <form action="{{ route('admin.ticket.purchase') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="event_ticket">Select Event Ticket:</label>
                            <select class="form-control" id="event_ticket" name="event_ticket">
                                @foreach($eventTickets as $ticket)
                                    <option value="{{ $ticket->id }}">{{ $ticket->name }} - ${{ $ticket->price }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>

                        <div class="form-group">
                            <label for="user_email">User's Email:</label>
                            <input type="email" class="form-control" id="user_email" name="user_email" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Purchase</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">Purchases</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User Email</th>
                                <th>Ticket Name</th>
                                <th>Amount Paid</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->user->email }}</td>
                                    <td>{{ $purchase->eventTicket->name }}</td>
                                    <td>${{ $purchase->amount_paid }}</td>
                                    <td>
                                        @if($purchase->status === 'UNUSED')
                                            <span class="badge badge-success">{{ $purchase->status }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ $purchase->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $purchase->created_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection
