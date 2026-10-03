function renumber(container: HTMLElement): void {
    const links = Array.from(
        container.querySelectorAll<HTMLElement>(
            '.js-social-link',
        ),
    );

    links.forEach((link, index): void => {
        const order = link.querySelector<HTMLInputElement>(
            'input[name$="[displayOrder]"]',
        );

        if (order) {
            order.value = String(index);
        }
    });
}

export function initAboutSocials(): void {
    const container = document.querySelector<HTMLElement>(
        '.js-about-socials',
    );

    if (!container) {
        return;
    }

    if (container.dataset.initialized === 'true') {
        return;
    }

    container.dataset.initialized = 'true';

    const list = container.querySelector<HTMLElement>(
        '.js-about-socials-list',
    );

    const addButton = container.querySelector<HTMLButtonElement>(
        '.js-social-link-add',
    );

    if (!list || !addButton) {
        return;
    }

    let index = Number(
        container.dataset.index ?? '0',
    );

    addButton.addEventListener('click', (): void => {
        const prototype = container.dataset.prototype;

        if (!prototype) {
            return;
        }

        const item = document.createElement('article');

        item.className = [
            'admin-social-link',
            'js-social-link',
        ].join(' ');

        item.innerHTML = `
            <div class="admin-social-link__fields">
                ${prototype.replace(
                    /__name__/g,
                    String(index),
                )}
            </div>

            <div class="admin-social-link__actions">
                <button
                    type="button"
                    class="admin-action js-social-link-up"
                    title="Monter"
                >
                    <i class="fa-solid fa-arrow-up"></i>
                </button>

                <button
                    type="button"
                    class="admin-action js-social-link-down"
                    title="Descendre"
                >
                    <i class="fa-solid fa-arrow-down"></i>
                </button>

                <button
                    type="button"
                    class="admin-action admin-action--danger js-social-link-remove"
                >
                    <i class="fa-solid fa-trash"></i>
                    Supprimer
                </button>
            </div>
        `;

        ++index;

        container.dataset.index = String(index);

        list.append(item);

        renumber(list);
    });

    list.addEventListener(
        'click',
        (event: MouseEvent): void => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const item = target.closest<HTMLElement>(
                '.js-social-link',
            );

            if (!item) {
                return;
            }

            if (target.closest('.js-social-link-remove')) {
                item.remove();
                renumber(list);

                return;
            }

            if (target.closest('.js-social-link-up')) {
                const previous = item.previousElementSibling;

                if (previous) {
                    list.insertBefore(
                        item,
                        previous,
                    );

                    renumber(list);
                }

                return;
            }

            if (target.closest('.js-social-link-down')) {
                const next = item.nextElementSibling;

                if (next) {
                    list.insertBefore(
                        next,
                        item,
                    );

                    renumber(list);
                }
            }
        },
    );

    renumber(list);
}
