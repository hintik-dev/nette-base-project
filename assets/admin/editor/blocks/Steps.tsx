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
    render: ({ heading, items }) => (
        <div className="bg-base-200 py-16">
            <div className="container mx-auto px-4">
                {heading && (
                    <div className="max-w-2xl mx-auto text-center mb-10">
                        <h2 className="text-3xl font-bold mb-4">{heading}</h2>
                    </div>
                )}
                <ul className="steps steps-vertical lg:steps-horizontal w-full">
                    {items.map((item, i) => (
                        <li key={i} className="step step-primary">
                            {item.text}
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    ),
};
