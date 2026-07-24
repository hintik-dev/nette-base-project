import '@puckeditor/core/no-external.css';
import './main.scss';

import { createRoot } from 'react-dom/client';
import { Puck, type Data } from '@puckeditor/core';
import { config } from './config';
import { csDictionary } from './dictionary.cs';

const mountEl = document.getElementById('puck-root');

if (mountEl) {
    const saveUrl = mountEl.dataset.saveUrl ?? '';
    const backUrl = mountEl.dataset.backUrl ?? '';
    const title = mountEl.dataset.title ?? '';
    const path = mountEl.dataset.path ?? '';
    const initialData: Data = JSON.parse(mountEl.dataset.initialContent ?? '{"content":[],"root":{}}');

    const publish = async (data: Data): Promise<void> => {
        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            window.alert('Uložení se nezdařilo.');
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
                        <a href={backUrl} className="btn btn-sm btn-secondary">
                            Zpět do administrace
                        </a>
                        {children}
                    </>
                ),
            }}
        />,
    );
}
