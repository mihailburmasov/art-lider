// Единый источник правды по данным компании (NAP) и настройкам сайта.
// Меняйте контакты, ID счётчиков и домен только здесь.

export const site = {
  companyName: 'Арт Лидер',
  legalBrand: 'АРТ·ЛИДЕР',
  tagline: 'Ремонт квартир и офисов',
  director: 'Артур Гимадиев',

  // TODO: заменить на боевой домен перед запуском
  url: 'https://art-lider-remont.ru',

  address: {
    region: 'Пермский край',
    city: 'Лысьва',
    street: 'ул. Федосеева',
    house: '37',
    full: 'Пермский край, г. Лысьва, ул. Федосеева, 37',
    // TODO: указать точные координаты офиса (Яндекс.Карты → «Поделиться» → координаты)
    geo: {
      lat: 58.1,
      lon: 57.8,
    },
  },

  areaServed: ['Лысьва', 'Чусовой'],

  phone: {
    display: '+7 (952) 317-87-99',
    href: 'tel:+79523178799',
  },

  messengers: {
    whatsapp: 'https://wa.me/79523178799',
    telegram: 'https://t.me/+79523178799',
    // TODO: вставить ссылку на MAX
    max: '#',
    vk: 'https://vk.ru/remontartur',
  },

  vkSubscribers: '1,8 тыс.',

  // Цифры для блока статистики на первом экране
  stats: {
    yearsOnMarket: 10,
    objectsCompleted: '50+',
    staffCount: '30+',
    warrantyYears: 2,
  },

  workingHours: {
    weekdays: '10:00–18:00',
    saturday: '10:00–17:00',
    sunday: 'выходной',
    text: 'Пн–Пт 10:00–18:00, Сб 10:00–17:00, Вс — выходной',
  },

  openingHoursSpecification: [
    { days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], opens: '10:00', closes: '18:00' },
    { days: ['Saturday'], opens: '10:00', closes: '17:00' },
  ],

  // Реквизиты ИП — TODO: заполнить перед публикацией (нужны для футера и юр. страниц)
  legal: {
    fullName: 'TODO: ФИО индивидуального предпринимателя',
    inn: 'TODO: ИНН',
    ogrnip: 'TODO: ОГРНИП',
  },

  email: 'TODO@example.com', // TODO: указать рабочую почту

  // Аналитика и вебмастера — оставить пустым, чтобы не подключать скрипт
  yandexMetrikaId: '', // TODO: ID счётчика Яндекс.Метрики
  yandexVerification: '', // TODO: код подтверждения Яндекс.Вебмастера
  googleVerification: '', // TODO: код подтверждения Google Search Console
  yandexSmartCaptchaKey: '', // TODO: ключ Яндекс SmartCaptcha (опционально)

  themeColor: '#E11F23',

  socialImage: '/og-image.jpg',
} as const;

export type Site = typeof site;
