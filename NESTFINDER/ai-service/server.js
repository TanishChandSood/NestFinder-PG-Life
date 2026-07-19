import express from "express";
import puppeteer from "puppeteer";

const app = express();
app.use(express.json());

const PORT = 3000;

app.post("/ask-ai", async (req, res) => {
  const { question } = req.body;
  console.log(`🤖 User Asked: ${question}`);

  let browser;
  try {
    browser = await puppeteer.launch({
      headless: false,
      defaultViewport: null,
      args: ["--start-maximized"],
    });

    const page = await browser.newPage();

    await page.goto("https://chatgpt.com", {
      waitUntil: "networkidle2",
      timeout: 60000,
    });

    const inputSelector = "#prompt-textarea";
    await page.waitForSelector(inputSelector, { timeout: 15000 });
    await page.click(inputSelector);

    await page.type(inputSelector, question);
    await page.keyboard.press("Enter");

    console.log("⏳ ChatGPT is generating response...");

    const responseSelector = '[data-message-author-role="assistant"]';
    await page.waitForSelector(responseSelector, { timeout: 30000 });

    await new Promise((resolve) => setTimeout(resolve, 4000));

    const replyText = await page.evaluate((selector) => {
      const elements = document.querySelectorAll(selector);

      const lastMessage = elements[elements.length - 1];
      return lastMessage
        ? lastMessage.innerText
        : "Sorry, I couldn't fetch the text.";
    }, responseSelector);

    console.log(`✅ AI Response Fetched!`);

    await browser.close();

    res.json({ reply: replyText });
  } catch (error) {
    console.error("❌ Error during AI processing:", error);
    if (browser) await browser.close();
    res
      .status(500)
      .json({
        reply: "Sorry, back-end automation script failed to fetch data.",
      });
  }
});

app.listen(PORT, () => {
  console.log(
    `🚀 AI Bridge Microservice is running on http://localhost:${PORT}`,
  );
  console.log(`💡 Node.js aur PHP ab ready hain connection ke liye!`);
});
