import express from "express";
import cors from "cors";
import { GoogleGenerativeAI } from "@google/generative-ai";

const app = express();

// Middleware
app.use(cors({ origin: "*" }));
app.use(express.json());

// Initialize Gemini
const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);

// Route which chat_helper.php calls via cURL
app.post("/ask-ai", async (req, res) => {
  try {
    // chat_helper.php se "msg" ya "question" dono accept kar lega
    const userMsg = req.body.msg || req.body.question;

    if (!userMsg) {
      return res.status(400).json({ reply: "Pardon? Message body missing." });
    }

    // Gemini 3.5 Flash Model Call
    const model = genAI.getGenerativeModel({ model: "gemini-3.5-flash" });
    const result = await model.generateContent(userMsg);
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

// Root Route Health Check
app.get("/", (req, res) => {
  res.send("NestFinder AI Vercel Microservice is Running!");
});

export default app;
