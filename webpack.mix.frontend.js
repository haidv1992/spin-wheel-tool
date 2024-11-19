const mix = require('laravel-mix');

// Frontend spin-wheel build configuration
if (mix.inProduction()) {
    console.log("inProduction....")
    // Production: build vào dist/frontend
    mix.setPublicPath('dist/frontend') // Output directory
        .js('resources/js/spin-wheel.js', 'js') // Entry file và output subdirectory
        .vue({ version: 3 }) // Vue integration
        .extract(['vue']); // Tách các thư viện bên thứ ba nếu cần
} else {
    console.log("inDeveloper....")

    // Development: build trực tiếp vào public/spin-wheel-tool
    mix.setPublicPath('../../public/spin-wheel-tool')
        .js('resources/js/spin-wheel.js', 'js')
        .vue({ version: 3 })
        .extract(['vue'])
        .sourceMaps();
}
