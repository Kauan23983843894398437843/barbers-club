import express from "express";
import multer from "multer";
import fetch from "node-fetch";
import fs from "fs";
import FormData from "form-data";

const app = express();
const upload = multer({ dest: "uploads/" });

// servir o front-end
app.use(express.static("public"));

// rota de geração
app.post("/api/preview", upload.single("photo"), async (req, res) => {
  try {
    const style = req.body.style;
    const filePath = req.file.path;

    const prompt = `Transforme somente o cabelo dessa pessoa colocando um ${style}, 
    sem fazer alterações no rosto, pele, expressão facial ou fundo da imagem. 
    Mantenha o estilo fotorrealista.`;

    const formData = new FormData();
    formData.append("prompt", prompt);
    formData.append("size", "512x512");
    formData.append("image", fs.createReadStream(filePath), {
      filename: req.file.originalname,
      contentType: "image/png"
    });

    const response = await fetch("https://api.openai.com/v1/images/edits", {
      method: "POST",
      headers: {
        Authorization: `Bearer ${process.env.OPENAI_API_KEY}`
      },
      body: formData
    });

    const data = await response.json();
    fs.unlinkSync(filePath);

    if (data.error) {
      return res.status(400).json({ error: data.error.message });
    }

    res.json({ url: data.data[0].url });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.listen(3000, () => {
  console.log("🚀 Servidor rodando em http://localhost:3000");
});
import OpenAI from "openai";

const openai = new OpenAI({
  apiKey: "COLOQUE_A_CHAVE_AQUI",
});

const response = openai.responses.create({
  model: "gpt-5-nano",
  input: "write a haiku about ai",
  store: true,
});

response.then((result) => console.log(result.output_text));
