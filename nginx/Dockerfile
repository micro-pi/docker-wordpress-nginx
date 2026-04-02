FROM nginx:latest

# Install curl for health checks
RUN apt-get update && apt-get install -y --no-install-recommends curl && rm -rf /var/lib/apt/lists/*

# Copy the NGINX config into the container
COPY default.conf /etc/nginx/conf.d/default.conf
