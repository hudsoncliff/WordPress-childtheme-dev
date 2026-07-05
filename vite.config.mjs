import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from 'vite';

const themeHeader = `/*!
Template: swell
Theme Name: ヨガスタジオベルガモット
Description: Swellの子テーマ
Author: KaiOno
Version: 1
*/`;

function phpFullReload() {
    return {
        name: 'php-full-reload',
        configureServer(server) {
            server.watcher.add('**/*.php');
            server.watcher.on('change', (file) => {
                if (file.endsWith('.php')) {
                    server.ws.send({ type: 'full-reload', path: '*' });
                }
            });
        },
    };
}

function preserveThemeHeader() {
    return {
        name: 'preserve-wordpress-theme-header',
        generateBundle(_, bundle) {
            const styleAsset = bundle['css/style.css'];

            if (!styleAsset || styleAsset.type !== 'asset') {
                return;
            }

            const source = String(styleAsset.source);

            if (!source.includes('Theme Name:')) {
                styleAsset.source = `${themeHeader}\n${source}`;
            }
        },
    };
}

function publishWordPressAssets() {
    return {
        name: 'publish-wordpress-assets',
        closeBundle() {
            const buildDir = path.resolve('assets/.vite-build');
            const manifestPath = path.join(buildDir, '.vite/manifest.json');
            const publicManifestPath = path.resolve('assets/.vite/manifest.json');
            const cssPath = path.join(buildDir, 'css/style.css');
            const jsDir = path.join(buildDir, 'js');

            if (fs.existsSync(cssPath)) {
                const publicStylePath = path.resolve('style.css');

                fs.copyFileSync(cssPath, publicStylePath);
                fs.chmodSync(publicStylePath, 0o755);
            }

            if (fs.existsSync(jsDir)) {
                fs.cpSync(jsDir, path.resolve('assets/js'), {
                    recursive: true,
                });
            }

            if (fs.existsSync(manifestPath)) {
                const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
                const publicManifest = Object.fromEntries(
                    Object.entries(manifest).map(([entry, value]) => [
                        entry,
                        rewriteManifestEntry(value),
                    ])
                );

                fs.mkdirSync(path.dirname(publicManifestPath), { recursive: true });
                fs.writeFileSync(publicManifestPath, `${JSON.stringify(publicManifest, null, 2)}\n`);
            }
        },
    };
}

function rewriteManifestEntry(entry) {
    return {
        ...entry,
        file: rewriteManifestPath(entry.file),
        css: entry.css?.map(rewriteManifestPath),
        assets: entry.assets?.map(rewriteManifestPath),
    };
}

function rewriteManifestPath(filePath) {
    if (filePath === 'css/style.css') {
        return 'style.css';
    }

    if (filePath.startsWith('js/')) {
        return `assets/${filePath}`;
    }

    return filePath;
}

export default defineConfig({
    base: './',
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
    },
    build: {
        outDir: 'assets/.vite-build',
        emptyOutDir: true,
        manifest: '.vite/manifest.json',
        rollupOptions: {
            input: {
                style: path.resolve('src/css/style.scss'),
                main: path.resolve('src/js/main.js'),
            },
            output: {
                entryFileNames: 'js/[name].js',
                chunkFileNames: 'js/[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name === 'style.css') {
                        return 'css/style.css';
                    }

                    return 'assets/[name][extname]';
                },
            },
        },
    },
    plugins: [
        phpFullReload(),
        preserveThemeHeader(),
        publishWordPressAssets(),
    ],
});
