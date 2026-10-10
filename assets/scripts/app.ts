import '@fortawesome/fontawesome-free/css/all.min.css';
import '../styles/app.scss';

import { initToasts } from './ui/toasts';

document.addEventListener('DOMContentLoaded', (): void => {
    document.documentElement.classList.add('js-enabled');

    initToasts();
    if (document.querySelector('.js-announcement-delete')) {
        void import('./admin/announcement-delete').then(
            ({ initAnnouncementDelete }) => {
                initAnnouncementDelete();
            },
        );
    }

    if (document.querySelector('.js-announcement-form')) {
        void import('./admin/announcement-form').then(
            ({ initAnnouncementForm }) => {
                initAnnouncementForm();
            },
        );
    }


    if (document.querySelector('.js-announcement-dialog')) {
        void import('./ui/announcement-popup').then(
            ({ initAnnouncementPopup }) => {
                initAnnouncementPopup();
            },
        );
    }
});

if (document.querySelector('.js-ckeditor')) {
    void import('./admin/editor').then(({ initEditors }) => {
        initEditors();
    });
}

if (document.querySelector('.js-game-sections')) {
    void import('./admin/game-sections').then(
        ({ initGameSections }) => {
            initGameSections();
        },
    );
}

if (document.querySelector('.js-about-socials')) {
    void import('./admin/about-socials').then(
        ({ initAboutSocials }) => {
            initAboutSocials();
        },
    );
}

if (document.querySelector('.js-site-socials')) {
    void import('./admin/site-socials').then(
        ({ initSiteSocials }) => {
            initSiteSocials();
        },
    );
}

if (document.querySelector('.js-game-links')) {
    void import('./admin/game-links').then(
        ({ initGameLinks }) => {
            initGameLinks();
        },
    );
}

if (document.querySelector('.js-jam-form')) {
    void import('./admin/jam-form').then(
        ({ initJamForm }) => {
            initJamForm();
        },
    );
}


if (document.querySelector('.js-challenge-form')) {
    void import('./admin/jam-challenge-form').then(({ initJamChallengeForm }) => initJamChallengeForm());
}
if (document.querySelector('.js-jam-countdown')) {
    void import('./ui/jam-countdown').then(({ initJamCountdown }) => initJamCountdown());
}
