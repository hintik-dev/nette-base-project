import type { ComponentConfig } from '@puckeditor/core';

export type HeadingProps = {
    text: string;
    align: 'left' | 'center' | 'right';
};

export const Heading: ComponentConfig<HeadingProps> = {
    label: 'Nadpis',
    fields: {
        text: { type: 'text', label: 'Text' },
        align: {
            type: 'radio',
            label: 'Zarovnání',
            options: [
                { label: 'Doleva', value: 'left' },
                { label: 'Na střed', value: 'center' },
                { label: 'Doprava', value: 'right' },
            ],
        },
    },
    defaultProps: {
        text: 'Nadpis',
        align: 'left',
    },
    render: ({ text, align }) => <h2 style={{ textAlign: align }}>{text}</h2>,
};
