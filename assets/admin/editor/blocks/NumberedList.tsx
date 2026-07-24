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
