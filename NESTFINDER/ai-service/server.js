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
      return res.status(400).json({ reply: "Kuch toh puchiye!" });
    }

    // ⚡ Fast Response Configuration
    const model = genAI.getGenerativeModel({
      model: "gemini-1.5-flash",
      generationConfig: {
        maxOutputTokens: 120, // Strict limit -> Ultra Fast Response!
        temperature: 0.3      // Direct & Precise answers
      }
    });

    const systemPrompt = `You are NestFinder's AI Assistant. 
Answer the user's query in maximum 2 short lines or 2 bullet points in Hinglish. 
Be concise, helpful, and friendly. Do not generate long text.`;

    const result = await model.generateContent([systemPrompt, userMsg]);
    const response = await result.response;
    
    return res.status(200).json({ reply: response.text() });
  } catch (error) {
    console.error("Gemini API Error:", error);
    return res.status(500).json({ 
      reply: "Sorry, AI Server busy hai. Kripya thodi der baad try karein!" 
    });
  }
});

app.get("/", (req, res) => {
  res.send("NestFinder AI Service Live!");
});

export default app;
