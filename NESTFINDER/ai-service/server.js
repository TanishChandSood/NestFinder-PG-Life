import express from "express";
import cors from "cors";

const app = express();

app.use(cors({ origin: "*" }));
app.use(express.json());

app.post("/ask-ai", async (req, res) => {
  try {
    const apiKey = process.env.GROQ_API_KEY;
    const userMsg = req.body.msg || req.body.question;

    if (!apiKey) {
      return res.status(200).json({ reply: "⚠️ Vercel par GROQ_API_KEY missing hai!" });
    }

    if (!userMsg) {
      return res.status(200).json({ reply: "Kuch toh puchiye!" });
    }

    // Direct Groq API Endpoint (Ultra Fast Llama-3 Model)
    const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${apiKey}`,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        model: "llama-3.3-70b-versatile",
        messages: [
          {
            role: "system",
            content: "You are NestFinder's AI Assistant. Answer the user's PG search queries in maximum 2 short lines in Hinglish. Be concise, friendly, and helpful."
          },
          {
            role: "user",
            content: userMsg
          }
        ],
        max_tokens: 120,
        temperature: 0.3
      })
    });

    const data = await response.json();

    if (response.ok && data.choices?.[0]?.message?.content) {
      const aiReply = data.choices[0].message.content;
      return res.status(200).json({ reply: aiReply });
    }

    console.error("Groq Response Error:", data);
    return res.status(200).json({ 
      reply: "🤖 AI filter busy hai, aap direct PG search bar use kar sakte hain!" 
    });

  } catch (err) {
    console.error("Server Error:", err.message);
    return res.status(200).json({ reply: "🤖 Connectivity issue, thodi der me try karein!" });
  }
});

app.get("/", (req, res) => res.send("NestFinder AI Backend Live via Groq!"));

export default app;
