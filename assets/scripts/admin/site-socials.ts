export function initSiteSocials(): void {
    const container = document.querySelector<HTMLElement>(
        '.js-site-socials',
    );

    if (!container || container.dataset.initialized === 'true') {
        return;
    }

    container.dataset.initialized = 'true';

    const list = container.querySelector<HTMLElement>(
        '.js-site-socials-list',
    );

    const addButton = container.querySelector<HTMLButtonElement>(
        '.js-site-social-add',
    );

    if (!list || !addButton) {
        return;
    }

    let index = Number(container.dataset.index ?? '0');

    addButton.addEventListener('click', (): void => {
        const prototype = container.dataset.prototype;

        if (!prototype) {
            return;
        }

        const item = document.createElement('article');

        item.className = 'admin-site-social js-site-social';

        item.innerHTML = `
            <div class="admin-site-social__fields">
                ${prototype.replace(/__name__/g, String(index))}
            </div>

            <button
                type="button"
                class="admin-action admin-action--danger js-site-social-remove"
            >
                <i class="fa-solid fa-trash"></i>
                Supprimer
            </button>
        `;

        ++index;

        container.dataset.index = String(index);

        list.append(item);
    });

    list.addEventListener(
        'click',
        (event: MouseEvent): void => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const removeButton = target.closest(
                '.js-site-social-remove',
            );

            if (!removeButton) {
                return;
            }

            removeButton
                .closest('.js-site-social')
                ?.remove();
        },
    );
}
