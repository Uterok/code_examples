var httpProxy = require('http-proxy');

var proxy = httpProxy.createProxyServer({target:'http://nginx:81'}).listen(8081); // See (†)
console.log('start proxy from localhost::8081 to nginx::81');

proxy.on('error', function(e) {
    console.log('error - ', e.code);
});

proxy.on('close', function (res, socket, head) {
  // view disconnected websocket connections
  console.log('Client disconnected');
});
