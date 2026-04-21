import os
import json
import re
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from groq import Groq
from dotenv import load_dotenv

load_dotenv()

client = Groq(api_key=os.environ["GROQ_API_KEY"])

app = FastAPI(title="Inventory AI Query Translator")

class SearchRequest(BaseModel):
    query: str
    available_categories: list[str] = [
        "laptop", "monitor", "book", "furniture", "vehicle"
    ]

class SearchParams(BaseModel):
    category: str | None = None
    attribute_filters: dict = {}
    keywords: list[str] = []
    intent_summary: str = ""

SYSTEM_PROMPT = """
You are a database query translator for an inventory management system.
Your sole job is to convert a natural language search query into a structured
JSON object that can be used to filter a MySQL database.

The database has an `inventory_items` table with these columns:
- name (string)
- category (string): one of the available categories provided
- status (string): "available", "checked_out", or "maintenance"
- quantity (integer)
- attributes (JSON): flexible key-value pairs that differ per category

Return ONLY a valid JSON object - no markdown fences, no explanation, no extra text.
The JSON must follow this exact shape:

{
  "category": "string or null",
  "attribute_filters": {
    "key": "value"
  },
  "keywords": ["word1", "word2"],
  "intent_summary": "one-sentence plain English description of what the user wants"
}

Rules:
1. If the query maps to a specific category, set "category" to that value.
2. Extract any specific attributes (e.g. refresh_rate_hz, ram_gb, author, brand).
3. For numeric comparisons, use objects: {"ram_gb": {"gte": 16}} using gte/lte/eq.
4. "keywords" should be the key search terms for a fallback name/attribute scan.
5. If you cannot determine a specific attribute key, put the concept in "keywords".
"""

@app.post("/translate-query", response_model=SearchParams)
async def translate_query(request: SearchRequest):
    user_message = (
        f"Available categories: {', '.join(request.available_categories)}\n\n"
        f"User search query: \"{request.query}\""
    )

    try:
        response = client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[
                {"role": "system", "content": SYSTEM_PROMPT},
                {"role": "user", "content": user_message}
            ],
            temperature=0.1,
        )

        raw = response.choices[0].message.content.strip()
        raw = re.sub(r"^```(?:json)?|```$", "", raw, flags=re.MULTILINE).strip()

        params = json.loads(raw)
        return SearchParams(**params)

    except json.JSONDecodeError as e:
        raise HTTPException(status_code=422, detail=f"LLM returned non-JSON: {str(e)}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/health")
async def health():
    return {"status": "ok"}
