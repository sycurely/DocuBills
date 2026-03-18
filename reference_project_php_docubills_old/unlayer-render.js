const puppeteer = require("puppeteer");
const fs = require("fs");

async function renderHTML(designJsonFile) {
  const designJson = JSON.parse(fs.readFileSync(designJsonFile, "utf8"));

  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();

  await page.setContent(`
    <html>
    <body>
      <div id="editor"></div>
      <script src="https://editor.unlayer.com/embed.js"></script>
      <script>
        window.onload = function () {
          unlayer.init({ id: 'editor', displayMode: 'email' });
          unlayer.loadDesign(${JSON.stringify(designJson)});
          unlayer.exportHtml(function(data) {
            document.body.innerHTML = '<textarea id="output">' + data.html + '</textarea>';
          });
        };
      </script>
    </body>
    </html>
  `);

  await page.waitForSelector("#output");

  const exportedHtml = await page.$eval("#output", el => el.value);
  console.log(exportedHtml);

  await browser.close();
}

const inputFile = process.argv[2];
renderHTML(inputFile);
