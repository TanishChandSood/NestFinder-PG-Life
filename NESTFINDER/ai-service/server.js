import express from "express";
import puppeteer from "puppeteer";
import cors from "cors";

const app = express();

app.use(cors({
  origin: '*',
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type']
}));

app.use(express.json());

const PORT = process.env.PORT || 3000;

app.post("/ask-ai", async (req, res) => {
  const { msg } = req.body;
  const question = msg; 
  
  if (!question) {
    return res.status(400).json({ reply: "No message provided by user." });
  }

  console.log(`🤖 User Asked: ${question}`);

  let browser;
  try {
    browser = await puppeteer.launch({
      headless: "new",
      args: [
        "--no-sandbox",
        "--disable-setuid-sandbox",
        "--disable-dev-shm-usage",
        "--disable-gpu"
      ],
    });

    const page = await browser.newPage();
    await page.setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36");

    await page.goto("https://chatgpt.com", {
      waitUntil: "domcontentloaded",
      timeout: 90000,
    });

    const inputSelector = "#prompt-textarea";
    await page.waitForSelector(inputSelector, { timeout: 30000 });
    await page.click(inputSelector);

    await page.type(inputSelector, question);
    await page.keyboard.press("Enter");

    console.log("⏳ ChatGPT is generating response...");

    const responseSelector = '[data-message-author-role="assistant"]';
    await page.waitForSelector(responseSelector, { timeout: 60000 });

    await new Promise((resolve) => setTimeout(resolve, 6000));

    const replyText = await page.evaluate((selector) => {
      const elements = document.querySelectorAll(selector);
      const lastMessage = elements[elements.length - 1];
      return lastMessage ? lastMessage.innerText : "Sorry, I couldn't fetch the text.";
    }, responseSelector);

    console.log(`✅ AI Response Fetched!`);

    await browser.close();
    res.json({ reply: replyText });

  } catch (error) {
    console.error("❌ Error during AI processing:", error);
    if (browser) await browser.close();
    res.status(500).json({
      reply: "AI server is taking too long to respond. Please try again!",
    });
  }
});

app.listen(PORT, () => {
  console.log(`🚀 AI Bridge Microservice is running on port ${PORT}`);
});
