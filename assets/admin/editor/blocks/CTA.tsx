import type { ComponentConfig } from '@puckeditor/core';

export type CTAProps = {
    heading: string;
    subtext: string;
    variant: 'primary' | 'muted';
    buttonText: string;
    buttonUrl: string;
};

// Literální třídy — viz konvence u ostatních bloků. Na "primary" variantě
// (plná barva pozadí) musí tlačítko kontrastovat — stejný princip jako
// PRIMARY_BUTTON_CLASSES v Hero.
const SECTION_CLASSES: Record<CTAProps['variant'], string> = {
    primary: 'bg-primary text-primary-content',
    muted: 'bg-base-200',
};

const BUTTON_CLASSES: Record<CTAProps['variant'], string> = {
    primary: 'bg-base-100 text-primary border-none hover:bg-base-100/90',
    muted: 'btn-primary',
};

export const CTA: ComponentConfig<CTAProps> = {
    label: 'Výzva k akci',
    fields: {
        heading: { type: 'text', label: 'Nadpis' },
        subtext: { type: 'textarea', label: 'Podtext (nepovinné)' },
        variant: {
            type: 'select',
            label: 'Pozadí',
            options: [
                { label: 'Primární', value: 'primary' },
                { label: 'Tlumené', value: 'muted' },
            ],
        },
        buttonText: { type: 'text', label: 'Text tlačítka' },
        buttonUrl: { type: 'text', label: 'Odkaz tlačítka' },
    },
    defaultProps: {
        heading: 'Vyzkoušejte to na vlastním webu',
        subtext: '',
        variant: 'primary',
        buttonText: 'Začít',
        buttonUrl: '#',
    },
    render: ({ heading, subtext, variant, buttonText, buttonUrl }) => (
        <div className={SECTION_CLASSES[variant]}>
            <div className="container mx-auto px-4 py-16 text-center">
                <h2 className="text-3xl font-bold mb-4">{heading}</h2>
                {subtext && <p className="opacity-80 mb-8 max-w-xl mx-auto">{subtext}</p>}
                {buttonText && (
                    <a href={buttonUrl || '#'} className={`btn btn-lg shadow-lg ${BUTTON_CLASSES[variant]}`}>
                        {buttonText}
                    </a>
                )}
            </div>
        </div>
    ),
};
