import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/timepicker.css",
                "resources/js/app.js",
                "resources/js/timepicker.js",
            ],
            refresh: ["resources/views/**/*"],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
    },
    resolve: {
        alias: {
            bootstrap: path.resolve(
                __dirname,
                "node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"
            ),
            "@popperjs/core": path.resolve(
                __dirname,
                "node_modules/@popperjs/core/dist/umd/popper.min.js"
            ),
        },
    },
});
