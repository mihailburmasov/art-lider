import { site } from '../config/site';

export function localBusinessJsonLd() {
  return {
    '@context': 'https://schema.org',
    '@type': 'HomeAndConstructionBusiness',
    name: site.companyName,
    alternateName: site.legalBrand,
    url: site.url,
    logo: new URL('/favicon.svg', site.url).toString(),
    image: new URL(site.socialImage, site.url).toString(),
    telephone: site.phone.href.replace('tel:', ''),
    priceRange: '₽₽',
    address: {
      '@type': 'PostalAddress',
      streetAddress: `${site.address.street}, ${site.address.house}`,
      addressLocality: site.address.city,
      addressRegion: site.address.region,
      addressCountry: 'RU',
    },
    geo: {
      '@type': 'GeoCoordinates',
      latitude: site.address.geo.lat,
      longitude: site.address.geo.lon,
    },
    areaServed: site.areaServed.map((city) => ({ '@type': 'City', name: city })),
    openingHoursSpecification: site.openingHoursSpecification.map((spec) => ({
      '@type': 'OpeningHoursSpecification',
      dayOfWeek: spec.days,
      opens: spec.opens,
      closes: spec.closes,
    })),
    sameAs: [site.messengers.vk],
  };
}

export function breadcrumbJsonLd(items: { name: string; path: string }[]) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: new URL(item.path, site.url).toString(),
    })),
  };
}

export function faqJsonLd(items: { q: string; a: string }[]) {
  return {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: items.map((item) => ({
      '@type': 'Question',
      name: item.q,
      acceptedAnswer: {
        '@type': 'Answer',
        text: item.a,
      },
    })),
  };
}

export function serviceJsonLd(service: { title: string; summary: string; slug: string }) {
  return {
    '@context': 'https://schema.org',
    '@type': 'Service',
    name: service.title,
    description: service.summary,
    url: new URL(`/uslugi/${service.slug}/`, site.url).toString(),
    provider: {
      '@type': 'HomeAndConstructionBusiness',
      name: site.companyName,
      telephone: site.phone.href.replace('tel:', ''),
      address: {
        '@type': 'PostalAddress',
        streetAddress: `${site.address.street}, ${site.address.house}`,
        addressLocality: site.address.city,
        addressRegion: site.address.region,
        addressCountry: 'RU',
      },
    },
    areaServed: site.areaServed.map((city) => ({ '@type': 'City', name: city })),
  };
}
