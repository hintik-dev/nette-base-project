import { createElement } from 'react';
import type { ComponentConfig } from '@puckeditor/core';
import { alignField, type Align } from '../alignField';

export type HeadingProps = {
    text: string;
    level: '1' | '2' | '3' | '4' | '5' | '6';
    align: Align;
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
    },
    defaultProps: {
        text: 'Nadpis',
        level: '2',
        align: 'left',
    },
    render: ({ text, level, align }) =>
        createElement(`h${level}`, { style: { textAlign: align } }, text),
};
