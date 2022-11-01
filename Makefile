all: build run
build:
	docker build -t neeskay_web .

run:
	docker kill neeskay_web || echo ""
	docker rm neeskay_web || echo ""
	docker run --name neeskay_web -d --restart always -p 8884:80 -v /opt/neeskay:/opt/neeskay  neeskay_web

