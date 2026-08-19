/**
 * Datagrid (ublaboo) a jeho pluginy — nejtěžší závislost admin bundlu
 * (Sortable, TomSelect, VanillaDatepicker, ...). Načítá se přes dynamický
 * import() jen na stránkách, kde je v DOM alespoň jeden [data-datagrid-name]
 * (viz main.js), aby nezatěžoval každou admin stránku.
 *
 * Kopíruje přesně nastavení z vendor/ublaboo/datagrid/assets/datagrid-full.ts,
 * jen bez naja.initialize() — ta proběhla už dřív v naja-init.js (podruhé by
 * Naja vyhodila výjimku "already initialized").
 */
import naja from 'naja';
import netteForms from 'nette-forms';
import {
    AutosubmitPlugin,
    CheckboxPlugin,
    ConfirmPlugin,
    createDatagrids,
    DatepickerPlugin,
    EditablePlugin,
    InlinePlugin,
    ItemDetailPlugin,
    NetteFormsPlugin,
    SelectpickerPlugin,
    SortableJS,
    SortablePlugin,
    TomSelect,
    TreeViewPlugin,
    VanillaDatepicker,
} from '../../../vendor/ublaboo/datagrid/assets';
import { NajaAjax } from '../../../vendor/ublaboo/datagrid/assets/ajax';
import Select from 'tom-select';
import { Dropdown } from 'bootstrap';

import './datagrid-spinner.js';
import './datagrid-flatpickr.js';

Array.from(document.querySelectorAll('.dropdown')).forEach((el) => new Dropdown(el));

createDatagrids(new NajaAjax(naja), {
    datagrid: {
        plugins: [
            new AutosubmitPlugin(),
            new CheckboxPlugin(),
            new ConfirmPlugin(),
            new EditablePlugin(),
            new InlinePlugin(),
            new ItemDetailPlugin(),
            new NetteFormsPlugin(netteForms),
            new SortablePlugin(new SortableJS()),
            new DatepickerPlugin(new VanillaDatepicker({ buttonClass: 'btn' })),
            new SelectpickerPlugin(new TomSelect(Select, {
                plugins: ['clear_button'],
                maxOptions: null,
            })),
            new TreeViewPlugin(),
        ],
    },
});
