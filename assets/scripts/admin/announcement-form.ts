export function initAnnouncementForm(): void {
    const form = document.querySelector<HTMLFormElement>(
        '.js-announcement-form',
    );

    if (!form) {
        return;
    }

    const selector = form.querySelector<HTMLSelectElement>(
        '[data-announcement-type]',
    );

    const banner = form.querySelector<HTMLElement>(
        '[data-announcement-section="banner"]',
    );

    const popup = form.querySelector<HTMLElement>(
        '[data-announcement-section="popup"]',
    );

    if (!selector || !banner || !popup) {
        return;
    }

    const update = (): void => {
        const isBanner = selector.value === 'banner';

        banner.hidden = !isBanner;
        popup.hidden = isBanner;
    };

    selector.addEventListener('change', update);
    update();
}
