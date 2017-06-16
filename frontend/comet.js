/**
 * Comet-сервер работающий на основе socket.io для отправки уведомлений пользователю "на лету"
 */

const config = require('./comet.config.js').config;
const bodyParser = require('body-parser');
const app = require('express')();
const http = require('http').Server(app);
const io = require('socket.io')(http);

console.log('start comet server');

app.get('/', (req, res) => {
    console.log('ping');
    res.send('pong');
});

// обработка post-данных от бекенда
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

/**
 * Подключение сокета
 */
io.on('connection', (socket) => {
    console.log('user connection');

    /**
     * Указание идентификатора пользователя
     */
    socket.on('join_user', (userId) => {
        socket.userId = userId;
        socket.join('user_' + userId);
        console.log('joined user: ' + userId);
    });

    /**
     * Отключение сокета
     */
    socket.on('disconnect', () => {
        console.log('user disconnected: ' + (socket.userId ? socket.userId : 'anonymous'));
    });
});

/**
 * Получения данных из бекенда (PHP)
 */
app.post('/', (req, res) => {
    const data = req.body;

    if (req.ip.indexOf('127.0.0.1') !== -1 && data) {
        /**
         * Пользователю необходимо обновить список уведомлений
         */
        if (data.task === 'fetchNewNotifications' && data.userId) {
            console.log('fetch new notifications for ' + data.userId);
            io.sockets.in('user_' + data.userId).emit('fetchNewNotifications', data.userId);
        }
    }

    res.end();
});

http.listen(config.port, () => {
    console.log('listening on ' + config.port);
});
