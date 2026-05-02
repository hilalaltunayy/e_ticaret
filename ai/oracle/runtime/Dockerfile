FROM python:3.12-alpine

RUN addgroup -S oracle && adduser -S oracle -G oracle

WORKDIR /workspace

USER oracle

CMD ["python", "ai/oracle/runtime/server.py"]
