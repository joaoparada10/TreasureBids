@extends('layouts.app')

@section('title', 'Auctions')

@section('content')

    <div class="container mt-5">
        <h1 class="text-center">Auctions</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Starting Price</th>
                    <th>Buyout Price</th>
                    <th>Owner</th>
                    <th>Highest Bid</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($auctions as $auction)

                            <tr>
                    <td>{{ $auction->title }}</td>
                    <td>${{ $auction->starting_price }}</td>
                    <td>${{ $auction->buyout_price }}</td>
                    <td>{{ $auction->owner->username ?? 'Unknown' }}</td>
                    <td>${{ $auction->highestBid->amount ?? 'None' }}</td>
                    <td>{{ $auction->end_date }}</td>
                    <td>
                        <a href="{{ url('/auctions', $auction->id) }}" class="btn btn-primary">View Details</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
