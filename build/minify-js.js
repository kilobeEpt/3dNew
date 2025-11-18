const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

const jsDir = path.join(__dirname, '../public_html/assets/js');
const outputDir = path.join(__dirname, '../public_html/assets/dist');

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

async function minifyFiles(dir, outputBase, relativeDir = '') {
    const files = fs.readdirSync(dir);
    
    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        
        if (stat.isDirectory()) {
            const subOutputDir = path.join(outputBase, relativeDir, file);
            if (!fs.existsSync(subOutputDir)) {
                fs.mkdirSync(subOutputDir, { recursive: true });
            }
            await minifyFiles(fullPath, outputBase, path.join(relativeDir, file));
        } else if (file.endsWith('.js') && !file.endsWith('.min.js')) {
            const outputPath = path.join(outputBase, relativeDir, file.replace('.js', '.min.js'));
            
            const input = fs.readFileSync(fullPath, 'utf8');
            
            try {
                const output = await minify(input, {
                    compress: true,
                    mangle: true,
                    module: true,
                    toplevel: true
                });
                
                fs.writeFileSync(outputPath, output.code);
                const relativePath = path.join(relativeDir, file);
                console.log(`Minified: ${relativePath || file} -> ${relativePath.replace('.js', '.min.js') || file.replace('.js', '.min.js')}`);
            } catch (error) {
                console.error(`Error minifying ${file}:`, error.message);
            }
        }
    }
}

async function run() {
    console.log('Minifying public JS files...');
    await minifyFiles(jsDir, outputDir);
    
    const adminJsDir = path.join(__dirname, '../admin/assets/js');
    const adminOutputDir = path.join(__dirname, '../admin/assets/dist');
    
    if (fs.existsSync(adminJsDir)) {
        console.log('Minifying admin JS files...');
        if (!fs.existsSync(adminOutputDir)) {
            fs.mkdirSync(adminOutputDir, { recursive: true });
        }
        await minifyFiles(adminJsDir, adminOutputDir);
    }
    
    console.log('JS minification complete!');
}

run();
