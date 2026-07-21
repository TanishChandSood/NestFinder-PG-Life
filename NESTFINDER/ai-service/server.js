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
  const question = msg; 
  
  if (!question) {
    return res.status(400).json({ reply: "No message provided by user." });
  }

  // 👇 --- VERCEL KEY DEBUGGING START --- 👇
  console.log("--- VERCEL KEY DEBUGGING ---");
  console.log("Key Status:", process.env.GEMINI_API_KEY ? "Load ho gayi! ✅" : "Missing (Undefined) ❌");

  if (process.env.GEMINI_API_KEY) {
      // Key leak na ho isliye sirf starting aur length check kar rahe hain
      console.log("Shuru ke 5 characters:", process.env.GEMINI_API_KEY.substring(0, 5));
      console.log("Total length:", process.env.GEMINI_API_KEY.length);
  }
  console.log("----------------------------");
  // 👆 --- VERCEL KEY DEBUGGING END --- 👆

  console.log(`🤖 User Asked: ${question}`);

  try {
    // SDK ko sahi tarike se object pass karke initialize kar rahe hain
    const ai = new GoogleGenAI({ apiKey: process.env.GEMINI_API_KEY });

    const response = await ai.models.generateContent({
      model: 'gemini-3.5-flash', // <-- Yahan purana model hata kar latest 2.5 flash set kar diya hai
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
