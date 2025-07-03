<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Auction Owner</th>
                <th>Starting Price</th>
                <th>Starting Date</th>
                <th>End Date</th>
                <th>Buyout Price</th>
                <th>Picture</th>
                <th>Description</th>
                
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

    <tbody>
    @foreach ($auctions as $auction)
        <tr id="auction-row-{{ $auction->id }}">
            <form method="post" action="{{ route('auctions.admin_update', $auction->id) }}">
                @csrf
                @method('PUT')
                <td><a href="{{ route('auction.show', $auction->id) }}">{{ $auction->id }}</a></td>
                <td>
                    <a href="{{ route('auction.show', $auction->id) }}" class="display display-title">{{ $auction->title }}</a>
                    <input type="text" name="title" class="edit form-control" value="{{ $auction->title }}" style="display: none;">
                </td>

                <td>
                    <a href="{{ route('profile', $auction->owner->username) }}" class="display display-owner">{{ $auction->owner->username }}</a>
                    <input type="text" name="owner" class="edit form-control" value="{{ $auction->owner->username }}" style="display: none;">
                </td>
                <td>
                    <span class="display display-starting_price">{{ $auction->starting_price }}</span>
                    <input type="number" name="starting_price" class="edit form-control" value="{{ $auction->starting_price }}" style="display: none;">
                </td>
                <td>
                    <span class="display display-starting_date">{{ $auction->starting_date }}</span>
                    <input type="datetime-local" name="starting_date" class="edit form-control"
                        value="{{ date('Y-m-d\TH:i', strtotime($auction->starting_date)) }}" style="display: none;">
                </td>

                <td>
                    <span class="display display-end_date">{{ $auction->end_date }}</span>
                    <input type="datetime-local" name="end_date" class="edit form-control"
                        value="{{ date('Y-m-d\TH:i', strtotime($auction->end_date)) }}" style="display: none;">
                </td>

                <td>
                    <span class="display display-buyout_price">{{ $auction->buyout_price }}</span>
                    <input type="number" name="buyout_price" class="edit form-control" value="{{ $auction->buyout_price }}" style="display: none;">
                </td>
                <td>
                    <img src="{{ $auction->getAuctionImage() }}" alt="Auction Image" style="max-width: 100px; max-height: 100px;" >
                </td>
                <td>
                    <span class="display display-description">{{ $auction->description }}</span>
                    <textarea name="description" class="edit form-control" style="display: none;">{{ $auction->description }}</textarea>
                </td>
                <td>
                    <span class="display display-status">{{ $auction->status ?? 'N/A' }}</span>
                    <input type="text" name="status" class="edit form-control" value="{{ $auction->status }}" style="display: none;">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $auction->id }}">Edit</button>
                    <button type="button" class="btn btn-sm btn-secondary cancel-btn" data-id="{{ $auction->id }}" style="display: none;">Discard</button>
                    <button type="submit" class="btn btn-sm btn-success save-btn" data-id="{{ $auction->id }}" style="display: none;">Save</button>
                
            </form>

            <!-- Delete Form -->
            <form method="post" action="{{ route('auctions.delete', $auction->id) }}" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this auction?')">Cancel</button>
            </form>
            </td>
        </tr>
    @endforeach
</tbody>

</table>
</div>
