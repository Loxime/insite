export function initGameLinks(): void {
    const container = document.querySelector<HTMLElement>(
        '.js-game-links',
    );

    if (!container || container.dataset.initialized === 'true') {
        return;
    }

    container.dataset.initialized = 'true';

    const list = container.querySelector<HTMLElement>(
        '.js-game-links-list',
    );

    const addButton = container.querySelector<HTMLButtonElement>(
        '.js-game-link-add',
    );

    if (!list || !addButton) {
        return;
    }

    let index = Number(container.dataset.index ?? '0');

    const updateOrder = (): void => {
        const items = list.querySelectorAll<HTMLElement>(
            '.js-game-link',
        );

        items.forEach((item, order): void => {
            const input = item.querySelector<HTMLInputElement>(
                'input[name$="[displayOrder]"]',
            );

            if (input) {
                input.value = String(order);
            }
        });
    };

    addButton.addEventListener('click', (): void => {
        const prototype = container.dataset.prototype;

        if (!prototype) {
            return;
        }

        const item = document.createElement('article');

        item.className = 'admin-game-link js-game-link';

        item.innerHTML = `
            <div class="admin-game-link__fields">
                ${prototype.replace(/__name__/g, String(index))}
            </div>

            <button
                type="button"
                class="admin-action admin-action--danger js-game-link-remove"
            >
                Supprimer
            </button>
        `;

        ++index;

        container.dataset.index = String(index);

        list.append(item);

        updateOrder();
    });

    list.addEventListener(
        'click',
        (event: MouseEvent): void => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const button = target.closest(
                '.js-game-link-remove',
            );

            if (!button) {
                return;
            }

            button.closest('.js-game-link')?.remove();

            updateOrder();
        },
    );

    updateOrder();
}
