import type { ComponentConfig } from '@puckeditor/core';

export type NumberedListProps = {
    items: { text: string }[];
};

export const NumberedList: ComponentConfig<NumberedListProps> = {
    label: 'Číslovaný seznam',
    fields: {
        items: {
            type: 'array',
            label: 'Položky',
            arrayFields: {
                text: { type: 'text', label: 'Text' },
            },
            getItemSummary: (item, index) => item.text || `Položka č. ${(index ?? 0) + 1}`,
        },
    },
    defaultProps: {
        items: [{ text: 'Položka 1' }],
    },
    render: ({ items }) => (
        <ol>
            {items.map((item, i) => (
                <li key={i}>{item.text}</li>
            ))}
        </ol>
    ),
};
