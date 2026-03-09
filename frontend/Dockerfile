FROM node:20-alpine

ARG UID=1000
ARG GID=1000

RUN deluser --remove-home node 2>/dev/null || true && \
    delgroup node 2>/dev/null || true && \
    addgroup -g ${GID} appgroup 2>/dev/null || true && \
    adduser -u ${UID} -G appgroup -s /bin/sh -D appuser

WORKDIR /app

RUN mkdir -p /app/node_modules && chown ${UID}:${GID} /app /app/node_modules

COPY --chown=${UID}:${GID} package*.json ./

USER appuser

EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]