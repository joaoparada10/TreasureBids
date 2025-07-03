// Handle input event for live search
document.getElementById('auction-search-box').addEventListener('input', function (event) {
    const query = event.target.value.trim(); // Get the input value
    const resultsContainer = document.getElementById('search-results');

    // Clear and hide results if query is empty
    if (query.length === 0) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        return;
    }

    // Fetch search results
    fetch(`/api/auctions/search?q=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.auctions && data.auctions.length > 0) {
                updateDropdownResults(data.auctions); // Update dropdown
                resultsContainer.style.display = 'block'; // Show dropdown
            } else {
                resultsContainer.innerHTML = '<p class="text-muted px-2">No results found</p>';
                resultsContainer.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching search results:', error);
            resultsContainer.innerHTML = '<p class="text-danger px-2">Error loading results</p>';
            resultsContainer.style.display = 'block';
        });
});

// Function to update the dropdown results dynamically
function updateDropdownResults(auctions) {
    const resultsContainer = document.getElementById('search-results');
    resultsContainer.innerHTML = ''; // Clear previous results

    auctions.forEach(auction => {
        const resultItem = document.createElement('a');
        resultItem.className = 'dropdown-item';
        resultItem.href = `/auctions/${auction.id}`; // Link to auction details page
        resultItem.textContent = auction.title;
        resultsContainer.appendChild(resultItem);
    });
}

// Hide the dropdown when clicking outside the search form
document.addEventListener('click', function (event) {
    const searchForm = document.getElementById('auction-search-form');
    const resultsContainer = document.getElementById('search-results');
    if (!searchForm.contains(event.target)) {
        resultsContainer.style.display = 'none';
    }
});

// Prevent hiding dropdown when interacting with it
document.getElementById('search-results').addEventListener('click', function (event) {
    event.stopPropagation();
});





/* FILTER *//* FILTER *//* FILTER *//* FILTER *//* FILTER *//* FILTER *//* FILTER *//* FILTER */



const params = new URLSearchParams(window.location.search);

    // Get the values of the query parameters
    const min_price = params.get('min_price'); // Returns '123'
    const max_price = params.get('max_price'); // Returns '123'
    const status = params.get('status'); // Returns 'active'

    const prices = document.querySelectorAll('.card-highest-bid');
    console.log(prices)

    prices.forEach(node => {
        const actual_price = parseInt(node.innerText.substring(1))
        console.log("inside")
        console.log(min_price)
        console.log(max_price)
        if(actual_price && min_price){
            if(min_price > actual_price)
                node.parentElement.parentElement.parentElement.parentElement.style.display ="none"
        }

        if(actual_price && max_price){
            if(max_price < actual_price)
                node.parentElement.parentElement.parentElement.parentElement.style.display ="none"
        }
    })