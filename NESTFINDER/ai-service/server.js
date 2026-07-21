import express from "express";
import cors from "cors";
import { GoogleGenAI } from "@google/genai";

const app = express();

app.use(cors({
  origin: '*',
  methods: ['GET', 'POST'],
  allowedHeaders: ['Content-Type']
}));

app.use(express.json());

const PORT = process.env.PORT || 10000;

const ai = new GoogleGenAI({ apiKey: "AQ.Ab8RN6LBqSBBvHfT2EsxtfofoLnFkejk6VlIpWwaUNLTxgeMZA" });

app.post("/ask-ai", async (req, res) => {
  const { msg } = req.body;
  const question = msg; 
  
  if (!question) {
    return res.status(400).json({ reply: "No message provided by user." });
  }

  console.log(`🤖 User Asked: ${question}`);

  try {
    const response = await ai.models.generateContent({
      model: 'gemini-flash-latest',
      contents: `Aap NestFinder (PG-Life) website ke ek smart assistant ho. Users ko PG dhoondhne, facilities, aur budget ke baare mein guide karo. User ka sawal hai: ${question}`,
    });

    const replyText = response.text;
    console.log(`✅ AI Response Fetched!`);
    res.json({ reply: replyText });

  } catch (error) {
    console.error("❌ Error during AI processing:", error);
    res.status(500).json({
      reply: "AI server is having trouble connecting. Please try again!",
    });
  }
});

app.listen(PORT, () => {
  console.log(`🚀 AI Bridge Microservice is running on port ${PORT}`);
});
