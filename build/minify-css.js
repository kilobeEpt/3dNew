const fs = require('fs');
const path = require('path');
const CleanCSS = require('clean-css');

const cssDir = path.join(__dirname, '../public_html/assets/css');
const outputDir = path.join(__dirname, '../public_html/assets/dist');

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

fs.readdirSync(cssDir).forEach(file => {
    if (file.endsWith('.css') && !file.endsWith('.min.css')) {
        const inputPath = path.join(cssDir, file);
        const outputPath = path.join(outputDir, file.replace('.css', '.min.css'));
        
        const input = fs.readFileSync(inputPath, 'utf8');
        const output = new CleanCSS().minify(input);
        
        if (output.errors.length > 0) {
            console.error(`Error minifying ${file}:`, output.errors);
        } else {
            fs.writeFileSync(outputPath, output.styles);
            console.log(`Minified: ${file} -> ${path.basename(outputPath)}`);
        }
    }
});

console.log('CSS minification complete!');
