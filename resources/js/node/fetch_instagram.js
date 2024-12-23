import puppeteer from "puppeteer";
import { fileURLToPath } from 'url';
import path from 'path';
import fs from 'fs';

// Define __dirname for ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

(async () => {
    const url = process.argv[2];
    if (!url) {
        console.error("Error: URL not provided.");
        process.exit(1);
    }

    try {
        // Define a custom directory for user data
        const userDataDir = path.join(__dirname, "chrome-profile");

        // Ensure the directory exists
        if (!fs.existsSync(userDataDir)) {
            fs.mkdirSync(userDataDir, { recursive: true });
        }

        console.log(`Using user data directory: ${userDataDir}`);

        const browser = await puppeteer.launch({
            headless: true,
            userDataDir, // Explicitly set the user data directory
            args: ["--no-sandbox"], // Required for restricted environments
        });

        const page = await browser.newPage();
        console.log(`Navigating to URL: ${url}`);
        await page.goto(url, { waitUntil: "networkidle2", timeout: 60000 });

        console.log("Fetching page content...");
        const html = await page.content();
        console.log("Page content fetched successfully.");
        console.log(html);

        await browser.close();
    } catch (error) {
        console.error("Error:", error.message);
        process.exit(1);
    }
})();
