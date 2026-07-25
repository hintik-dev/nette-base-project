import type { ComponentConfig, RichText } from '@puckeditor/core';

export type ImageTextProps = {
    imageSrc: string;
    imageAlt: string;
    imagePosition: 'left' | 'right';
    heading: string;
    text: RichText;
    buttonText: string;
    buttonUrl: string;
};

// Literální třídy (ne skládané za běhu) — Tailwind hledá přesné řetězce
// ve zdrojových souborech. order-* přehazuje obrázek/text bez nutnosti
// měnit pořadí markupu (důležité pro čtecí pořadí/přístupnost).
const IMAGE_ORDER_CLASSES: Record<ImageTextProps['imagePosition'], string> = {
    left: '',
    right: 'md:order-2',
};

export const ImageText: ComponentConfig<ImageTextProps> = {
    label: 'Obrázek a text',
    fields: {
        imageSrc: { type: 'text', label: 'URL obrázku' },
        imageAlt: { type: 'text', label: 'Alternativní text' },
        imagePosition: {
            type: 'select',
            label: 'Pozice obrázku',
            options: [
                { label: 'Vlevo', value: 'left' },
                { label: 'Vpravo', value: 'right' },
            ],
        },
        heading: { type: 'text', label: 'Nadpis' },
        text: { type: 'richtext', label: 'Text' },
        buttonText: { type: 'text', label: 'Text tlačítka (nepovinné)' },
        buttonUrl: { type: 'text', label: 'Odkaz tlačítka' },
    },
    defaultProps: {
        imageSrc: '',
        imageAlt: '',
        imagePosition: 'left',
        heading: 'Nadpis sekce',
        text: '<p>Popisný text vedle obrázku.</p>',
        buttonText: '',
        buttonUrl: '',
    },
    render: ({ imageSrc, imageAlt, imagePosition, heading, text, buttonText, buttonUrl }) => (
        <div className="container mx-auto px-4 py-10">
            <div className="grid md:grid-cols-2 gap-10 items-center">
                <div className={IMAGE_ORDER_CLASSES[imagePosition]}>
                    {imageSrc ? (
                        <img src={imageSrc} alt={imageAlt} className="rounded-2xl shadow-lg w-full h-auto" />
                    ) : (
                        <div className="rounded-2xl border border-dashed border-base-300 aspect-video flex items-center justify-center text-base-content/40 text-sm">
                            (vyplňte URL obrázku)
                        </div>
                    )}
                </div>
                <div>
                    <h2 className="text-3xl font-bold mb-4">{heading}</h2>
                    <div className="prose max-w-none text-base-content/70 mb-6">{text}</div>
                    {buttonText && (
                        <a href={buttonUrl || '#'} className="btn btn-primary">
                            {buttonText}
                        </a>
                    )}
                </div>
            </div>
        </div>
    ),
};
