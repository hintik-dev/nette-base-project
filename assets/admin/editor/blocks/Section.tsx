import type { ComponentConfig, Slot } from '@puckeditor/core';

export type SectionProps = {
    eyebrow: string;
    heading: string;
    subtext: string;
    background: 'none' | 'muted';
    content: Slot;
};

// Obecná titulní sekce — obalí libovolné bloky (přes slot) společným
// nadpisem/podtextem ve stejném vizuálním jazyce jako Hero/FeatureGrid/Steps
// (eyebrow štítek, volitelné tlumené pozadí), aniž by редaktor musel ručně
// skládat Heading + Text pokaždé znovu.
const BACKGROUND_CLASSES: Record<SectionProps['background'], string> = {
    none: '',
    muted: 'bg-base-200/60',
};

export const Section: ComponentConfig<SectionProps> = {
    label: 'Sekce s nadpisem',
    fields: {
        eyebrow: { type: 'text', label: 'Eyebrow štítek (nepovinné)' },
        heading: { type: 'text', label: 'Nadpis sekce' },
        subtext: { type: 'textarea', label: 'Podtext (nepovinné)' },
        background: {
            type: 'select',
            label: 'Pozadí',
            options: [
                { label: 'Výchozí', value: 'none' },
                { label: 'Tlumené', value: 'muted' },
            ],
        },
        content: { type: 'slot', label: 'Obsah sekce' },
    },
    defaultProps: {
        eyebrow: '',
        heading: 'Nadpis sekce',
        subtext: '',
        background: 'none',
        content: [],
    },
    render: ({ eyebrow, heading, subtext, background, content: Content }) => (
        <div className={BACKGROUND_CLASSES[background]}>
            <div className="container mx-auto px-4 py-14">
                <div className="max-w-2xl mx-auto text-center mb-10">
                    {eyebrow && (
                        <span className="inline-block font-mono text-xs uppercase tracking-widest text-base-content/50 border border-base-300 rounded-full px-3 py-1 mb-4">
                            {eyebrow}
                        </span>
                    )}
                    <h2 className="text-3xl font-bold mb-4">{heading}</h2>
                    {subtext && <p className="text-base-content/70">{subtext}</p>}
                </div>
                <Content />
            </div>
        </div>
    ),
};
