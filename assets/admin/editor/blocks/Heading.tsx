import type { ComponentConfig } from '@puckeditor/core';

export type HeadingProps = {
    text: string;
};

export const Heading: ComponentConfig<HeadingProps> = {
    label: 'Nadpis',
    fields: {
        text: { type: 'text', label: 'Text' },
    },
    defaultProps: {
        text: 'Nadpis',
    },
    render: ({ text }) => <h2>{text}</h2>,
};
