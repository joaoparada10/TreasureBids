document.addEventListener('DOMContentLoaded', function () {
    // Get the section from the query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeSection = urlParams.get('section');

    // Default to 'user-details' if no section is specified
    const defaultSection = 'user-details';
    const sectionToShow = activeSection || defaultSection;

    // Hide all sections and show the active one
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.add('d-none'));

    const targetSection = document.getElementById(sectionToShow);
    if (targetSection) {
        targetSection.classList.remove('d-none');
    }

    // Update the active menu item
    const menuItems = document.querySelectorAll('.menu-item .nav-link');
    menuItems.forEach(item => item.parentElement.classList.remove('selected'));

    const activeMenuItem = document.querySelector(`.menu-item .nav-link[data-content="${sectionToShow}"]`);
    if (activeMenuItem) {
        activeMenuItem.parentElement.classList.add('selected');
    }

    // Add click event listeners to sidebar buttons
    menuItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove 'active' from all menu items
            menuItems.forEach(link => link.parentElement.classList.remove('active'));

            // Add 'active' to the clicked menu item
            this.parentElement.classList.add('active');

            // Hide all sections
            sections.forEach(section => section.classList.add('d-none'));

            // Show the corresponding section
            const contentId = this.getAttribute('data-content');
            const targetSection = document.getElementById(contentId);

            if (targetSection) {
                targetSection.classList.remove('d-none');
            } else {
                console.error(`No section found with ID "${contentId}"`);
            }

            // Update the query parameter without reloading
            const newUrl = `${window.location.pathname}?section=${contentId}`;
            window.history.pushState({}, '', newUrl);
        });
    });
});



document.addEventListener('DOMContentLoaded', function () {
    const editProfileButton = document.querySelector('.edit-profile-btn');
    const cancelEditButton = document.querySelector('.cancel-edit-btn');
    const userDetailsSection = document.getElementById('user-details');
    const editProfileSection = document.getElementById('edit-profile');

    // Show edit form
    editProfileButton.addEventListener('click', function () {
        userDetailsSection.classList.add('d-none');
        editProfileSection.classList.remove('d-none');
    });

    // Cancel edit
    cancelEditButton.addEventListener('click', function () {
        editProfileSection.classList.add('d-none');
        userDetailsSection.classList.remove('d-none');
    });
});

//display image preview
document.addEventListener('DOMContentLoaded', function () {
    const profileInput = document.getElementById('picture');
    const previewImg = document.getElementById('preview-img');

    profileInput.addEventListener('change', function (event) {
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                previewImg.src = e.target.result; // Update image preview
            };

            reader.readAsDataURL(file); // Read the file
        } else {
            previewImg.src = "{{ asset('profile/no_image.png') }}"; // Default fallback image
        }
    });
});

function bankCardLogic(){
    // JavaScript to handle the popup card
    const overlay = document.getElementById('overlay');
    const showCardBtn = document.querySelector('.popup');
    const closeCard = document.getElementById('closeCard');

    // Show the overlay and card when the button is clicked
    showCardBtn.addEventListener('click', () => {
    overlay.style.display = 'flex';
    });

    // Hide the overlay and card when the close button is clicked
    closeCard.addEventListener('click', () => {
    overlay.style.display = 'none';
    });
}