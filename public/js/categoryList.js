let categoryList = document.getElementById('category-list');

function scrollToNext(direction) {
    const scrollAmount = categoryList.clientWidth; // Scroll by the width of the visible area
    categoryList.scrollBy({ left: scrollAmount * (direction === 'right' ? 1 : -1), behavior: 'smooth' });
}
document.getElementById('scroll-arrow-right').addEventListener('click', function () {
    scrollToNext('right');
});
document.getElementById('scroll-arrow-left').addEventListener('click', function () {
    scrollToNext('left');
});