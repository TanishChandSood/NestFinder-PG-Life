import express from "express";
import cors from "cors";
import { GoogleGenerativeAI } from "@google/generative-ai";

const app = express();

app.use(cors({ origin: "*" }));
app.use(express.json());

app.post("/ask-ai", async (req, res) => {
  try {
    const apiKey = process.env.GEMINI_API_KEY;

    // 🔴 Step A: Check if API Key exists
    if (!apiKey) {
      console.error("CRITICAL ERROR: GEMINI_API_KEY environment variable is missing!");
      return res.status(200).json({ reply: "⚠️ API Key not configured on Vercel backend." });
    }

    const genAI = new GoogleGenerativeAI(apiKey);
    const userMsg = req.body.msg || req.body.question;

    if (!userMsg) {
      return res.status(200).json({ reply: "Kuch toh puchiye!" });
    }

    const candidateModels = [
      "gemini-1.5-flash",
      "gemini-2.0-flash",
      "gemini-1.5-pro",
      "models/gemini-2.5-flash"
    ];

    let lastError = null;

    for (const modelName of candidateModels) {
      try {
        const model = genAI.getGenerativeModel({
          model: modelName,
          generationConfig: { maxOutputTokens: 150, temperature: 0.3 }
        });

        const systemPrompt = `You are NestFinder's AI Assistant. Answer in maximum 2 short lines in Hinglish.`;
        const result = await model.generateContent([systemPrompt, userMsg]);
        const response = await result.response;
        const aiText = response.text();

        if (aiText) {
          console.log(`SUCCESS with model: ${modelName}`);
          return res.status(200).json({ reply: aiText });
        }
      } catch (err) {
        lastError = err.message;
        // 🔍 Is line se Vercel log me exact problem pta chalegi:
        console.error(`FAILED [${modelName}]:`, err.message);
      }
    }

    // Return the actual error message to chat window for instant fixing
    return res.status(200).json({ 
      reply: `⚠️ API Error: ${lastError || "All models failed"}` 
    });

  } catch (globalError) {
    console.error("Global Error:", globalError);
    return res.status(200).json({ reply: `⚠️ Server Error: ${globalError.message}` });
  }
});

app.get("/", (req, res) => res.send("NestFinder AI Service Live!"));

export default app;
