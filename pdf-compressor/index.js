const express = require('express');
const multer = require('multer');
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

const app = express();
const upload = multer({ dest: '/tmp/compress/' }); // temp folder

app.post('/compress', upload.single('file'), (req, res) => {
    const inputPath = req.file.path;
    const outputPath = inputPath + '_compressed.pdf';

    // Ghostscript command – adjust preset if needed
    const gsPath = '"C:\\Program Files\\gs\\gs10.06.0\\bin\\gswin64c.exe"';
    const gsCmd = `${gsPath} -sDEVICE=pdfwrite -dPDFSETTINGS=/ebook -dNOPAUSE -dBATCH -sOutputFile=${outputPath} ${inputPath}`;

    exec(gsCmd, (error, stdout, stderr) => {
        if (error) {
            console.error('GS error:', stderr);
            fs.unlinkSync(inputPath);
            return res.status(500).json({ error: 'Compression failed' });
        }

        // Read compressed file and send back
        const compressedBuffer = fs.readFileSync(outputPath);
        fs.unlinkSync(inputPath);
        fs.unlinkSync(outputPath);

        res.set('Content-Type', 'application/pdf');
        res.send(compressedBuffer);
    });
});

const PORT = 3000;
app.listen(PORT, () => console.log(`Compressor running on port ${PORT}`));
