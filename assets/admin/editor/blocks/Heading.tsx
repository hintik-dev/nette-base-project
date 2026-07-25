import { createElement } from 'react';
import type { ComponentConfig } from '@puckeditor/core';
import { alignField, type Align } from '../alignField';

export type HeadingProps = {
    text: string;
    level: '1' | '2' | '3' | '4' | '5' | '6';
    align: Align;
    color: 'default' | 'primary' | 'secondary' | 'accent';
};

// Preflight (Tailwind) resetuje h1-h6 na zcela neokázalý styl (dědí
// velikost/váhu z okolí) — bez explicitních tříd by nadpis mimo .prose
// nebyl vůbec vidět jako nadpis. Literální tabulka podle úrovně, ne
// skládaný název třídy (viz konvence u ostatních bloků).
const LEVEL_CLASSES: Record<HeadingProps['level'], string> = {
    '1': 'text-4xl md:text-5xl font-bold mb-6',
    '2': 'text-3xl md:text-4xl font-bold mb-5',
    '3': 'text-2xl md:text-3xl font-bold mb-4',
    '4': 'text-xl md:text-2xl font-bold mb-3',
    '5': 'text-lg font-bold mb-2',
    '6': 'text-base font-bold mb-2',
};

// "default" nechává barvu dědit z textu stránky, ostatní volby sáhnou
// po akcentu z palety — stejná volba jako accent v Hero.
const COLOR_CLASSES: Record<HeadingProps['color'], string> = {
    default: '',
    primary: 'text-primary',
    secondary: 'text-secondary',
    accent: 'text-accent',
};

export const Heading: ComponentConfig<HeadingProps> = {
    label: 'Nadpis',
    fields: {
        text: { type: 'text', label: 'Text' },
        level: {
            type: 'select',
            label: 'Úroveň',
            options: [
                { label: 'H1', value: '1' },
                { label: 'H2', value: '2' },
                { label: 'H3', value: '3' },
                { label: 'H4', value: '4' },
                { label: 'H5', value: '5' },
                { label: 'H6', value: '6' },
            ],
        },
        align: alignField,
        color: {
            type: 'select',
            label: 'Barva',
            options: [
                { label: 'Výchozí', value: 'default' },
                { label: 'Primární', value: 'primary' },
                { label: 'Sekundární', value: 'secondary' },
                { label: 'Akcentní', value: 'accent' },
            ],
        },
    },
    defaultProps: {
        text: 'Nadpis',
        level: '2',
        align: 'left',
        color: 'default',
    },
    render: ({ text, level, align, color }) => (
        <div className="container mx-auto px-4 py-6">
            {createElement(
                `h${level}`,
                { className: `${LEVEL_CLASSES[level]} ${COLOR_CLASSES[color]}`, style: { textAlign: align } },
                text,
            )}
        </div>
    ),
};
