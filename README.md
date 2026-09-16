# Арт Лидер — сайт компании

Сайт-визитка + каталог услуг + портфолио для компании «Арт Лидер» (ремонт квартир и офисов, Лысьва и Чусовой, Пермский край). Собран на [Astro](https://astro.build) как статический сайт — быстрый, без сервера рендеринга, подходит для любого хостинга с PHP (форма обратной связи работает через `public/api/send.php`).

## Технологии

- **Astro** (static output) — HTML/CSS с минимумом JS
- **Content Collections** — портфолио объектов хранится как Markdown с фото рядом
- **astro:assets** — автоматическая конвертация фото в WebP, `srcset`, `loading="lazy"`
- Чистый CSS с переменными (без Tailwind/UI-библиотек)
- Самостоятельно захостенные шрифты (Oswald + Inter, кириллица)
- `@astrojs/sitemap` — автогенерация `sitemap-index.xml`
- `public/api/send.php` — обработчик формы (Telegram-бот + email)

## Команды

| Команда | Действие |
| :--- | :--- |
| `npm install` | Установка зависимостей |
| `npm run dev` | Локальный сервер на `localhost:4321` |
| `npm run build` | Сборка в `./dist/` |
| `npm run preview` | Просмотр собранного сайта локально |
| `npm run check` | Проверка типов/шаблонов Astro |

В этом проекте (см. `CLAUDE.md`) для запуска dev-сервера в фоне используется `astro dev --background`, управление — `astro dev stop` / `astro dev status` / `astro dev logs`.

## Структура проекта

```
src/
  config/site.ts        # ЕДИНЫЙ источник контактов, адреса, ссылок, ID счётчиков
  data/services.ts       # 10 услуг: описания, цены, FAQ
  data/reviews.ts         # отзывы (сейчас — заглушки, см. TODO)
  data/faq.ts             # общие вопросы для главной
  content/projects/...    # объекты портфолио (Markdown + фото рядом)
  content.config.ts       # схема коллекции projects (zod)
  components/             # Header, Footer, ContactForm, BeforeAfter, Gallery и т.д.
  layouts/BaseLayout.astro
  pages/                  # все страницы сайта
  styles/                 # global.css, fonts.css
public/
  api/send.php            # обработчик формы (см. ниже)
  api/config.example.php  # шаблон конфига — скопировать в config.php
  fonts/                  # самохостящиеся шрифты
  robots.txt, site.webmanifest, favicon.*, og-image.jpg
  .htaccess               # HTTPS-редирект, кэш, сжатие, 404
```

## Как добавить новый объект в портфолио

Портфолио — это коллекция `src/content/projects/`. Каждый объект — отдельная папка с `index.md` и фото рядом.

1. Создайте папку, например `src/content/projects/moya-kvartira/`.
2. Скопируйте туда фото: `gallery-01.jpg`, `gallery-02.jpg`, … — для обычной галереи; `before-1.jpg` + `after-1.jpg` — для пары «до/после» (можно несколько пар: `before-2.jpg`/`after-2.jpg` и т.д.).
3. Создайте файл `index.md` по образцу:

   ```markdown
   ---
   title: "Ремонт кухни в квартире"
   city: "Лысьва"                     # Лысьва | Чусовой | другое
   objectType: "Квартира"             # Квартира | Офис | Дом | Коммерческое помещение
   services: ["remont-kvartir-i-ofisov", "elektromontazh"]   # slug'и из src/data/services.ts
   area: 12                            # м², необязательно
   duration: "2 недели"                # необязательно
   date: 2026-06-01
   cover: "./gallery-01.jpg"           # обложка карточки
   featured: true                       # показывать на главной (6 самых свежих featured-объектов)
   beforeAfter:                         # необязательно
     - before: "./before-1.jpg"
       after: "./after-1.jpg"
       caption: "Кухня"
   gallery:                             # необязательно
     - src: "./gallery-01.jpg"
       alt: "Ремонт кухни в Лысьве — общий вид"
   ---

   Короткое описание объекта — 2–4 предложения о том, что было сделано.
   ```

4. Сохраните — объект автоматически появится на `/raboty/` и (если `featured: true`) на главной странице. Отдельная страница объекта создастся по адресу `/raboty/moya-kvartira/`.

Список `slug` услуг для поля `services` — в `src/data/services.ts` (первое поле каждой услуги, `slug`).

## Форма обратной связи → Telegram-бот

Заявки с сайта уходят в Telegram и дублируются на email через `public/api/send.php`.

1. Скопируйте `public/api/config.example.php` в `public/api/config.php` (этот файл в `.gitignore`, в репозиторий не попадёт).
2. Создайте Telegram-бота:
   - напишите [@BotFather](https://t.me/BotFather) → `/newbot` → следуйте инструкциям → получите **токен**;
   - создайте группу/канал для заявок, добавьте туда бота;
   - узнайте **chat_id** группы — самый простой способ: добавить в группу [@userinfobot](https://t.me/userinfobot) на минуту, либо отправить любое сообщение в группу и открыть `https://api.telegram.org/bot<ТОКЕН>/getUpdates` — там будет `chat.id` (для групп обычно отрицательное число).
3. Впишите токен и chat_id в `public/api/config.php`.
4. Укажите рабочий email в `notify_email` — на него будут дублироваться заявки через `mail()` (должен быть настроен на хостинге).
5. Загрузите `config.php` на хостинг вместе с остальным сайтом (он не публикуется через git, поэтому копируйте вручную/через FTP).

Антиспам уже встроен: honeypot-поле, проверка времени заполнения формы, лимит запросов по IP (файловый, без базы данных).

## Деплой

### Вариант А: обычный хостинг с PHP (Beget, Timeweb и т. п.) по FTP

1. `npm run build` — соберёт сайт в `dist/`.
2. Загрузите **содержимое** `dist/` в корень сайта на хостинге (обычно `public_html/`) по FTP/SFTP.
3. Отдельно загрузите `public/api/config.php` (см. раздел выше) — он не попадает в `dist/`, если вы создали его после последней сборки, поэтому проверьте, что `public/api/config.php` тоже скопирован в `dist/api/` при сборке (или загрузите вручную рядом с `send.php`).
4. Проверьте, что `.htaccess` загрузился (некоторые FTP-клиенты скрывают файлы с точкой в начале имени).
5. Убедитесь, что на хостинге включён PHP 8+ и функция `mail()` или настроен SMTP.

### Вариант Б: автодеплой через GitHub Actions по FTP/SFTP

1. Инициализируйте git-репозиторий (`git init`), если ещё не сделано, и запушьте проект на GitHub.
2. В настройках репозитория добавьте secrets: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` (или SSH-ключ для SFTP).
3. Добавьте workflow `.github/workflows/deploy.yml`, например:

   ```yaml
   name: Deploy
   on:
     push:
       branches: [main]
   jobs:
     deploy:
       runs-on: ubuntu-latest
       steps:
         - uses: actions/checkout@v4
         - uses: actions/setup-node@v4
           with: { node-version: 22 }
         - run: npm ci
         - run: npm run build
         - uses: SamKirkland/FTP-Deploy-Action@v4
           with:
             server: ${{ secrets.FTP_SERVER }}
             username: ${{ secrets.FTP_USERNAME }}
             password: ${{ secrets.FTP_PASSWORD }}
             local-dir: ./dist/
             exclude: |
               **/api/config.php
   ```

4. `public/api/config.php` при таком подходе **не должен** попадать в репозиторий — загрузите его на хостинг один раз вручную и исключите из синхронизации (как в примере выше), иначе деплой будет его удалять.

## Чек-лист после запуска (SEO)

- [ ] Добавить сайт в [Яндекс.Вебмастер](https://webmaster.yandex.ru) и [Google Search Console](https://search.google.com/search-console), вписать коды подтверждения в `src/config/site.ts` (`yandexVerification`, `googleVerification`).
- [ ] Отправить `sitemap-index.xml` в обоих вебмастерах.
- [ ] Создать/обновить карточки компании в Яндекс.Бизнесе и 2ГИС с теми же данными (адрес, телефон, часы), что в `src/config/site.ts`.
- [ ] Указать сайт в описании группы ВКонтакте.
- [ ] Подключить Яндекс.Метрику: вписать ID счётчика в `yandexMetrikaId` в `src/config/site.ts` — счётчик подключится автоматически (загружается отложенно, через 1.5 c после `load`).

## Что уже проверено

- `npm run build` и `npm run check` проходят без ошибок.
- Внутренние ссылки на всех 33 страницах проверены скриптом — битых нет.
- Слайдер «до/после» проверен через Playwright: работает мышью (drag), клавиатурой (стрелки — это нативный `<input type="range">`), при отсутствии пары «до/после» показывается только галерея.
- Форма: клиентская валидация (пустое имя/телефон, обязательное согласие) и корректная обработка ошибки сети/сервера проверены через Playwright. Серверную часть (`send.php`) нужно проверить на реальном PHP-хостинге — локально PHP не запускался.
- Все страницы отрисованы в Chromium на десктопе (1280px) и мобильном (390px) без ошибок в консоли.
- Header/бургер-меню, нижняя мобильная панель, модальное окно заказа звонка, лайтбокс галереи — проверены вручную через автоматизированный браузер, работают.

## Что нужно проверить/доделать дополнительно

- Lighthouse-аудит (мобильный режим) — не запускался в этой сессии (нет Chrome DevTools/Lighthouse CLI в окружении сборки); рекомендуется прогнать вручную в Chrome DevTools после деплоя на реальный домен.
- Скриншоты вёрстки на 360/768/1440px — вёрстка проверена на 390/1280px через Playwright; itemized 360/768/1440 не снимались отдельно, но брейкпоинты в CSS (480/768/1024/1280) покрывают этот диапазон.

## TODO для владельца перед публикацией

Все ниже перечисленные значения — заглушки, сайт **соберётся и будет работать без них**, но их обязательно нужно заполнить до реального запуска:

**`src/config/site.ts`:**
- `url` — реальный домен сайта (сейчас `art-lider-remont.ru` как плейсхолдер)
- `address.geo.lat` / `lon` — точные координаты офиса (для микроразметки и карты)
- `legal.fullName`, `legal.inn`, `legal.ogrnip` — реквизиты ИП (нужны для футера и юридических страниц)
- `email` — рабочий email компании
- `yandexMetrikaId`, `yandexVerification`, `googleVerification` — счётчики и коды подтверждения
- `yandexSmartCaptchaKey` — опционально, если понадобится капча
- `messengers.max` — ссылка на MAX (сейчас `#`-заглушка)

**`public/api/config.php`** (создать из `config.example.php`):
- `telegram_bot_token`, `telegram_chat_id` — данные Telegram-бота
- `notify_email` — почта для дублирования заявок

**`src/data/services.ts`:**
- Все `priceFrom: null` в блоках `prices` — реальные цены по каждой услуге (на сайте до заполнения показывается «по запросу», это не ошибка, а осознанная заглушка)

**`src/data/reviews.ts`:**
- Реальные отзывы клиентов (сейчас 3 плейсхолдера с пометкой TODO)

**Фото:**
- Логотип — сейчас используется временный текстовый лого «АРТ·ЛИДЕР» с иконкой валика. Если появится готовый векторный логотип — заменить в `src/components/Header.astro` и `src/components/Footer.astro`, а также перегенерировать `favicon.*`/`og-image.jpg`.
- Все фото объектов и вывески уже взяты из `Фото/` и размещены по проекту — при появлении новых объектов используйте инструкцию выше.

**Домен и хостинг:**
- `public/robots.txt` — строка `Sitemap:` содержит placeholder-домен, обновить на реальный при смене `site.url`.
