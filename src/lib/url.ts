// Делает внутреннюю абсолютную ссылку ("/uslugi/") учитывающей base-path
// сборки (import.meta.env.BASE_URL). На обычном хостинге base = "/", и
// withBase(path) === path. На GitHub Pages (base = "/art-lider/") ссылка
// получает нужный префикс.
export function withBase(path: string): string {
  const base = import.meta.env.BASE_URL || '/';
  const clean = path.replace(/^\//, '');
  return clean ? base + clean : base;
}
