const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

const jsDir = path.join(__dirname, '../public_html/assets/js');
const outputDir = path.join(__dirname, '../public_html/assets/dist');

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

async function minifyFiles() {
    const files = fs.readdirSync(jsDir);
    
    for (const file of files) {
        if (file.endsWith('.js') && !file.endsWith('.min.js')) {
            const inputPath = path.join(jsDir, file);
            const outputPath = path.join(outputDir, file.replace('.js', '.min.js'));
            
            const input = fs.readFileSync(inputPath, 'utf8');
            
            try {
                const output = await minify(input, {
                    compress: true,
                    mangle: true
                });
                
                fs.writeFileSync(outputPath, output.code);
                console.log(`Minified: ${file} -> ${path.basename(outputPath)}`);
            } catch (error) {
                console.error(`Error minifying ${file}:`, error);
            }
        }
    }
    
    console.log('JS minification complete!');
}

minifyFiles();
