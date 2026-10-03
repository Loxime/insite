import { initEditors } from './editor';

function renumber(container: HTMLElement): void {
    const sections = Array.from(
        container.querySelectorAll<HTMLElement>(
            '.js-game-section',
        ),
    );

    sections.forEach((section, index): void => {
        const number = section.querySelector<HTMLElement>(
            '.js-game-section-number',
        );

        const order = section.querySelector<HTMLInputElement>(
            'input[name$="[displayOrder]"]',
        );

        if (number) {
            number.textContent = String(index + 1);
        }

        if (order) {
            order.value = String(index);
        }
    });
}

export function initGameSections(): void {
    const container = document.querySelector<HTMLElement>(
        '.js-game-sections',
    );

    if (!container) {
        return;
    }

    if (container.dataset.initialized === 'true') {
        return;
    }

    container.dataset.initialized = 'true';

    const list = container.querySelector<HTMLElement>(
        '.js-game-sections-list',
    );

    const addButton = container.querySelector<HTMLButtonElement>(
        '.js-game-section-add',
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

        const html = prototype.replace(
            /__name__/g,
            String(index),
        );

        ++index;

        container.dataset.index = String(index);

        const section = document.createElement('article');

        section.className = [
            'admin-game-section',
            'admin-game-section--new',
            'admin-game-section--without-image',
            'js-game-section',
        ].join(' ');

        section.innerHTML = `
            <header class="admin-game-section__header">
                <h3>
                    Section
                    <span class="js-game-section-number"></span>
                </h3>

                <div class="admin-game-section__controls">
                    <button
                        type="button"
                        class="admin-action js-game-section-up"
                        title="Monter"
                    >
                        <i class="fa-solid fa-arrow-up"></i>
                    </button>

                    <button
                        type="button"
                        class="admin-action js-game-section-down"
                        title="Descendre"
                    >
                        <i class="fa-solid fa-arrow-down"></i>
                    </button>

                    <button
                        type="button"
                        class="admin-action admin-action--danger js-game-section-remove"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Supprimer
                    </button>
                </div>
            </header>

            <div class="admin-game-section__fields">
                ${html}
            </div>
        `;

        list.append(section);

        renumber(list);
        initEditors();
    });

    list.addEventListener(
        'click',
        (event: MouseEvent): void => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const section = target.closest<HTMLElement>(
                '.js-game-section',
            );

            if (!section) {
                return;
            }

            if (target.closest('.js-game-section-remove')) {
                section.remove();
                renumber(list);

                return;
            }

            if (target.closest('.js-game-section-up')) {
                const previous = section.previousElementSibling;

                if (previous) {
                    list.insertBefore(
                        section,
                        previous,
                    );

                    renumber(list);
                }

                return;
            }

            if (target.closest('.js-game-section-down')) {
                const next = section.nextElementSibling;

                if (next) {
                    list.insertBefore(
                        next,
                        section,
                    );

                    renumber(list);
                }
            }
        },
    );

    renumber(list);
}
