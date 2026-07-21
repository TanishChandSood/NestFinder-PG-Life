import express from "express";
import cors from "cors";
import { GoogleGenAI } from "@google/genai";

const app = express();

// CORS Middleware configuration
app.use(cors({
  origin: '*',
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type']
}));

app.use(express.json());

const PORT = process.env.PORT || 10000;

app.post("/ask-ai", async (req, res) => {
  const { msg } = req.body;
  
  if (!msg) {
    return res.status(400).json({ reply: "No message provided." });
  }

  console.log(`🤖 Received message: ${msg}`);

  try {
    const ai = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });

    const systemPrompt = `
      Aap 'NestFinder (PG-Life)' website ke ek smart aur helpful assistant ho.
      Aapka kaam users ko guide karna aur friendly baatcheet karna hai.

      🚨 STRICT FORMATTING RULES:
      1. Do NOT use any Markdown formatting like '###', '**', or '*'.
      2. Keep the text completely plain, clean, and readable.
      3. Use emojis to make it lively and talk in a friendly Hinglish tone.

      User ka message hai: ${msg}
    `;

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash',
      contents: systemPrompt,
    });

    const replyText = response.text || "Mujhe samajh nahi aaya, thoda aur clearly batao!";
    res.json({ reply: replyText });

  } catch (error) {
    console.error("❌ AI Error:", error);
    res.status(500).json({ 
      reply: "Sorry, mera AI server abhi thoda busy hai. Thodi der baad try karo!" 
    });
  }
});
app.listen(PORT, () => {
  console.log(`🚀 AI Bridge Microservice is running on port ${PORT}`);
});
