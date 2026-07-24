export type FontSize = 'small' | 'normal' | 'large' | 'xlarge';

export const FONT_SIZE_PX: Record<FontSize, string> = {
    small: '0.875rem',
    normal: '1rem',
    large: '1.25rem',
    xlarge: '1.5rem',
};

export const fontSizeField = {
    type: 'select' as const,
    label: 'Velikost písma',
    options: [
        { label: 'Malé', value: 'small' },
        { label: 'Normální', value: 'normal' },
        { label: 'Velké', value: 'large' },
        { label: 'Extra velké', value: 'xlarge' },
    ],
};
