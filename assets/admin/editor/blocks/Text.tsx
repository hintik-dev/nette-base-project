import type { ComponentConfig } from '@puckeditor/core';

export type TextProps = {
    text: string;
};

export const Text: ComponentConfig<TextProps> = {
    label: 'Odstavec',
    fields: {
        text: { type: 'textarea', label: 'Text' },
    },
    defaultProps: {
        text: 'Text odstavce',
    },
    render: ({ text }) => <p>{text}</p>,
};
