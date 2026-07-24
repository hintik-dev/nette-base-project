import type { CustomField } from '@puckeditor/core';

export type Align = 'left' | 'center' | 'right';

const ALIGN_OPTIONS: { value: Align; label: string; lines: [number, number, number] }[] = [
    { value: 'left', label: 'Doleva', lines: [100, 70, 85] },
    { value: 'center', label: 'Na střed', lines: [100, 70, 85] },
    { value: 'right', label: 'Doprava', lines: [100, 70, 85] },
];

function AlignIcon({ align, lineWidths }: { align: Align; lineWidths: [number, number, number] }) {
    const justify = align === 'left' ? 'flex-start' : align === 'center' ? 'center' : 'flex-end';
    return (
        <span style={{ display: 'flex', flexDirection: 'column', gap: 2, width: 16 }}>
            {lineWidths.map((w, i) => (
                <span
                    key={i}
                    style={{
                        display: 'flex',
                        justifyContent: justify,
                    }}
                >
                    <span style={{ display: 'block', width: `${w}%`, height: 2, background: 'currentColor', borderRadius: 1 }} />
                </span>
            ))}
        </span>
    );
}

/**
 * Segmentovaný přepínač zarovnání s ikonami místo generických radio
 * přepínačů (Puck field typu "radio" nemá ikony na úrovni jednotlivých
 * voleb, jen obecné kolečko).
 */
export const alignField: CustomField<Align> = {
    type: 'custom',
    label: 'Zarovnání',
    render: ({ value, onChange }) => (
        <div style={{ display: 'flex', gap: 4 }}>
            {ALIGN_OPTIONS.map((option) => (
                <button
                    key={option.value}
                    type="button"
                    onClick={() => onChange(option.value)}
                    title={option.label}
                    aria-label={option.label}
                    aria-pressed={value === option.value}
                    style={{
                        flex: 1,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        padding: '6px 0',
                        cursor: 'pointer',
                        border: '1px solid var(--puck-color-border)',
                        borderRadius: 'var(--puck-radius-s)',
                        background: value === option.value ? 'var(--puck-color-interactive-subtle)' : 'var(--puck-color-surface)',
                        color: value === option.value ? 'var(--puck-color-interactive)' : 'var(--puck-color-text)',
                    }}
                >
                    <AlignIcon align={option.value} lineWidths={option.lines} />
                </button>
            ))}
        </div>
    ),
};
