import '@puckeditor/core/no-external.css';
import './main.scss';

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

    createRoot(mountEl).render(
        <Puck
            config={config}
            data={initialData}
            onPublish={publish}
            dictionary={csDictionary}
            headerTitle={title}
            headerPath={path}
            overrides={{
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
