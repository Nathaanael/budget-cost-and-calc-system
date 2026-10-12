import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    // Relative base: lazy-loaded chunks (charts, calendar, map) resolve next to
    // app.js, so the build works under a sub-folder like /bcs as well as at /.
    // Vite ignores a relative base in dev mode.
    base: "./",
    server: {
        host: "0.0.0.0",
        port: 5173,
        strictPort: true,
        hmr: {
            host: "localhost",
        },
        watch: {
            usePolling: true,
        },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
