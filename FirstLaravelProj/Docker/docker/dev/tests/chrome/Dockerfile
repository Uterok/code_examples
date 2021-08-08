# Image
FROM selenium/standalone-chrome:latest
USER root

RUN cd ~ \ 
	&& curl -sL https://deb.nodesource.com/setup_14.x -o nodesource_setup.sh \
	&& sudo bash nodesource_setup.sh \
	&& sudo apt install nodejs

WORKDIR /app

RUN npm install http-proxy --save