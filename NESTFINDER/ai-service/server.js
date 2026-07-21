import express from "express";
import cors from "cors";

const app = express();
app.use(cors({ origin: "*" }));
app.use(express.json());

app.post("/ask-ai", async (req, res) => {
  try {
    const apiKey = process.env.GROQ_API_KEY;
    const userMsg = req.body.msg || req.body.question;

    if (!apiKey) return res.status(200).json({ reply: "⚠️ API Key missing!" });
    if (!userMsg) return res.status(200).json({ reply: "Kuch puchiye!" });

    const systemPrompt = `
You are "NestFinder AI", a helpful customer support assistant for NestFinder PG booking platform.
RULES:
1. Do NOT invent fake PG names or prices.
2. Answer greetings (e.g. "hi", "kaise ho") and general questions warmly in max 2 short lines in Hinglish.
3. If asked about booking or rules, guide them politely to use the search bar filters.
`;

    const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${apiKey}`,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        model: "llama-3.3-70b-versatile",
        messages: [
          { role: "system", content: systemPrompt },
          { role: "user", content: userMsg }
        ],
        max_tokens: 100,
        temperature: 0.2
      })
    });

    const data = await response.json();
    if (response.ok && data.choices?.[0]?.message?.content) {
      return res.status(200).json({ reply: data.choices[0].message.content });
    }
    return res.status(200).json({ reply: "Aap PG Search filters check kar sakte hain!" });

  } catch (err) {
    return res.status(200).json({ reply: "Server error, try again!" });
  }
});

export default app;
