import type { ComponentConfig, RichText } from '@puckeditor/core';
import { FONT_SIZE_PX, fontSizeField, type FontSize } from '../fontSize';

export type TextProps = {
    text: RichText;
    size: FontSize;
};

export const Text: ComponentConfig<TextProps> = {
    label: 'Odstavec',
    fields: {
        // Vestavěný toolbar: tučné, kurzíva, podtržení, přeškrtnutí, odkaz,
        // citace, kód, seznamy, zarovnání — bez vlastní konfigurace extension.
        text: { type: 'richtext', label: 'Text' },
        size: fontSizeField,
    },
    defaultProps: {
        text: '<p>Text odstavce</p>',
        size: 'normal',
    },
    // Puck předává hodnotu richtext pole do render() už jako vyrenderovaný
    // ReactNode (ne holý HTML string) — vlastní dangerouslySetInnerHTML
    // způsobovalo pád komponenty hned po vložení (viz .scratch-repro.mjs).
    render: ({ text, size }) => (
        <div className="container mx-auto px-4 py-6">
            <div className="prose lg:prose-lg max-w-none" style={{ fontSize: FONT_SIZE_PX[size] }}>
                {text}
            </div>
        </div>
    ),
};
