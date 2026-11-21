export function initAccordion() {
    const accordions = document.querySelectorAll('[data-accordion]');
    if (!accordions.length) return;

    accordions.forEach((accordion) => {
        if (accordion.dataset.accordionBound || accordion.hasAttribute('data-publication-accordion')) {
            return;
        }
        accordion.dataset.accordionBound = 'true';

        accordion.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-accordion-trigger]');
            if (!trigger || !accordion.contains(trigger)) return;

            const item = trigger.closest('[data-accordion-item]');
            if (!item) return;

            const content = item.querySelector('[data-accordion-content]');
            const isOpen = item.classList.contains('is-open');

            if (isOpen) {
                item.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                if (content) content.hidden = true;
            } else {
                item.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                if (content) content.hidden = false;
            }
        });
    });
}
