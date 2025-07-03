<div class="favorite" onclick="toggleFollow({{ $auction->id }})">
                @if ($isFollowing)
                    <span id="follow-star">&#9733;</span>
                @else
                    <span id="follow-star">&#9734;</span>
                @endif
            </div>