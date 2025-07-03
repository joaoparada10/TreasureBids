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
            } if (hours > 0 && days == 0) {
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
                element.textContent = `Auction starts in: ${formatCountdown(timeLeftToStart)}`;
            } else if (now >= startTime && now < endTime) {
                const timeLeftToEnd = endTime - now;
                element.textContent = `Auction ends in: ${formatCountdown(timeLeftToEnd)}`;
            } else {
                element.textContent = "Auction Ended";
            }
            // Schedule next update
            requestAnimationFrame(updateCountdown);
        }
        // Start the countdown
        updateCountdown();
    });
});