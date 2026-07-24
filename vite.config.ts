import { defineConfig } from 'vite';
import nette from '@nette/vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
	plugins: [
		nette({
			entry: ['web/main.js', 'admin/main.js', 'admin/editor/main.tsx'],
		}),
		react({
			include: /\.tsx$/,
		}),
	],

	build: {
		emptyOutDir: true,
	},

	css: {
		devSourcemap: true,
	},
});
