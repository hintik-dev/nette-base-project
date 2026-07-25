import type { ComponentConfig } from '@puckeditor/core';

export type HeroProps = {
    heading: string;
    subtext: string;
    variant: 'neutral' | 'primary' | 'base-200';
    primaryButtonText: string;
    primaryButtonUrl: string;
    secondaryButtonText: string;
    secondaryButtonUrl: string;
};

// Literální třídy (ne skládané za běhu) — Tailwind hledá přesné řetězce
// ve zdrojových souborech, dynamicky sestavený název by nenašel.
const VARIANT_CLASSES: Record<HeroProps['variant'], string> = {
    neutral: 'bg-neutral text-neutral-content',
    primary: 'bg-primary text-primary-content',
    'base-200': 'bg-base-200',
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
        primaryButtonText: { type: 'text', label: 'Text hlavního tlačítka' },
        primaryButtonUrl: { type: 'text', label: 'Odkaz hlavního tlačítka' },
        secondaryButtonText: { type: 'text', label: 'Text vedlejšího tlačítka (nepovinné)' },
        secondaryButtonUrl: { type: 'text', label: 'Odkaz vedlejšího tlačítka' },
    },
    defaultProps: {
        heading: 'Nadpis hero sekce',
        subtext: 'Krátký popisný text pod nadpisem.',
        variant: 'neutral',
        primaryButtonText: 'Hlavní akce',
        primaryButtonUrl: '#',
        secondaryButtonText: '',
        secondaryButtonUrl: '',
    },
    render: ({ heading, subtext, variant, primaryButtonText, primaryButtonUrl, secondaryButtonText, secondaryButtonUrl }) => (
        <div className={`hero min-h-[60vh] ${VARIANT_CLASSES[variant]}`}>
            <div className="hero-content text-center">
                <div className="max-w-md">
                    <div className="flex flex-wrap gap-2 justify-center mb-6 font-mono text-[11px] uppercase tracking-widest">
                        {BLOCK_CHIPS.map((chip) => (
                            <span key={chip} className={`rounded-full border px-3 py-1 ${CHIP_CLASSES[variant]}`}>
                                {chip}
                            </span>
                        ))}
                    </div>
                    <h1 className="text-5xl font-bold">{heading}</h1>
                    {subtext && <p className="py-6 opacity-80">{subtext}</p>}
                    <div className="flex gap-2 justify-center">
                        {primaryButtonText && (
                            <a href={primaryButtonUrl || '#'} className="btn btn-primary">
                                {primaryButtonText}
                            </a>
                        )}
                        {secondaryButtonText && (
                            <a href={secondaryButtonUrl || '#'} className="btn btn-outline">
                                {secondaryButtonText}
                            </a>
                        )}
                    </div>
                </div>
            </div>
        </div>
    ),
};
