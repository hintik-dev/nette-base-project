import type { ComponentConfig } from '@puckeditor/core';

export type StepsProps = {
    heading: string;
    items: { text: string }[];
};

export const Steps: ComponentConfig<StepsProps> = {
    label: 'Kroky',
    fields: {
        heading: { type: 'text', label: 'Nadpis sekce (nepovinné)' },
        items: {
            type: 'array',
            label: 'Kroky',
            arrayFields: {
                text: { type: 'text', label: 'Text kroku' },
            },
            getItemSummary: (item, index) => item.text || `Krok č. ${(index ?? 0) + 1}`,
        },
    },
    defaultProps: {
        heading: 'Jak to funguje',
        items: [
            { text: 'První krok' },
            { text: 'Druhý krok' },
            { text: 'Třetí krok' },
        ],
    },
    // Skleněné dlaždice s číslem — stejný vizuální jazyk jako bento panel
    // v Hero a karty ve FeatureGrid (glass efekt, jemný stín, hover lift),
    // místo vestavěné daisyUI komponenty "steps", která z toho vybočovala.
    render: ({ heading, items }) => (
        <div className="relative isolate overflow-hidden bg-gradient-to-b from-base-100 to-base-200/60 py-20">
            <div className="pointer-events-none absolute bottom-0 left-1/2 -translate-x-1/2 w-[36rem] h-[20rem] rounded-full blur-3xl bg-gradient-to-br from-accent/15 via-primary/10 to-transparent" />
            <div className="relative container mx-auto px-4">
                <div className="max-w-2xl mx-auto text-center mb-12">
                    <span className="inline-block font-mono text-xs uppercase tracking-widest text-base-content/50 border border-base-300 rounded-full px-3 py-1 mb-4">
                        Postup
                    </span>
                    {heading && <h2 className="text-3xl font-bold">{heading}</h2>}
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {items.map((item, i) => (
                        <div
                            key={i}
                            className="rounded-2xl border border-base-300/60 bg-base-100/70 backdrop-blur shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all p-6"
                        >
                            <div className="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center font-mono font-bold mb-4">
                                {i + 1}
                            </div>
                            <p className="font-medium">{item.text}</p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    ),
};
