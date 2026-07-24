import type { ComponentConfig, RichText } from '@puckeditor/core';

export type TextProps = {
    text: RichText;
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
    // Puck předává hodnotu richtext pole do render() už jako vyrenderovaný
    // ReactNode (ne holý HTML string) — vlastní dangerouslySetInnerHTML
    // způsobovalo pád komponenty hned po vložení (viz .scratch-repro.mjs).
    render: ({ text }) => <div>{text}</div>,
};
