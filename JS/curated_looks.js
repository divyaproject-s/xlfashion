/**
 * Curated Looks Slider Logic - Refined for Active Card Detection
 */
document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.looks-slider-container');
    const prevBtn = document.getElementById('prevLook');
    const nextBtn = document.getElementById('nextLook');
    const cards = document.querySelectorAll('.look-card');

    if (!slider || !prevBtn || !nextBtn || cards.length === 0) return;

    // Scroll amount calculation
    const getScrollAmount = () => {
        const cardWidth = cards[0].offsetWidth;
        const gap = 30;
        return cardWidth + gap;
    };

    nextBtn.addEventListener('click', () => {
        slider.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
    });

    prevBtn.addEventListener('click', () => {
        slider.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
    });

    /**
     * Detect the card closest to the center of the slider and mark it active
     */
    const updateActiveCard = () => {
        const sliderRect = slider.getBoundingClientRect();
        const sliderCenter = sliderRect.left + sliderRect.width / 2;

        let closestCard = null;
        let minDistance = Infinity;

        cards.forEach(card => {
            const cardRect = card.getBoundingClientRect();
            const cardCenter = cardRect.left + cardRect.width / 2;
            const distance = Math.abs(sliderCenter - cardCenter);

            if (distance < minDistance) {
                minDistance = distance;
                closestCard = card;
            }
        });

        cards.forEach(card => card.classList.remove('active'));
        if (closestCard) {
            closestCard.classList.add('active');
        }
    };

    // Listen for scroll events with a bit of throttling
    let scrollTimeout;
    slider.addEventListener('scroll', () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(updateActiveCard, 50);
    });

    // Handle button visibility
    slider.addEventListener('scroll', () => {
        const atStart = slider.scrollLeft <= 5;
        const atEnd = Math.ceil(slider.scrollLeft + slider.clientWidth) >= slider.scrollWidth - 5;

        prevBtn.style.opacity = atStart ? '0' : '1';
        prevBtn.style.pointerEvents = atStart ? 'none' : 'auto';

        nextBtn.style.opacity = atEnd ? '0' : '1';
        nextBtn.style.pointerEvents = atEnd ? 'none' : 'auto';
    });

    // Initial positioning: Scroll to middle if many cards
    if (cards.length > 2) {
        // Trigger initial active card detection
        setTimeout(updateActiveCard, 100);
    }

    // Trigger scroll listener once to set initial arrow state
    slider.dispatchEvent(new Event('scroll'));
});
