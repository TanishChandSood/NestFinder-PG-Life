import express from "express";
import cors from "cors";
import { GoogleGenerativeAI } from "@google/generative-ai";

const app = express();

app.use(cors({ origin: "*" }));
app.use(express.json());

const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);

app.post("/ask-ai", async (req, res) => {
  try {
    const userMsg = req.body.msg || req.body.question;

    if (!userMsg) {
      return res.status(200).json({ reply: "Kuch toh puchiye!" });
    }

    // 🎯 Tumhari API key ka confirmed model
    const model = genAI.getGenerativeModel({
      model: "gemini-2.5-flash",
      generationConfig: {
        maxOutputTokens: 150,
        temperature: 0.3
      }
    });

    const systemPrompt = `You are NestFinder's AI Assistant. Answer in maximum 2 short lines in Hinglish. Be concise, helpful and friendly.`;

    const result = await model.generateContent([systemPrompt, userMsg]);
    const response = await result.response;
    const aiText = response.text();

    return res.status(200).json({ reply: aiText });

  } catch (error) {
    console.error("Gemini Error:", error);

    // 🔍 REAL ERROR FRONTEND PAR BEJ RAHE HAIN DEBUGGING KE LIYE
    return res.status(200).json({ 
      reply: `⚠️ Error Details: ${error.message}` 
    });
  }
});

app.get("/", (req, res) => {
  res.send("NestFinder AI Service Live!");
});

export default app;
