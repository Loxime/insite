export function initAnnouncementPopup(): void {
    const dialog = document.querySelector<HTMLDialogElement>(
        '.js-announcement-dialog',
    );

    if (!dialog) {
        return;
    }

    const id = dialog.dataset.announcementId;
    const revision = dialog.dataset.announcementRevision;

    if (!id || !revision) {
        return;
    }

    const storageKey = `insite:announcement:dismissed:${id}:${revision}`;

    try {
        if (sessionStorage.getItem(storageKey) === '1') {
            return;
        }
    } catch {
        // La popup reste fonctionnelle sans stockage de session.
    }

    const rememberDismissal = (): void => {
        try {
            sessionStorage.setItem(storageKey, '1');
        } catch {
            // Le stockage peut être indisponible.
        }
    };

    const closeButton = dialog.querySelector<HTMLButtonElement>(
        '.js-announcement-close',
    );

    const cta = dialog.querySelector<HTMLAnchorElement>(
        '.js-announcement-cta',
    );

    dialog.addEventListener('close', rememberDismissal);

    closeButton?.addEventListener('click', (): void => {
        dialog.close();
    });

    cta?.addEventListener('click', rememberDismissal);

    dialog.addEventListener('click', (event: MouseEvent): void => {
        if (event.target !== dialog) {
            return;
        }

        const bounds = dialog.getBoundingClientRect();

        const outside =
            event.clientX < bounds.left ||
            event.clientX > bounds.right ||
            event.clientY < bounds.top ||
            event.clientY > bounds.bottom;

        if (outside) {
            dialog.close();
        }
    });

    dialog.showModal();
}
