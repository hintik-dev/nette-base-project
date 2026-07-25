import type { ComponentConfig } from '@puckeditor/core';

export type HeroProps = {
    heading: string;
    subtext: string;
    accent: 'primary' | 'secondary' | 'accent';
    primaryButtonText: string;
    primaryButtonUrl: string;
    secondaryButtonText: string;
    secondaryButtonUrl: string;
};

// Literální třídy (ne skládané za běhu) — Tailwind hledá přesné řetězce
// ve zdrojových souborech, dynamicky sestavený název by nenašel.
// Sekce je vždy světlá (uživatel chce jednoznačně light mode) — "accent"
// jen barví aurora přechod na pozadí a velkou dlaždici v bentu.
const AURORA_CLASSES: Record<HeroProps['accent'], string> = {
    primary: 'from-primary/25 via-secondary/10 to-transparent',
    secondary: 'from-secondary/25 via-primary/10 to-transparent',
    accent: 'from-accent/25 via-primary/10 to-transparent',
};

const BIG_TILE_CLASSES: Record<HeroProps['accent'], string> = {
    primary: 'bg-primary/10 text-primary',
    secondary: 'bg-secondary/10 text-secondary',
    accent: 'bg-accent/10 text-accent',
};

// Signaturní prvek: bento mřížka skutečných typů bloků tohoto redaktoru —
// 2026 trend (bento grid) tady navíc doslova zobrazuje, co produkt dělá:
// stránka vzniká skládáním dlaždic/bloků různé velikosti, přesně jako tenhle panel.
const BLOCK_TILES = [
    { icon: '🎯', label: 'Hero', hint: 'Poutavý úvod s CTA' },
    { icon: '📝', label: 'Text' },
    { icon: '🖼️', label: 'Obrázek' },
    { icon: '🧱', label: 'Sloupce' },
    { icon: '🔢', label: 'Kroky' },
];

export const Hero: ComponentConfig<HeroProps> = {
    label: 'Hero sekce',
    fields: {
        heading: { type: 'text', label: 'Nadpis' },
        subtext: { type: 'textarea', label: 'Podtext' },
        accent: {
            type: 'select',
            label: 'Akcentní barva',
            options: [
                { label: 'Amber (primary)', value: 'primary' },
                { label: 'Slate (secondary)', value: 'secondary' },
                { label: 'Moss (accent)', value: 'accent' },
            ],
        },
        primaryButtonText: { type: 'text', label: 'Text hlavního tlačítka' },
        primaryButtonUrl: { type: 'text', label: 'Odkaz hlavního tlačítka' },
        secondaryButtonText: { type: 'text', label: 'Text vedlejšího odkazu (nepovinné)' },
        secondaryButtonUrl: { type: 'text', label: 'Odkaz vedlejšího odkazu' },
    },
    defaultProps: {
        heading: 'Nadpis hero sekce',
        subtext: 'Krátký popisný text pod nadpisem.',
        accent: 'primary',
        primaryButtonText: 'Hlavní akce',
        primaryButtonUrl: '#',
        secondaryButtonText: '',
        secondaryButtonUrl: '',
    },
    render: ({ heading, subtext, accent, primaryButtonText, primaryButtonUrl, secondaryButtonText, secondaryButtonUrl }) => (
        <div className="relative isolate overflow-hidden bg-gradient-to-b from-base-200/70 to-base-100">
            {/* Aurora — dvě rozostřené barevné skvrny na světlém pozadí místo plochy jedné barvy. */}
            <div className={`pointer-events-none absolute -top-24 -left-24 w-[32rem] h-[32rem] rounded-full blur-3xl bg-gradient-to-br ${AURORA_CLASSES[accent]}`} />
            <div className="pointer-events-none absolute top-1/3 -right-32 w-[28rem] h-[28rem] rounded-full blur-3xl bg-gradient-to-br from-secondary/15 via-accent/10 to-transparent" />

            <div className="relative container mx-auto px-4 py-20 lg:py-28 grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span className="inline-block font-mono text-xs uppercase tracking-widest text-base-content/50 border border-base-300 rounded-full px-3 py-1 mb-6">
                        Vizuální editor obsahu
                    </span>
                    <h1
                        className={`text-5xl md:text-6xl font-bold leading-[1.05] mb-6 bg-gradient-to-br bg-clip-text text-transparent ${accent === 'primary' ? 'from-primary via-primary to-secondary' : accent === 'secondary' ? 'from-secondary via-secondary to-primary' : 'from-accent via-accent to-secondary'}`}
                    >
                        {heading}
                    </h1>
                    {subtext && <p className="text-lg text-base-content/70 mb-8 max-w-lg">{subtext}</p>}
                    <div className="flex flex-wrap items-center gap-6">
                        {primaryButtonText && (
                            <a href={primaryButtonUrl || '#'} className="btn btn-lg btn-primary shadow-lg shadow-primary/20">
                                {primaryButtonText}
                            </a>
                        )}
                        {secondaryButtonText && (
                            <a href={secondaryButtonUrl || '#'} className="link link-hover font-semibold">
                                {secondaryButtonText} →
                            </a>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-4 grid-rows-2 gap-3 h-80">
                    {BLOCK_TILES.map((tile, i) => (
                        <div
                            key={tile.label}
                            className={`rounded-2xl border border-base-300/60 bg-base-100/80 backdrop-blur shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all p-4 flex flex-col ${
                                i === 0 ? 'col-span-2 row-span-2 justify-between' : i === 1 ? 'col-start-3 justify-center' : i === 2 ? 'col-start-4 justify-center' : i === 3 ? 'col-start-3 row-start-2 justify-center' : 'col-start-4 row-start-2 justify-center'
                            }`}
                        >
                            <div className={`w-10 h-10 rounded-full flex items-center justify-center text-xl ${i === 0 ? BIG_TILE_CLASSES[accent] : 'bg-base-200 text-base-content/70'}`}>
                                {tile.icon}
                            </div>
                            <div>
                                <p className="font-semibold text-sm mt-2">{tile.label}</p>
                                {tile.hint && <p className="text-xs text-base-content/50 mt-1">{tile.hint}</p>}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    ),
};
