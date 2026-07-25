import '@puckeditor/core/no-external.css';
import './main.scss';

import { useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { Puck, Button, type Data } from '@puckeditor/core';
import { config } from './config';
import { csDictionary } from './dictionary.cs';

declare global {
    interface Window {
        toastr?: {
            success: (msg: string) => void;
            error: (msg: string) => void;
        };
    }
}

const mountEl = document.getElementById('puck-root');

if (mountEl) {
    const saveUrl = mountEl.dataset.saveUrl ?? '';
    const backUrl = mountEl.dataset.backUrl ?? '';
    const title = mountEl.dataset.title ?? '';
    const path = mountEl.dataset.path ?? '';
    const webCssUrl = mountEl.dataset.webCssUrl ?? '';
    const initialData: Data = JSON.parse(mountEl.dataset.initialContent ?? '{"content":[],"root":{}}');

    const publish = async (data: Data): Promise<void> => {
        try {
            const response = await fetch(saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            window.toastr?.success('Stránka byla publikována.');
        } catch {
            window.toastr?.error('Uložení se nezdařilo. Zkuste to prosím znovu.');
        }
    };

    // Puck renderuje canvas ve vlastním izolovaném iframe. Bez tohohle by
    // bloky při editaci vypadaly úplně jinak než po publikaci (žádný
    // Tailwind/daisyUI styl). syncHostStyles je proto vypnuté (jinak by se
    // do canvasu zrcadlilo admin/AdminLTE téma, ne styl veřejného webu) a
    // reálné CSS webu se vstřikuje ručně přes override.
    const IframeStyles = ({ children, document }: { children: React.ReactNode; document?: Document }) => {
        useEffect(() => {
            if (!document || !webCssUrl) return;

            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = webCssUrl;
            document.head.appendChild(link);

            return () => link.remove();
        }, [document]);

        return <>{children}</>;
    };

    createRoot(mountEl).render(
        <Puck
            config={config}
            data={initialData}
            onPublish={publish}
            dictionary={csDictionary}
            headerTitle={title}
            headerPath={path}
            iframe={{ syncHostStyles: false }}
            overrides={{
                iframe: IframeStyles,
                headerActions: ({ children }) => (
                    <>
                        <Button href={backUrl} variant="secondary">
                            Zpět do administrace
                        </Button>
                        {children}
                    </>
                ),
            }}
        />,
    );
}
