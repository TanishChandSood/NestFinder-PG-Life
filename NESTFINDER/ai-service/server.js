import express from "express";
import cors from "cors";
import { GoogleGenerativeAI } from "@google/generative-ai";

const app = express();

app.use(cors({ origin: "*" }));
app.use(express.json());

app.post("/ask-ai", async (req, res) => {
  try {
    const apiKey = process.env.GEMINI_API_KEY;

    if (!apiKey) {
      return res.status(200).json({ reply: "⚠️ API Key missing on Vercel!" });
    }

    const genAI = new GoogleGenerativeAI(apiKey);
    const userMsg = req.body.msg || req.body.question;

    if (!userMsg) {
      return res.status(200).json({ reply: "Kuch toh puchiye!" });
    }

    // Working model candidates for free tier
    const modelsToTry = [
      "gemini-2.0-flash",
      "gemini-2.0-flash-exp",
      "gemini-1.5-flash-8b"
    ];

    let aiText = null;

    for (const modelName of modelsToTry) {
      try {
        const model = genAI.getGenerativeModel({
          model: modelName,
          generationConfig: { maxOutputTokens: 120, temperature: 0.3 }
        });

        const systemPrompt = `You are NestFinder's AI Assistant. Answer in maximum 2 short lines in Hinglish.`;
        const result = await model.generateContent([systemPrompt, userMsg]);
        const response = await result.response;
        aiText = response.text();

        if (aiText) {
          console.log(`Success using model: ${modelName}`);
          break;
        }
      } catch (err) {
        console.warn(`Model ${modelName} failed: ${err.message}`);
      }
    }

    if (aiText) {
      return res.status(200).json({ reply: aiText });
    }

    return res.status(200).json({ 
      reply: "🤖 Free Tier Daily Quota full ho gaya hai. Aap direct city ya budget type karke search karein!" 
    });

  } catch (globalError) {
    console.error("Global Error:", globalError);
    return res.status(200).json({ reply: "🤖 Server error, thodi der me try karein." });
  }
});

app.get("/", (req, res) => res.send("NestFinder AI Backend Live!"));

export default app;
