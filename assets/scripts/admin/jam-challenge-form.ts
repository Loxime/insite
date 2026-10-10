function syncColorPreview(row: HTMLElement): void {
    const input = row.querySelector<HTMLInputElement>(
        'input[type="color"]',
    );
    const preview = row.querySelector<HTMLElement>(
        '.js-challenge-color-preview',
    );
    const label = row.querySelector<HTMLElement>(
        '.js-challenge-color-label',
    );

    if (!input || !preview || !label) {
        return;
    }

    const value = input.value || '#000000';

    preview.style.setProperty('--challenge-color', value);
    label.textContent = value.toUpperCase();
}

function enhanceColorRow(row: HTMLElement): void {
    if (!row.classList.contains('admin-challenge-color')) {
        return;
    }

    let field = row.querySelector<HTMLElement>(
        '.admin-challenge-color__field',
    );

    const input = row.querySelector<HTMLInputElement>(
        'input[type="color"]',
    );

    if (!input) {
        return;
    }

    if (!field) {
        field = document.createElement('div');
        field.className = 'admin-challenge-color__field';

        input.parentElement?.insertBefore(field, input);
        field.append(input);
    }

    let preview = row.querySelector<HTMLElement>(
        '.js-challenge-color-preview',
    );

    if (!preview) {
        preview = document.createElement('span');
        preview.className =
            'admin-challenge-color__preview js-challenge-color-preview';

        const label = document.createElement('span');
        label.className = 'js-challenge-color-label';

        preview.append(label);
        field.append(preview);
    }

    if (!input.dataset.previewBound) {
        input.addEventListener('input', (): void => {
            syncColorPreview(row);
        });

        input.dataset.previewBound = '1';
    }

    const remove = row.querySelector<HTMLButtonElement>(
        '.js-challenge-remove',
    );

    if (remove && !remove.dataset.iconized) {
        remove.innerHTML =
            '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
        remove.setAttribute('aria-label', 'Retirer la couleur');
        remove.dataset.iconized = '1';
    }

    syncColorPreview(row);
}

export function initJamChallengeForm(): void {
    const root = document.querySelector<HTMLElement>('.js-challenge-form');

    if (!root) {
        return;
    }

    root.querySelectorAll<HTMLElement>(
        '.js-challenge-collection',
    ).forEach(collection => {
        const items = collection.querySelector<HTMLElement>(
            '.js-challenge-items',
        );
        const add = collection.querySelector<HTMLButtonElement>(
            '.js-challenge-add',
        );
        const maximum = Number(collection.dataset.max ?? '12');

        if (!items || !add) {
            return;
        }

        const refresh = (): void => {
            const rows = items.querySelectorAll<HTMLElement>(
                '.js-challenge-row',
            );

            add.disabled = rows.length >= maximum;

            if (maximum === 3) {
                rows.forEach(row => {
                    enhanceColorRow(row);
                });
            }
        };

        add.addEventListener('click', (): void => {
            const prototype = collection.dataset.prototype;

            if (!prototype || add.disabled) {
                return;
            }

            const index = Number(collection.dataset.index ?? '0');
            const row = document.createElement('div');

            row.className = `js-challenge-row ${
                maximum === 3
                    ? 'admin-challenge-color'
                    : 'admin-challenge-platform'
            }`;

            if (maximum === 3) {
                const field = document.createElement('div');
                field.className = 'admin-challenge-color__field';
                field.innerHTML = prototype.replace(
                    /__name__/g,
                    String(index),
                );
                row.append(field);
            } else {
                const fields = document.createElement('div');
                fields.className = 'admin-form-row';
                fields.innerHTML = prototype.replace(
                    /__name__/g,
                    String(index),
                );
                row.append(fields);
            }

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className =
                'admin-button admin-button--secondary js-challenge-remove';

            if (maximum === 3) {
                remove.innerHTML =
                    '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
                remove.setAttribute('aria-label', 'Retirer la couleur');
            } else {
                remove.textContent = 'Retirer';
            }

            row.append(remove);
            items.append(row);

            collection.dataset.index = String(index + 1);

            refresh();
        });

        items.addEventListener('click', (event: MouseEvent): void => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const button = target.closest('.js-challenge-remove');

            if (!button) {
                return;
            }

            if (
                maximum === 3
                && items.querySelectorAll('.js-challenge-row').length <= 1
            ) {
                return;
            }

            button.closest('.js-challenge-row')?.remove();
            refresh();
        });

        refresh();
    });
}
