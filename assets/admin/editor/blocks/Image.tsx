import type { ComponentConfig } from '@puckeditor/core';

export type ImageProps = {
    src: string;
    alt: string;
    width: '25' | '50' | '75' | '100';
};

export const Image: ComponentConfig<ImageProps> = {
    label: 'Obrázek',
    fields: {
        src: { type: 'text', label: 'URL obrázku' },
        alt: { type: 'text', label: 'Alternativní text' },
        width: {
            type: 'select',
            label: 'Šířka',
            options: [
                { label: '25 %', value: '25' },
                { label: '50 %', value: '50' },
                { label: '75 %', value: '75' },
                { label: '100 % (celá šířka)', value: '100' },
            ],
        },
    },
    defaultProps: {
        src: '',
        alt: '',
        width: '100',
    },
    render: ({ src, alt, width }) =>
        src ? (
            <img src={src} alt={alt} style={{ width: `${width}%`, maxWidth: '100%' }} />
        ) : (
            <p>(vyplňte URL obrázku)</p>
        ),
};
