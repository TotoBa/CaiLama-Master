FROM python:3.12-slim

WORKDIR /app
COPY pyproject.toml README.md ./
COPY src ./src
RUN pip install --no-cache-dir -e .

EXPOSE 8010
CMD ["uvicorn", "cailama_origin.main:app", "--host", "0.0.0.0", "--port", "8010"]
