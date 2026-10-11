import type { APIRoute } from 'astro';

export const GET: APIRoute = ({ site }) => {
  if (!site) {
    throw new Error(
      'La propriété site est obligatoire dans astro.config.mjs.',
    );
  }

  const sitemap = new URL('/sitemap-index.xml', site);

  const content = [
    'User-agent: *',
    'Allow: /',
    `Sitemap: ${sitemap.href}`,
    '',
  ].join('\n');

  return new Response(content, {
    headers: {
      'Content-Type': 'text/plain; charset=utf-8',
    },
  });
};
