document.addEventListener('DOMContentLoaded', () => {
    // Get all auction cards
    const auctionCards = document.querySelectorAll('.preview-auction');
    const auctionContainer = document.querySelector('.auction-container');
    let currentIndex = 0; // Start with the first auction
    
    auctionContainer.style.transform = `translateX(0)`;

    // Show the first auction
    auctionCards[currentIndex].classList.add('active');
    
    // Function to show the next auction
    function showNextAuction() {
        // Hide the current auction
        auctionCards[currentIndex].classList.remove('active');
        
        // Increment index (loop back to 0 if at the last auction)
        currentIndex = (currentIndex + 1) % auctionCards.length;
        
        // Show the new auction
        auctionContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    // Function to show the previous auction
    function showPreviousAuction() {
        // Hide the current auction
        auctionCards[currentIndex].classList.remove('active');
        
        // Decrement index (loop back to the last auction if at the first one)
        currentIndex = (currentIndex - 1 + auctionCards.length) % auctionCards.length;
        
        // Show the new auction
        auctionContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    // Add event listeners for the navigation buttons
    document.getElementById('next').addEventListener('click', showNextAuction);
    document.getElementById('prev').addEventListener('click', showPreviousAuction);
});

