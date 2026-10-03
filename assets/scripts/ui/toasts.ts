const LEAVE_DURATION = 220;

function removeToast(toast: HTMLElement): void {
    if (toast.classList.contains('is-leaving')) {
        return;
    }

    toast.classList.add('is-leaving');

    window.setTimeout((): void => {
        toast.remove();
    }, LEAVE_DURATION);
}

export function initToasts(): void {
    const toasts = document.querySelectorAll<HTMLElement>(
        '.js-toast',
    );

    toasts.forEach((toast): void => {
        const closeButton =
            toast.querySelector<HTMLButtonElement>(
                '.js-toast-close',
            );

        const configuredDuration = Number(
            toast.dataset.duration ?? '5000',
        );

        let remaining = Number.isFinite(configuredDuration)
            ? configuredDuration
            : 5000;

        let startedAt = 0;
        let timer: number | undefined;

        const stopTimer = (): void => {
            if (timer === undefined) {
                return;
            }

            window.clearTimeout(timer);

            const elapsed = Date.now() - startedAt;

            remaining = Math.max(
                0,
                remaining - elapsed,
            );

            timer = undefined;
        };

        const startTimer = (): void => {
            if (remaining <= 0) {
                removeToast(toast);

                return;
            }

            startedAt = Date.now();

            timer = window.setTimeout((): void => {
                timer = undefined;

                removeToast(toast);
            }, remaining);
        };

        closeButton?.addEventListener(
            'click',
            (): void => {
                stopTimer();
                removeToast(toast);
            },
        );

        toast.addEventListener(
            'mouseenter',
            stopTimer,
        );

        toast.addEventListener(
            'mouseleave',
            startTimer,
        );

        toast.addEventListener(
            'focusin',
            stopTimer,
        );

        toast.addEventListener(
            'focusout',
            (): void => {
                if (!toast.contains(document.activeElement)) {
                    startTimer();
                }
            },
        );

        startTimer();
    });
}
