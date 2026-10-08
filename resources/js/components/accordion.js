export function initAccordion() {
    const accordions = document.querySelectorAll('[data-accordion]');
    if (!accordions.length) return;

    accordions.forEach((accordion) => {
        const triggers = accordion.querySelectorAll('[data-accordion-trigger]');

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
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
    });
}
