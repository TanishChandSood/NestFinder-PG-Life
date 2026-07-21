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
      return res.status(200).json({ reply: "⚠️ API Key configured nahi hai!" });
    }

    const genAI = new GoogleGenerativeAI(apiKey);
    const userMsg = req.body.msg || req.body.question;

    if (!userMsg) {
      return res.status(200).json({ reply: "Kuch toh puchiye!" });
    }

    // 🎯 VERIFIED WORKING MODEL
    const model = genAI.getGenerativeModel({
      model: "gemini-2.0-flash",
      generationConfig: {
        maxOutputTokens: 120,
        temperature: 0.3
      }
    });

    const systemPrompt = `You are NestFinder's AI Assistant. Answer in maximum 2 short lines in Hinglish. Be concise and helpful.`;

    const result = await model.generateContent([systemPrompt, userMsg]);
    const response = await result.response;
    const aiText = response.text();

    return res.status(200).json({ reply: aiText });

  } catch (error) {
    console.error("Gemini Error:", error.message);

    // Rate Limit (429) Handling
    if (error.message.includes("429") || error.message.includes("quota")) {
      return res.status(200).json({ 
        reply: "⏳ AI abhi thoda busy hai (Rate Limit). 10-15 seconds baad try karein ya direct PG search karein!" 
      });
    }

    return res.status(200).json({ 
      reply: "🤖 Abhi connectivity issue hai, kripya thodi der baad try karein!" 
    });
  }
});

app.get("/", (req, res) => res.send("NestFinder AI Backend Live!"));

export default app;
