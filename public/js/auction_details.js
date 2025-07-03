document.addEventListener('DOMContentLoaded', function () {
    // Select all countdown elements
    const countdownElements = document.querySelectorAll('.time-left');
    countdownElements.forEach((element) => {
        const startTime = new Date(element.getAttribute('data-start-date')).getTime();
        const endTime = new Date(element.getAttribute('data-end-date')).getTime();
        // Function to format countdown text
        function formatCountdown(timeLeft) {
            const seconds = Math.floor((timeLeft / 1000) % 60);
            const minutes = Math.floor((timeLeft / 1000 / 60) % 60);
            const hours = Math.floor((timeLeft / 1000 / 60 / 60) % 24);
            const days = Math.floor(timeLeft / 1000 / 60 / 60 / 24);
            if (days > 0) {
                return `${days}d ${hours}h`;
            } else if (hours > 0 && days == 0) {
                return `${hours}h ${minutes}m ${seconds}s`;
            } else {
                return `${minutes}m ${seconds}s`;
            }
        }
        // Function to update the countdown
        function updateCountdown() {
            const now = new Date().getTime();
            if (now < startTime) {
                const timeLeftToStart = startTime - now;
                element.innerHTML = `<div style="color:#aaa;display: flex;align-self:flex-start">Auction starts in:</div> <span>${formatCountdown(timeLeftToStart)}</span>`;
            } else if (now >= startTime && now < endTime) {
                const timeLeftToEnd = endTime - now;
                element.innerHTML = `<div style="color:#aaa;display: flex;align-self:flex-start">Auction ends in:</div> <span>${formatCountdown(timeLeftToEnd)}</span>`;
            } else {
                element.textContent = "Auction Ended";
            }
        }
        
        // Start the countdown
        updateCountdown();
        setInterval(updateCountdown, 1000);
    });
});
// Toggle Follow Function
async function toggleFollow(auctionId) {
    try {
        const response = await fetch(`/auctions/${auctionId}/follow`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        });
        if (response.ok) {
            const data = await response.json();
            console.log(data);
            const followButton = document.getElementById("follow-star");
            if (data.message === 'Auction followed') {
                followButton.classList.remove("bi-star");
                followButton.classList.add("bi-star-fill");
            } else if (data.message === 'Auction unfollowed') {
                followButton.classList.add("bi-star");

                followButton.classList.remove("bi-star-fill");
            }
        } else {
            console.error('Failed to toggle follow status. Status:', response.status);
            alert('Unable to update follow status. Please try again.');
        }
    } catch (error) {
        console.error('Error toggling follow status:', error);
        alert('An error occurred while toggling follow status. Please try again.');
    }
}

function auctionCardRedirect(){
    document.addEventListener('DOMContentLoaded', function () {
        // Select all elements with the 'auction-card' class
        document.querySelectorAll('.auction-card').forEach(function (card) {
            // Add a click event listener
            card.addEventListener('click', function () {
                const url = card.getAttribute('data-href');
                if (url) {
                    // Redirect to the URL specified in 'data-href'
                    window.location.href = url;
                }
            });
        });
    });
}

function starRating(){
    // Get the star display container
    const starDisplay = document.getElementById('star-display');

    const averageRating = starDisplay.getAttribute('rating');

    // Generate stars based on the average rating
    for (let i = 1; i <= 5; i++) {
      const star = document.createElement('div');
      star.classList.add('star');
      if (i <= Math.floor(averageRating)) {
        star.classList.add('filled'); // Fully filled star
      } else if (i - averageRating < 1 && i - averageRating > 0) {
        star.classList.add('half'); // Half-filled star
      }
      starDisplay.appendChild(star);
    }

    // Display the average rating value (optional)
    document.getElementById('average-rating').textContent = averageRating.toFixed(1);
}