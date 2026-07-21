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
      return res.status(400).json({ reply: "Pardon? Message missing." });
    }

    // System prompt forces short, crisp & friendly response
    const systemPrompt = `You are an AI assistant for "NestFinder / PG Life" portal. 
Answer user general queries concisely in 2-3 short bullet points or lines. 
Keep tone friendly, helpful, and use Hinglish/English. 
Do NOT write long essays or large paragraphs.`;

    const model = genAI.getGenerativeModel({ model: "gemini-3.5-flash" });
    const result = await model.generateContent([systemPrompt, userMsg]);
    const response = await result.response;
    const aiText = response.text();

    return res.status(200).json({ reply: aiText });
  } catch (error) {
    console.error("Gemini API Error:", error);
    return res.status(500).json({ 
      reply: "Sorry, mera AI server abhi response nahi de pa raha hai." 
    });
  }
});

app.get("/", (req, res) => {
  res.send("NestFinder AI Vercel Microservice is Running!");
});

export default app;
