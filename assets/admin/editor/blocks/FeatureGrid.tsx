import type { ComponentConfig } from '@puckeditor/core';

export type FeatureGridProps = {
    heading: string;
    intro: string;
    columns: '2' | '3' | '4';
    items: { title: string; text: string }[];
};

// Literální třídy — Tailwind ve zdrojácích hledá přesné řetězce, ne
// dynamicky skládaný `grid-cols-${columns}`.
const GRID_CLASSES: Record<FeatureGridProps['columns'], string> = {
    '2': 'grid-cols-1 sm:grid-cols-2',
    '3': 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    '4': 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
};

// Karty se střídají po třech barvách palety, ať mřížka nepůsobí jako
// jednolitá plocha stejných dlaždic — každá "vlastnost" dostane jiný
// horní akcentní pruh (amber / slate / moss).
const ACCENT_BORDER_CLASSES = ['border-t-primary', 'border-t-secondary', 'border-t-accent'];

export const FeatureGrid: ComponentConfig<FeatureGridProps> = {
    label: 'Mřížka vlastností',
    fields: {
        heading: { type: 'text', label: 'Nadpis sekce' },
        intro: { type: 'textarea', label: 'Úvodní text (nepovinné)' },
        columns: {
            type: 'select',
            label: 'Počet sloupců',
            options: [
                { label: '2', value: '2' },
                { label: '3', value: '3' },
                { label: '4', value: '4' },
            ],
        },
        items: {
            type: 'array',
            label: 'Položky',
            arrayFields: {
                title: { type: 'text', label: 'Titulek' },
                text: { type: 'textarea', label: 'Text' },
            },
            getItemSummary: (item, index) => item.title || `Položka č. ${(index ?? 0) + 1}`,
        },
    },
    defaultProps: {
        heading: 'Co to umí',
        intro: '',
        columns: '4',
        items: [
            { title: 'Vlastnost 1', text: 'Krátký popis.' },
            { title: 'Vlastnost 2', text: 'Krátký popis.' },
        ],
    },
    render: ({ heading, intro, columns, items }) => (
        <div className="container mx-auto px-4 py-16">
            <div className="max-w-2xl mx-auto text-center mb-12">
                <h2 className="text-3xl font-bold mb-4">{heading}</h2>
                {intro && <p className="text-base-content/70">{intro}</p>}
            </div>
            <div className={`grid ${GRID_CLASSES[columns]} gap-6`}>
                {items.map((item, i) => (
                    <div key={i} className={`card bg-base-100 shadow-sm border-t-4 ${ACCENT_BORDER_CLASSES[i % 3]}`}>
                        <div className="card-body items-center text-center">
                            <h3 className="card-title text-base">{item.title}</h3>
                            <p className="text-sm text-base-content/70">{item.text}</p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    ),
};
