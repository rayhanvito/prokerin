import { defineConfig } from 'vitest/config';
import { resolve } from 'node:path';

// Note: @vitejs/plugin-react is intentionally omitted here.
// Project uses Vite 8 (rolldown), but Vitest 3 bundles its own Vite (rollup),
// causing a Plugin<any> type mismatch when the React plugin is passed.
// Hook & helper unit tests in this project don't require React Fast Refresh,
// and Vite/esbuild handle JSX automatically via tsconfig "jsx": "react-jsx".
export default defineConfig({
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: ['./resources/js/__tests__/setup.ts'],
        css: false,
        include: [
            'resources/js/**/__tests__/**/*.test.{ts,tsx}',
            'resources/js/**/*.test.{ts,tsx}',
        ],
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});
