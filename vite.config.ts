import { defineConfig } from 'vite';
import nette from '@nette/vite-plugin';

export default defineConfig({
	plugins: [
		nette({
			entry: ['web/main.js', 'admin/main.js'],
		}),
	],

	build: {
		emptyOutDir: true,
	},

	css: {
		devSourcemap: true,
	},
});
