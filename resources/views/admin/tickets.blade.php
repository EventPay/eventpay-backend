@extends('layouts.admin')
@section('content')
    <div class="content">

        <div class="container mt-5">
            <h2 class="mb-4">Tickets</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th> ID</th>
                        <th>Email</th>
                        <th>Total Tickets</th>
                        <th>Total Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $count = 1;
                    @endphp
                    @foreach ($tickets as $ticket)
                        @php
                            $count++;
                        @endphp
                        <tr>
                            <td>{{ $count }}</td>
                            <td>{{ $ticket->email }}</td>
                            <td>{{ $ticket->total_tickets }}</td>
                            <td>{{ number_format($ticket->total_amount_paid, 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
@endsection
