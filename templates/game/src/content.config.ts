import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { z } from 'astro/zod';
import { contrastRatio, blendHex } from './lib/contrast';

const requiredText = z.string().trim().min(1);

const hexColor = z.string().regex(
  /^#[0-9a-fA-F]{6}$/,
  'Une couleur doit être au format #RRGGBB',
);

const publicUrl = z.url().refine(
  (value) => /^https?:\/\//i.test(value),
  'Une URL HTTP ou HTTPS est requise',
);


const themeSchema = z.strictObject({
  bg: hexColor,
  accent: hexColor,
  text: hexColor,
}).superRefine((theme, ctx) => {
  const isHex = (value: string) =>
    /^#[0-9a-fA-F]{6}$/.test(value);

  if (
    !isHex(theme.bg) ||
    !isHex(theme.accent) ||
    !isHex(theme.text)
  ) {
    return;
  }

  const subduedText = blendHex(
    theme.text,
    theme.bg,
    0.75,
  );

  const textContrast = contrastRatio(
    subduedText,
    theme.bg,
  );

  if (textContrast < 4.5) {
    ctx.addIssue({
      code: 'custom',
      path: ['text'],
      message:
        'Contraste WCAG AA insuffisant pour le texte '
        + `(opacité 75 % : ${textContrast.toFixed(2)}:1).`,
    });
  }

  const accentContrast = contrastRatio(
    theme.accent,
    theme.bg,
  );

  if (accentContrast < 4.5) {
    ctx.addIssue({
      code: 'custom',
      path: ['accent'],
      message:
        'Contraste WCAG AA insuffisant pour '
        + `la couleur d'accent (${accentContrast.toFixed(2)}:1).`,
    });
  }
});

const game = defineCollection({
  loader: glob({
    pattern: 'page.yaml',
    base: './src/content/game',
  }),

  schema: ({ image }) => z.strictObject({
    title: requiredText,
    description: requiredText.max(160),
    lang: requiredText.default('fr'),

    theme: themeSchema,

    seo: z.strictObject({
      ogImage: image(),
      ogImageAlt: requiredText,
    }),

    hero: z.strictObject({
      image: image(),
      imageAlt: requiredText,
      logo: image(),
      logoAlt: requiredText,
      tagline: requiredText,
    }),

    features: z.array(z.strictObject({
      title: requiredText,
      body: requiredText,
      image: image(),
      imageAlt: requiredText,
      bullets: z.array(requiredText).optional(),
    })).min(1),

    stores: z.array(z.strictObject({
      name: requiredText,
      url: publicUrl,
      logo: image(),
      logoAlt: requiredText,
    })).min(1),

    packs: z.array(z.strictObject({
      name: requiredText,
      blurb: requiredText,
      image: image(),
      imageAlt: requiredText,
    })).optional(),

    gallery: z.array(z.strictObject({
      image: image(),
      alt: requiredText,
    })).optional(),

    press: z.array(z.strictObject({
      quote: requiredText,
      source: requiredText,
      score: requiredText.optional(),
    })).optional(),

    team: z.array(z.strictObject({
      name: requiredText,
      role: requiredText,
      bio: requiredText.optional(),
    })).optional(),

    socials: z.array(z.strictObject({
      network: requiredText,
      url: publicUrl,
    })).min(1),
  }),
});

export const collections = { game };
