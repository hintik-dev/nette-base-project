import { defineConfig } from 'vite';
import nette from '@nette/vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
	plugins: [
		nette({
			entry: ['web/main.js', 'admin/main.js', 'admin/editor/main.tsx'],
		}),
		react({
			include: /\.tsx$/,
		}),
		// Aktivuje se jen na CSS soubory s `@import "tailwindcss"` (web/main.css) —
		// admin bundle (AdminLTE/Bootstrap SCSS) tím není dotčen.
		tailwindcss(),
	],

	build: {
		emptyOutDir: true,
	},

	css: {
		devSourcemap: true,
	},
});
