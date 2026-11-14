FROM node:20-alpine AS build

WORKDIR /app
COPY package.json ./
# In full build, npm install & build are run here

FROM nginx:1.27-alpine AS prod
COPY --from=build /app/index.html /usr/share/nginx/html/index.html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
