import type { ComponentConfig, RichText } from '@puckeditor/core';
import { FONT_SIZE_PX, fontSizeField, type FontSize } from '../fontSize';

export type NumberedListProps = {
    items: { text: RichText }[];
    size: FontSize;
};

const stripHtml = (html: string): string => html.replace(/<[^>]*>/g, '');

export const NumberedList: ComponentConfig<NumberedListProps> = {
    label: 'Číslovaný seznam',
    fields: {
        items: {
            type: 'array',
            label: 'Položky',
            arrayFields: {
                text: { type: 'richtext', label: 'Text' },
            },
            getItemSummary: (item, index) =>
                (typeof item.text === 'string' ? stripHtml(item.text) : '') || `Položka č. ${(index ?? 0) + 1}`,
        },
        size: fontSizeField,
    },
    defaultProps: {
        items: [{ text: '<p>Položka 1</p>' }],
        size: 'normal',
    },
    render: ({ items, size }) => (
        <ol style={{ fontSize: FONT_SIZE_PX[size] }}>
            {items.map((item, i) => (
                <li key={i}>{item.text}</li>
            ))}
        </ol>
    ),
};
