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
