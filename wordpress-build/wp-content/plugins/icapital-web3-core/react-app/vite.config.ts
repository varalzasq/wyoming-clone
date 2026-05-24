import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  build: {
    // Output as a single IIFE bundle for WordPress enqueue
    lib: {
      entry: resolve(__dirname, 'src/app-entry.tsx'),
      name: 'ICapitalApp',
      fileName: 'icapital-app',
      formats: ['iife'],
    },
    outDir: 'dist',
    rollupOptions: {
      // React and ReactDOM are loaded from the page — externalize to reduce bundle size
      // If not provided globally, remove these externals
      external: [],
      output: {
        globals: {},
        assetFileNames: 'icapital-app.[ext]',
      },
    },
    // Single file for easy WP enqueueing
    cssCodeSplit: false,
    sourcemap: false,
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  define: {
    // WordPress provides the REST URL via window.icapitalData
    'process.env.NODE_ENV': JSON.stringify('production'),
  },
});
