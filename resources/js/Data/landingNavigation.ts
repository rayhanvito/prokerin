interface LandingNavigationItem {
    label: string;
    href: string;
}

export const landingNavigationItems: LandingNavigationItem[] = [
    { label: 'Fitur', href: route('landing.features') },
    { label: 'Harga', href: route('landing.pricing') },
];
