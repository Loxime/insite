export function initAnnouncementDelete(): void {
    const forms = document.querySelectorAll<HTMLFormElement>(
        '.js-announcement-delete',
    );

    forms.forEach((form) => {
        form.addEventListener('submit', (event: SubmitEvent): void => {
            if (!window.confirm(
                'Supprimer définitivement cette annonce ?',
            )) {
                event.preventDefault();
            }
        });
    });
}
