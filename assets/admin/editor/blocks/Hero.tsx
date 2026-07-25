import type { ComponentConfig } from '@puckeditor/core';

export type HeroProps = {
    heading: string;
    subtext: string;
    variant: 'neutral' | 'primary' | 'base-200';
    backgroundImageUrl: string;
    primaryButtonText: string;
    primaryButtonUrl: string;
    secondaryButtonText: string;
    secondaryButtonUrl: string;
};

// Literální třídy (ne skládané za běhu) — Tailwind hledá přesné řetězce
// ve zdrojových souborech, dynamicky sestavený název by nenašel.
// Bez fotky na pozadí: jemný přechod barvou místo plochého jednobarevného
// pozadí, ať sekce nepůsobí jako plochá "dlaždice".
const VARIANT_CLASSES: Record<HeroProps['variant'], string> = {
    neutral: 'bg-gradient-to-br from-neutral via-neutral to-secondary text-neutral-content',
    primary: 'bg-gradient-to-br from-primary via-primary to-secondary text-primary-content',
    'base-200': 'bg-gradient-to-br from-base-200 to-base-100 text-base-content',
};

// S fotkou na pozadí: poloprůhledný barevný přechod přes fotku, aby text
// zůstal čitelný a sekce zároveň nesla skutečnou fotografii (ne plochu barvy).
const OVERLAY_CLASSES: Record<HeroProps['variant'], string> = {
    neutral: 'bg-gradient-to-br from-neutral/90 via-neutral/60 to-secondary/50 text-neutral-content',
    primary: 'bg-gradient-to-br from-primary/85 via-primary/55 to-secondary/45 text-primary-content',
    'base-200': 'bg-gradient-to-br from-base-100/90 via-base-100/70 to-base-200/50 text-base-content',
};

// Hlavní CTA tlačítko musí vůči pozadí kontrastovat — na primary pozadí
// by btn-primary splynulo se sekcí, proto je pro tuhle variantu tlačítko
// obrácené (světlé s barevným textem) místo plné primary barvy.
const PRIMARY_BUTTON_CLASSES: Record<HeroProps['variant'], string> = {
    neutral: 'btn-primary',
    primary: 'bg-base-100 text-primary border-none hover:bg-base-100/90',
    'base-200': 'btn-primary',
};

const CHIP_CLASSES: Record<HeroProps['variant'], string> = {
    neutral: 'border-neutral-content/30 text-neutral-content/70',
    primary: 'border-primary-content/30 text-primary-content/70',
    'base-200': 'border-base-content/20 text-base-content/60',
};

// Signaturní prvek: jmenovky skutečných typů bloků tohoto redaktoru —
// hero sekce sama sebe prezentuje jako "poskládanou z bloků", proto
// stavební kameny (block chips) ukazujeme přímo doslova.
const BLOCK_CHIPS = ['Nadpis', 'Text', 'Obrázek', 'Sloupce', 'Kroky'];

export const Hero: ComponentConfig<HeroProps> = {
    label: 'Hero sekce',
    fields: {
        heading: { type: 'text', label: 'Nadpis' },
        subtext: { type: 'textarea', label: 'Podtext' },
        variant: {
            type: 'select',
            label: 'Barevný styl',
            options: [
                { label: 'Neutrální (tmavá)', value: 'neutral' },
                { label: 'Primární', value: 'primary' },
                { label: 'Světlá', value: 'base-200' },
            ],
        },
        backgroundImageUrl: { type: 'text', label: 'URL fotky na pozadí (nepovinné)' },
        primaryButtonText: { type: 'text', label: 'Text hlavního tlačítka' },
        primaryButtonUrl: { type: 'text', label: 'Odkaz hlavního tlačítka' },
        secondaryButtonText: { type: 'text', label: 'Text vedlejšího tlačítka (nepovinné)' },
        secondaryButtonUrl: { type: 'text', label: 'Odkaz vedlejšího tlačítka' },
    },
    defaultProps: {
        heading: 'Nadpis hero sekce',
        subtext: 'Krátký popisný text pod nadpisem.',
        variant: 'primary',
        backgroundImageUrl: '',
        primaryButtonText: 'Hlavní akce',
        primaryButtonUrl: '#',
        secondaryButtonText: '',
        secondaryButtonUrl: '',
    },
    render: ({
        heading,
        subtext,
        variant,
        backgroundImageUrl,
        primaryButtonText,
        primaryButtonUrl,
        secondaryButtonText,
        secondaryButtonUrl,
    }) => (
        <div
            className={`hero min-h-[70vh] relative isolate overflow-hidden ${backgroundImageUrl ? OVERLAY_CLASSES[variant] : VARIANT_CLASSES[variant]}`}
            style={backgroundImageUrl ? { backgroundImage: `url(${backgroundImageUrl})`, backgroundSize: 'cover', backgroundPosition: 'center' } : undefined}
        >
            <div className="hero-content text-center py-24">
                <div className="max-w-2xl">
                    <div className="flex flex-wrap gap-2 justify-center mb-6 font-mono text-[11px] uppercase tracking-widest">
                        {BLOCK_CHIPS.map((chip) => (
                            <span key={chip} className={`rounded-full border px-3 py-1 ${CHIP_CLASSES[variant]}`}>
                                {chip}
                            </span>
                        ))}
                    </div>
                    <h1 className="text-5xl md:text-6xl font-bold">{heading}</h1>
                    {subtext && <p className="py-6 text-lg opacity-90">{subtext}</p>}
                    <div className="flex gap-3 justify-center">
                        {primaryButtonText && (
                            <a href={primaryButtonUrl || '#'} className={`btn btn-lg shadow-lg ${PRIMARY_BUTTON_CLASSES[variant]}`}>
                                {primaryButtonText}
                            </a>
                        )}
                        {secondaryButtonText && (
                            <a href={secondaryButtonUrl || '#'} className="btn btn-lg btn-outline">
                                {secondaryButtonText}
                            </a>
                        )}
                    </div>
                </div>
            </div>
        </div>
    ),
};
