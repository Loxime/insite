import {
    BlockQuote,
    Bold,
    ClassicEditor,
    Essentials,
    Heading,
    Italic,
    Link,
    List,
    Paragraph,
    Undo,
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

export function initEditors(): void {
    document
        .querySelectorAll<HTMLTextAreaElement>('.js-ckeditor')
        .forEach((element): void => {
            if (element.dataset.ckeditorInitialized === 'true') {
                return;
            }

            element.dataset.ckeditorInitialized = 'true';

            void ClassicEditor
                .create(element, {
                    licenseKey: 'GPL',
                    plugins: [
                        Essentials,
                        Paragraph,
                        Heading,
                        Bold,
                        Italic,
                        Link,
                        List,
                        BlockQuote,
                        Undo,
                    ],
                    toolbar: [
                        'undo',
                        'redo',
                        '|',
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'link',
                        '|',
                        'bulletedList',
                        'numberedList',
                        '|',
                        'blockQuote',
                    ],
                })
                .catch((error: unknown): void => {
                    delete element.dataset.ckeditorInitialized;

                    console.error(
                        'CKEditor initialization failed.',
                        error,
                    );
                });
        });
}
