const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const rootDir = path.resolve(__dirname, '..');
const distDir = path.join(rootDir, 'dist', 'rss-card');
const distRoot = path.join(rootDir, 'dist');
const zipPath = path.join(distRoot, 'rss-card.zip');

const copyTargets = [
	'build',
	'rss-card.php',
	'readme.txt',
	'README.md',
	'LICENSE',
];

const copyRecursive = (source, destination) => {
	const stats = fs.statSync(source);

	if (stats.isDirectory()) {
		fs.mkdirSync(destination, { recursive: true });
		for (const entry of fs.readdirSync(source)) {
			copyRecursive(path.join(source, entry), path.join(destination, entry));
		}
		return;
	}

	fs.copyFileSync(source, destination);
};

if (fs.existsSync(distDir)) {
	fs.rmSync(distDir, { recursive: true, force: true });
}
fs.mkdirSync(distDir, { recursive: true });

for (const target of copyTargets) {
	const sourcePath = path.join(rootDir, target);
	if (!fs.existsSync(sourcePath)) {
		continue;
	}

	if (target === 'build') {
		const nestedBuild = path.join(sourcePath, 'rss-card');
		const distBuild = path.join(distDir, 'build');
		if (fs.existsSync(nestedBuild)) {
			copyRecursive(nestedBuild, distBuild);
			const manifest = path.join(sourcePath, 'blocks-manifest.php');
			if (fs.existsSync(manifest)) {
				copyRecursive(manifest, path.join(distBuild, 'blocks-manifest.php'));
			}
			continue;
		}
	}

	copyRecursive(sourcePath, path.join(distDir, target));
}

console.log('dist ready:', distDir);

try {
	if (fs.existsSync(zipPath)) {
		fs.rmSync(zipPath, { force: true });
	}
	// Use system zip to create dist/rss-card.zip from dist/rss-card.
	execFileSync('zip', ['-r', zipPath, 'rss-card'], {
		cwd: distRoot,
		stdio: 'inherit',
	});
	console.log('zip ready:', zipPath);
} catch (error) {
	console.warn('zip skipped:', error.message);
}
