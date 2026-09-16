import { defineCollection, z } from 'astro:content';
import { glob } from 'astro/loaders';

const projects = defineCollection({
  loader: glob({ pattern: '**/index.md', base: './src/content/projects' }),
  schema: ({ image }) =>
    z.object({
      title: z.string(),
      city: z.enum(['Лысьва', 'Чусовой', 'другое']),
      objectType: z.enum(['Квартира', 'Офис', 'Дом', 'Коммерческое помещение']),
      services: z.array(z.string()),
      area: z.number().optional(),
      duration: z.string().optional(),
      date: z.coerce.date(),
      cover: image(),
      featured: z.boolean().default(false),
      beforeAfter: z
        .array(
          z.object({
            before: image(),
            after: image(),
            caption: z.string().optional(),
          }),
        )
        .optional(),
      gallery: z
        .array(
          z.object({
            src: image(),
            alt: z.string().optional(),
          }),
        )
        .optional(),
    }),
});

export const collections = { projects };
