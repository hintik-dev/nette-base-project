import type { ComponentConfig } from '@puckeditor/core';

export type BulletListProps = {
    items: { text: string }[];
};

export const BulletList: ComponentConfig<BulletListProps> = {
    label: 'Nečíslovaný seznam',
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
        <ul>
            {items.map((item, i) => (
                <li key={i}>{item.text}</li>
            ))}
        </ul>
    ),
};
