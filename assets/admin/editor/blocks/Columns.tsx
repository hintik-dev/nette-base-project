import type { ComponentConfig, Slot } from '@puckeditor/core';

export type ColumnsProps = {
    left: Slot;
    right: Slot;
};

export const Columns: ComponentConfig<ColumnsProps> = {
    label: 'Dva sloupce',
    fields: {
        left: { type: 'slot', label: 'Levý sloupec' },
        right: { type: 'slot', label: 'Pravý sloupec' },
    },
    render: ({ left: Left, right: Right }) => (
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem' }}>
            <Left />
            <Right />
        </div>
    ),
};
