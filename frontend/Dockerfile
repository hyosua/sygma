FROM node:20-alpine

ARG UID=1000
ARG GID=1000

RUN addgroup -g ${GID} appgroup 2>/dev/null || true && \
    adduser -u ${UID} -G appgroup -s /bin/sh -D appuser 2>/dev/null || true

WORKDIR /app

# Pre-create node_modules with correct ownership so the named volume
# inherits these permissions on first initialization (same pattern as backend).
RUN mkdir -p /app/node_modules && chown ${UID}:${GID} /app /app/node_modules

COPY --chown=${UID}:${GID} package*.json ./

USER appuser

RUN npm ci

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]