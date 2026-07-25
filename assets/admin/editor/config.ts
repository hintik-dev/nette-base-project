import type { Config } from '@puckeditor/core';
import { Heading, type HeadingProps } from './blocks/Heading';
import { Text, type TextProps } from './blocks/Text';
import { NumberedList, type NumberedListProps } from './blocks/NumberedList';
import { BulletList, type BulletListProps } from './blocks/BulletList';
import { Image, type ImageProps } from './blocks/Image';
import { Columns, type ColumnsProps } from './blocks/Columns';
import { Hero, type HeroProps } from './blocks/Hero';
import { FeatureGrid, type FeatureGridProps } from './blocks/FeatureGrid';
import { Steps, type StepsProps } from './blocks/Steps';

/**
 * Registr bloků editoru — PHP protějšek je App\Domain\Page\BlockRenderer,
 * který pro každý klíč očekává Latte šablonu Web/Page/blocks/{klíč}.latte.
 */
type Props = {
    Heading: HeadingProps;
    Text: TextProps;
    NumberedList: NumberedListProps;
    BulletList: BulletListProps;
    Image: ImageProps;
    Columns: ColumnsProps;
    Hero: HeroProps;
    FeatureGrid: FeatureGridProps;
    Steps: StepsProps;
};

export const config: Config<Props> = {
    root: {
        fields: {
            // Volitelný SEO titulek pro <title> veřejné stránky — čte ho
            // Web\Page\PagePresenter, s fallbackem na interní název z adminu.
            title: { type: 'text', label: 'Název (titulek prohlížeče)' },
        },
    },
    categories: {
        sections: {
            title: 'Sekce',
            components: ['Hero', 'FeatureGrid', 'Steps'],
        },
        typography: {
            title: 'Text',
            components: ['Heading', 'Text', 'NumberedList', 'BulletList'],
        },
        media: {
            title: 'Média',
            components: ['Image'],
        },
        layout: {
            title: 'Rozložení',
            components: ['Columns'],
        },
    },
    components: {
        Heading,
        Text,
        NumberedList,
        BulletList,
        Image,
        Columns,
        Hero,
        FeatureGrid,
        Steps,
    },
};
