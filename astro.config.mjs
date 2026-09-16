// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import { site } from './src/config/site.ts';

// Обычная сборка (PHP-хостинг) — base "/" и домен из src/config/site.ts.
// Для превью на GitHub Pages workflow (.github/workflows/deploy-pages.yml)
// подставляет ASTRO_BASE=/art-lider/ и ASTRO_SITE_URL, т.к. Pages отдаёт
// сайт не с корня, а по пути /<имя-репозитория>/.
const base = process.env.ASTRO_BASE || '/';
const siteUrl = process.env.ASTRO_SITE_URL || site.url;

// https://astro.build/config
export default defineConfig({
  site: siteUrl,
  base,
  output: 'static',
  trailingSlash: 'always',
  integrations: [
    sitemap({
      filter: (page) => !page.includes('/spasibo/') && !page.endsWith('/404/'),
    }),
  ],
});
