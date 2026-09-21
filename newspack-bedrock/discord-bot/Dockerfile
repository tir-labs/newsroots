FROM node:18-alpine
WORKDIR /app
COPY package.json ./
RUN npm install --production 2>/dev/null || true
COPY index.js ./
VOLUME ["/app/data"]
ENV STATE_FILE=/app/data/bot-state.json
CMD ["node", "index.js"]

