import type { ComponentConfig } from '@puckeditor/core';

export type ImageProps = {
    src: string;
    alt: string;
};

export const Image: ComponentConfig<ImageProps> = {
    label: 'Obrázek',
    fields: {
        src: { type: 'text', label: 'URL obrázku' },
        alt: { type: 'text', label: 'Alternativní text' },
    },
    defaultProps: {
        src: '',
        alt: '',
    },
    render: ({ src, alt }) =>
        src ? <img src={src} alt={alt} style={{ maxWidth: '100%' }} /> : <p>(vyplňte URL obrázku)</p>,
};
