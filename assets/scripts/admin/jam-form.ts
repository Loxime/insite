export function initJamForm(): void {
    const form = document.querySelector<HTMLElement>(
        '.js-jam-form',
    );

    if (!form) {
        return;
    }

    const schedule = form.querySelector<HTMLElement>(
        '.js-jam-schedule',
    );

    const list = form.querySelector<HTMLElement>(
        '.js-jam-days',
    );

    const dates = form.querySelector<HTMLElement>(
        '.js-jam-dates',
    );

    const mode = form.querySelector<HTMLSelectElement>(
        'select[name$="[mode]"]',
    );

    const permanent = form.querySelector<HTMLInputElement>(
        'input[name$="[permanent]"]',
    );

    const start = form.querySelector<HTMLInputElement>(
        'input[name$="[startDate]"]',
    );

    const end = form.querySelector<HTMLInputElement>(
        'input[name$="[endDate]"]',
    );

    const addButton = form.querySelector<HTMLButtonElement>(
        '.js-jam-day-add',
    );

    const generateButton =
        form.querySelector<HTMLButtonElement>(
            '.js-jam-generate',
        );

    if (
        !schedule
        || !list
        || !mode
        || !permanent
    ) {
        return;
    }

    let index = Number(
        schedule.dataset.index ?? '0',
    );

    const syncVisibility = (): void => {
        if (permanent.checked) {
            mode.value = 'random';
        }

        if (dates) {
            dates.hidden = permanent.checked;
        }

        schedule.hidden =
            permanent.checked
            || mode.value !== 'fixed';
    };

    const createDay = (
        dateValue = '',
    ): void => {
        const prototype = schedule.dataset.prototype;

        if (!prototype) {
            return;
        }

        const item = document.createElement('article');

        item.className =
            'admin-jam-day js-jam-day';

        item.innerHTML = `
            <div class="admin-jam-day__fields">
                ${prototype.replace(
                    /__name__/g,
                    String(index),
                )}
            </div>

            <button
                type="button"
                class="admin-action admin-action--danger js-jam-day-remove"
            >
                Supprimer
            </button>
        `;

        ++index;

        schedule.dataset.index = String(index);

        list.append(item);

        const dateInput =
            item.querySelector<HTMLInputElement>(
                'input[name$="[date]"]',
            );

        if (dateInput && dateValue) {
            dateInput.value = dateValue;
        }
    };

    const formatDate = (date: Date): string => {
        const year = date.getFullYear();

        const month = String(
            date.getMonth() + 1,
        ).padStart(2, '0');

        const day = String(
            date.getDate(),
        ).padStart(2, '0');

        return `${year}-${month}-${day}`;
    };

    addButton?.addEventListener(
        'click',
        (): void => {
            createDay();
        },
    );

    generateButton?.addEventListener(
        'click',
        (): void => {
            if (!start?.value || !end?.value) {
                window.alert(
                    'Renseigne d’abord les dates de début et de fin.',
                );

                return;
            }

            const from = new Date(
                `${start.value}T00:00:00`,
            );

            const to = new Date(
                `${end.value}T00:00:00`,
            );

            if (from > to) {
                window.alert(
                    'La date de fin doit être postérieure au début.',
                );

                return;
            }

            if (
                list.children.length > 0
                && !window.confirm(
                    'Remplacer la programmation existante ?',
                )
            ) {
                return;
            }

            list.innerHTML = '';

            const cursor = new Date(from);

            while (cursor <= to) {
                createDay(
                    formatDate(cursor),
                );

                cursor.setDate(
                    cursor.getDate() + 1,
                );
            }
        },
    );

    list.addEventListener(
        'click',
        (event: MouseEvent): void => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const button = target.closest(
                '.js-jam-day-remove',
            );

            if (!button) {
                return;
            }

            button
                .closest('.js-jam-day')
                ?.remove();
        },
    );

    mode.addEventListener(
        'change',
        syncVisibility,
    );

    permanent.addEventListener(
        'change',
        syncVisibility,
    );

    syncVisibility();
}
