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
    // Bez vlastního py — vnořené bloky ve slotech (Heading/Text) si svislé
    // odsazení nesou samy, přidání dalšího by ho zdvojilo.
    render: ({ left: Left, right: Right }) => (
        <div className="container mx-auto px-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <Left />
            <Right />
        </div>
    ),
};
