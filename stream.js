const Stream = require('node-rtsp-stream');
const streamUrl = 'rtsp://admin:Admin123@192.168.1.108:554/cam/realmonitor?channel=1&subtype=1';

stream = new Stream({
    name: 'cctv',
    streamUrl: streamUrl,
    wsPort: 9999,
    width: 1280,
    height: 720
});
