import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://__SLUG__.example.com',

  output: 'static',

  integrations: [
    sitemap(),
  ],

  vite: {
    server: {
      allowedHosts: ['.localhost'],
    },
  },
});
