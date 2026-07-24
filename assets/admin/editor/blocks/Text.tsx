import type { ComponentConfig } from '@puckeditor/core';

export type TextProps = {
    text: string;
};

export const Text: ComponentConfig<TextProps> = {
    label: 'Odstavec',
    fields: {
        // Vestavěný toolbar: tučné, kurzíva, podtržení, přeškrtnutí, odkaz,
        // citace, kód, seznamy, zarovnání — bez vlastní konfigurace extension.
        text: { type: 'richtext', label: 'Text' },
    },
    defaultProps: {
        text: '<p>Text odstavce</p>',
    },
    render: ({ text }) => (
        <div dangerouslySetInnerHTML={{ __html: typeof text === 'string' ? text : '' }} />
    ),
};
