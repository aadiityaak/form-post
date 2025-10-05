const fs = require("fs");
const path = require("path");
const archiver = require("archiver");

// Get plugin info from package.json
const packageJson = require("./package.json");
const pluginName = packageJson.name;
const version = packageJson.version;

// Create output directory if it doesn't exist
const outputDir = path.join(__dirname, "dist");
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir);
}

// Create zip file name
const zipFileName = `${pluginName}-v${version}.zip`;
const outputPath = path.join(outputDir, zipFileName);

// Create a file to stream archive data to
const output = fs.createWriteStream(outputPath);
const archive = archiver("zip", {
  zlib: { level: 9 }, // Sets the compression level
});

// Listen for all archive data to be written
output.on("close", () => {
  console.log(`✅ Build completed successfully!`);
  console.log(`📦 Plugin zipped to: ${outputPath}`);
  console.log(
    `📊 Total size: ${(archive.pointer() / 1024 / 1024).toFixed(2)} MB`
  );
});

// Good practice to catch warnings
archive.on("warning", (err) => {
  if (err.code === "ENOENT") {
    console.warn("Warning:", err);
  } else {
    throw err;
  }
});

// Good practice to catch this error explicitly
archive.on("error", (err) => {
  throw err;
});

// Pipe archive data to the file
archive.pipe(output);

// Files and directories to include in the zip
const filesToInclude = ["form-post.php", "README.md", "assets/", "includes/"];

// Add files to the archive
filesToInclude.forEach((file) => {
  const filePath = path.join(__dirname, file);
  if (fs.existsSync(filePath)) {
    if (fs.statSync(filePath).isDirectory()) {
      archive.directory(filePath, file);
      console.log(`📁 Added directory: ${file}`);
    } else {
      archive.file(filePath, { name: file });
      console.log(`📄 Added file: ${file}`);
    }
  } else {
    console.warn(`⚠️  File not found: ${file}`);
  }
});

// Finalize the archive
archive.finalize();
