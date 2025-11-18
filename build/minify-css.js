const fs = require('fs');
const path = require('path');
const CleanCSS = require('clean-css');

const cssDir = path.join(__dirname, '../public_html/assets/css');
const outputDir = path.join(__dirname, '../public_html/assets/dist');
const adminCssDir = path.join(__dirname, '../admin/assets/css');
const adminOutputDir = path.join(__dirname, '../admin/assets/dist');

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

if (!fs.existsSync(adminOutputDir)) {
    fs.mkdirSync(adminOutputDir, { recursive: true });
}

const files = [
    'variables.css',
    'reset.css',
    'components.css',
    'header.css',
    'hero.css',
    'gallery.css',
    'footer.css',
    'main.css'
];

let combinedCss = '';

files.forEach(file => {
    const filePath = path.join(cssDir, file);
    if (fs.existsSync(filePath)) {
        let content = fs.readFileSync(filePath, 'utf8');
        if (file === 'main.css') {
            content = content.replace(/@import\s+['"][^'"]+['"]\s*;/g, '');
        }
        combinedCss += content + '\n';
    }
});

const outputPath = path.join(outputDir, 'main.min.css');
const output = new CleanCSS({ level: 2 }).minify(combinedCss);

if (output.errors.length > 0) {
    console.error('Error minifying CSS:', output.errors);
    process.exit(1);
} else if (output.warnings.length > 0) {
    console.warn('Warnings:', output.warnings);
}

fs.writeFileSync(outputPath, output.styles);
console.log(`Minified: main.css -> main.min.css (${(output.styles.length / 1024).toFixed(2)} KB)`);

const adminCssPath = path.join(adminCssDir, 'admin.css');
if (fs.existsSync(adminCssPath)) {
    const adminCss = fs.readFileSync(adminCssPath, 'utf8');
    const adminOutput = new CleanCSS({ level: 2 }).minify(adminCss);
    
    if (adminOutput.errors.length > 0) {
        console.error('Error minifying admin CSS:', adminOutput.errors);
    } else {
        const adminOutputPath = path.join(adminOutputDir, 'admin.min.css');
        fs.writeFileSync(adminOutputPath, adminOutput.styles);
        console.log(`Minified: admin.css -> admin.min.css (${(adminOutput.styles.length / 1024).toFixed(2)} KB)`);
    }
}

console.log('CSS minification complete!');
