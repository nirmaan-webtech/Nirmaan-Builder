import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  base: './',
  build: {
    outDir: 'assets/build',
    emptyOutDir: true,
    lib: {
      entry: 'src/editor.jsx',
      name: 'NirmaanBuilderEditor',
      formats: ['iife'],
      fileName: () => 'editor.js',
    },
    rollupOptions: {
      output: {
        assetFileNames: (asset) => {
          if (asset.name && asset.name.endsWith('.css')) return 'editor.css';
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
});
