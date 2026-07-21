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

    // 🎯 Active & Production-Ready Models (Tested & Working)
    const workingModels = [
      "gemini-1.5-flash",
      "gemini-2.0-flash",
      "gemini-1.5-pro"
    ];

    let aiText = null;

    // Loop through working models if one fails
    for (const modelName of workingModels) {
      try {
        const model = genAI.getGenerativeModel({
          model: modelName,
          generationConfig: {
            maxOutputTokens: 150,
            temperature: 0.3
          }
        });

        const systemPrompt = `You are NestFinder's AI Assistant. Answer in maximum 2 short lines in Hinglish. Be concise, helpful and friendly.`;

        const result = await model.generateContent([systemPrompt, userMsg]);
        const response = await result.response;
        aiText = response.text();

        if (aiText) break; // Valid output milte hi loop break ho jayega
      } catch (err) {
        console.warn(`Model ${modelName} failed, trying next...`);
      }
    }

    if (aiText) {
      return res.status(200).json({ reply: aiText });
    } else {
      return res.status(200).json({ 
        reply: "🤖 Abhi AI server busy hai. Aap direct city name ya budget type karke PGs search kar sakte hain!" 
      });
    }

  } catch (globalError) {
    console.error("Global Error:", globalError);
    return res.status(200).json({ 
      reply: "🤖 Connectivity issue. Kripya thodi der baad try karein!" 
    });
  }
});

app.get("/", (req, res) => {
  res.send("NestFinder AI Service Live!");
});

export default app;
